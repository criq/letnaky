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
				return $i->venueUrl;
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
		if ($this->csfdId < 0) {
			return false;
		}

		try {

			// Look for the ID.
			if (!$this->csfdId) {
				$res = \Katu\Utils\Cache::getUrl(\Katu\Types\TUrl::make('http://csfdapi.cz/movie', [
					'search' => $this->title,
				]));
				if (isset($res[0])) {
					$csfdId = $res[0]->id;
				}
			} else {
				$csfdId = $this->csfdId;
			}

			if ($csfdId) {
				return \Katu\Utils\Cache::getUrl(\Katu\Types\TUrl::make('http://csfdapi.cz/movie/' . $csfdId));
			}

		} catch (\Exception $e) {
			return false;
		}

		return false;
	}

	public function getRating() {
		$csfdInfo = $this->getCsfdInfo();
		if (isset($csfdInfo->rating)) {
			return $csfdInfo->rating * .01;
		}

		return false;
	}

	public function getPosterUrl() {
		$csfdInfo = $this->getCsfdInfo();
		if (isset($csfdInfo->poster_url)) {
			return $csfdInfo->poster_url;
		}

		return false;
	}

	public function getYear() {
		$csfdInfo = $this->getCsfdInfo();
		if (isset($csfdInfo->year)) {
			return $csfdInfo->year;
		}

		return false;
	}

	public function getRuntime() {
		$csfdInfo = $this->getCsfdInfo();
		if (isset($csfdInfo->runtime) && preg_match('#^[0-9]+ min#', $csfdInfo->runtime, $match)) {
			return $match[0];
		}

		return false;
	}

	public function getRuntimeInMinutes() {
		if (preg_match('#^([0-9]+) min$#', $this->getRuntime(), $match)) {
			return (int) $match[1];
		}

		return false;
	}

	public function getPlot() {
		$csfdInfo = $this->getCsfdInfo();
		if (isset($csfdInfo->plot)) {
			return $csfdInfo->plot;
		}

		return false;
	}

	public function getCsfdUrl() {
		$csfdInfo = $this->getCsfdInfo();
		if (isset($csfdInfo->csfd_url)) {
			return $csfdInfo->csfd_url;
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
