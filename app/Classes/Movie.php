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

		return false;
	}

	public function getRating() {
		$csfdInfo = $this->getCsfdInfo();

		return $csfdInfo->rating * .01;
	}

	public function getPosterImageColors() {
		return \Katu\Utils\Cache::get(function($posterUrl) {

			return \Kleur\Kleur::extractColors($posterUrl, 3);

		}, null, $this->getCsfdInfo()->poster_url);
	}

}
