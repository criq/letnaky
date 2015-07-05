<?php

namespace App\Classes;

class Movie {

	static function createFromTable($dom) {
		$object = new static;

		$object->venue = $dom->filter('td')->eq(0)->html();
		$object->venueUrl = $dom->filter('td')->eq(1)->html();
		$object->dateTime = \Katu\Utils\DateTime::createFromFormat('j.n.Y H:i:s', $dom->filter('td')->eq(2)->html() . ' ' . $dom->filter('td')->eq(3)->html());
		$object->title = strip_tags($dom->filter('td')->eq(4)->html());
		$object->entry = (int) strtr(substr($dom->filter('td')->eq(5)->html(), 0, -3), ',', '.');
		$object->csfdId = (int) $dom->filter('td')->eq(6)->html();

		return $object;
	}

	public function getCsfdInfo() {
		if ($this->csfdId) {
			return \Katu\Utils\Cache::getUrl(\Katu\Types\TUrl::make('http://csfdapi.cz/movie/' . $this->csfdId));
		} else {
			$res = \Katu\Utils\Cache::getUrl(\Katu\Types\TUrl::make('http://csfdapi.cz/movie', [
				'search' => $this->title,
			]));
			if (isset($res[0])) {
				return $res[0];
			}
		}

		return false;
	}

}
