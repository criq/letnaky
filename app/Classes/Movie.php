<?php

namespace App\Classes;

class Movie {

	static function getAll($timeout = null) {
		return \Katu\Utils\Cache::get('movies', function() use($timeout) {

			$res = \Katu\Utils\Cache::getUrl('https://docs.google.com/spreadsheets/d/1_H6y1uS-yGkZGfdMtS2kFkCw5Fgml35PDJcK0ZcWr_0/pubhtml', $timeout);
			$dom = \Katu\Utils\DOM::crawlHtml($res);

			$movies = array_slice($dom->filter('table tbody tr')->each(function($e) {
				return static::createFromTable($e);
			}), 2);

			$movies = array_filter($movies, function($i) {
				return $i->venueUrl && $i->dateTime;
			});

			array_multisort(array_map(function($i) {
				return $i->dateTime->getTimestamp();
			}, $movies), $movies);

			return $movies;

		}, $timeout);
	}

	static function getByHash($hash) {
		foreach (static::getAll() as $movie) {
			if ($movie->hash == $hash) {
				return $movie;
			}
		}

		return false;
	}

	static function createFromTable($dom) {
		$object = new static;

		$object->venue    = strip_tags($dom->filter('td')->eq(0)->html());
		$object->venueUrl = strip_tags($dom->filter('td')->eq(1)->html());
		$object->dateTime = \Katu\Utils\DateTime::createFromFormat('j.n.Y H:i:s', $dom->filter('td')->eq(2)->html() . ' ' . $dom->filter('td')->eq(3)->html());
		if (!$object->dateTime) {
			$object->dateTime = \Katu\Utils\DateTime::createFromFormat('j.n.Y H:i', $dom->filter('td')->eq(2)->html() . ' ' . $dom->filter('td')->eq(3)->html());
		}
		$object->title    = strip_tags($dom->filter('td')->eq(4)->html());
		$object->entry    = (int) strtr(substr($dom->filter('td')->eq(5)->html(), 0, -3), ',', '.');
		$object->csfdId   = (int) $dom->filter('td')->eq(6)->html();
		$object->eventUrl = strip_tags($dom->filter('td')->eq(7)->html());
		$object->hash     = sha1(\Katu\Utils\JSON::encodeStandard([
			$object->venue,
			$object->dateTime,
			$object->title,
		]));

		return $object;
	}

	public function getCsfdInfo() {
		$csfdUrl = $this->getCsfdUrl();
		if ($csfdUrl) {

			$src = \Katu\Utils\Cache::getUrl($csfdUrl);
			$dom = \Katu\Utils\DOM::crawlHtml($src);

			$res = [];

			try {
				if (preg_match('#^([0-9]+)%$#', $dom->filter('#rating .average')->html(), $match)) {
					$res['rating'] = $match[1] * .01;
				}
			} catch (\Exception $e) {

			}

			try {
				if (preg_match('#"year":([0-9]{4})#', $src, $match)) {
					$res['year'] = $match[1];
				}
			} catch (\Exception $e) {

			}

			try {
				if (preg_match('#([0-9]+) min#', $src, $match)) {
					$res['runtime'] = $match[1];
				}
			} catch (\Exception $e) {

			}

			return $res;

		}
	}

	public function getRating() {
		$csfdInfo = $this->getCsfdInfo();
		if (isset($csfdInfo['rating'])) {
			return $csfdInfo['rating'];
		}

		return false;
	}

	public function getYear() {
		$csfdInfo = $this->getCsfdInfo();
		if (isset($csfdInfo['year'])) {
			return $csfdInfo['year'];
		}

		return false;
	}

	public function getRuntime() {
		$csfdInfo = $this->getCsfdInfo();
		if (isset($csfdInfo['runtime'])) {
			return $csfdInfo['runtime'];
		}

		return false;
	}

	public function getRuntimeInMinutes() {
		if (preg_match('#^([0-9]+) min$#', $this->getRuntime(), $match)) {
			return (int) $match[1];
		}

		return false;
	}

	public function getCsfdUrl() {
		if ($this->csfdId < 0) {
			return false;
		}

		try {

			$src = \Katu\Utils\Cache::getUrl(\Katu\Types\TUrl::make('http://www.csfd.cz/hledat/', [
				'q' => $this->title,
			]));
			$dom = \Katu\Utils\DOM::crawlHtml($src);

			try {
				return 'http://www.csfd.cz' . $dom->filter('#search-films .content ul li')->eq(0)->filter('a')->attr('href');
			} catch (\Exception $e) {
				return false;
			}

		} catch (\Exception $e) {
			return false;
		}

		return false;
	}

	public function getEventUrl() {
		return $this->eventUrl;
	}

	public function getUrl() {
		$csfdUrl = $this->getCsfdUrl();

		return $csfdUrl ?: $this->getEventUrl();
	}

	public function getPosterImageColor() {
		try {

			$color = \Katu\Config::get('venues', $this->venue, 'color');



			return new \MischiefCollective\ColorJizz\Formats\Hex(hexdec($color));

		} catch (\Exception $e) {

			try {

				return \Katu\Utils\Cache::get(function($posterUrl) {

					$image = new \Intervention\Image\Image($posterUrl);
					$image->resize(1, 1);
					$color = $image->pickColor(0, 0);

					return new \MischiefCollective\ColorJizz\Formats\RGB($color['r'], $color['g'], $color['b']);

				}, null, $this->getPosterUrl());

			} catch (\Exception $e) {
				return new \MischiefCollective\ColorJizz\Formats\RGB(rand(0, 255), rand(0, 255), rand(0, 255));
			}

		}
	}

}
