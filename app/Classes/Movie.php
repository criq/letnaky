<?php

namespace App\Classes;

class Movie {

	static function createFromTable($dom) {
		$object = new static;

		$object->venue    = strip_tags($dom->filter('td')->eq(0)->html());
		$object->venueUrl = strip_tags($dom->filter('td')->eq(1)->html());
		$object->dateTime = \Katu\Utils\DateTime::createFromFormat('j.n.Y H:i:s', $dom->filter('td')->eq(2)->html() . ' ' . $dom->filter('td')->eq(3)->html());
		$object->title    = strip_tags($dom->filter('td')->eq(4)->html());
		$object->entry    = (int) strtr(substr($dom->filter('td')->eq(5)->html(), 0, -3), ',', '.');
		$object->csfdId   = (int) $dom->filter('td')->eq(6)->html();

		return $object;
	}

	public function getCsfdInfo() {
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
		if (isset($csfdInfo->runtime) && preg_match('#^[0-9]+ min$#', $csfdInfo->runtime, $match)) {
			return $match[0];
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
