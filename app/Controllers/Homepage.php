<?php

namespace App\Controllers;

class Homepage extends \Katu\Controller {

	static function index() {
		static::$data['movies'] = \Katu\Utils\Cache::get(function() {

			$res = \Katu\Utils\Cache::getUrl('https://docs.google.com/spreadsheets/d/1_H6y1uS-yGkZGfdMtS2kFkCw5Fgml35PDJcK0ZcWr_0/pubhtml', 3600);
			$dom = \Katu\Utils\DOM::crawlHtml($res);

			$movies = array_slice($dom->filter('table tbody tr')->each(function($e) {
				return \App\Classes\Movie::createFromTable($e);
			}), 2);

			array_multisort(array_map(function($i) {
				return $i->dateTime->getTimestamp();
			}, $movies), $movies);

			return $movies;

		}, 3600);

		$dateTime = new \Katu\Utils\DateTime;
		static::$data['theme'] = ($dateTime->format('H') > 6 && $dateTime->format('H') < 20) ? 'light' : 'dark';

		static::$data['_page']['title'] = 'Letňáky v Brně';

		return static::render("Homepage/index");
	}

}
