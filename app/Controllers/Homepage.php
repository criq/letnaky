<?php

namespace App\Controllers;

class Homepage extends \Katu\Controller {

	static function index() {
		$app = \Katu\App::get();

		static::$data['movies'] = \Katu\Utils\Cache::get('movies', function() {

			$res = \Katu\Utils\Cache::getUrl('https://docs.google.com/spreadsheets/d/1_H6y1uS-yGkZGfdMtS2kFkCw5Fgml35PDJcK0ZcWr_0/pubhtml', 3600);
			$dom = \Katu\Utils\DOM::crawlHtml($res);

			$movies = array_slice($dom->filter('table tbody tr')->each(function($e) {
				return \App\Classes\Movie::createFromTable($e);
			}), 2);

			$movies = array_filter($movies, function($i) {
				return $i->venueUrl;
			});

			array_multisort(array_map(function($i) {
				return $i->dateTime->getTimestamp();
			}, $movies), $movies);

			return $movies;

		}, 3600);

		static::$data['_page']['title'] = 'Letňáky v Brně';

		static::$data['movies'] = array_filter(static::$data['movies'], function($i) {
			return $i->dateTime->isInFuture();
		});

		if ($app->router()->getCurrentRoute()->getName() == 'playlist.new') {

			static::$data['movies'] = array_filter(static::$data['movies'], function($i) {
				return $i->getYear() >= 2014;
			});
			static::$data['_page']['title'] = 'Novinky - ' . static::$data['_page']['title'];
			static::$data['title'] = 'Novinky';

		} elseif ($app->router()->getCurrentRoute()->getName() == 'playlist.old') {

			static::$data['movies'] = array_filter(static::$data['movies'], function($i) {
				return $i->getYear() && $i->getYear() <= 1959;
			});
			static::$data['_page']['title'] = 'Pro pamětníky - ' . static::$data['_page']['title'];
			static::$data['title'] = 'Pro pamětníky';

		} elseif ($app->router()->getCurrentRoute()->getName() == 'playlist.newWave') {

			static::$data['movies'] = array_filter(static::$data['movies'], function($i) {
				return
				$i->getYear() && $i->getYear() >= 1960
				&&
				$i->getYear() && $i->getYear() <= 1970
				;
			});
			static::$data['_page']['title'] = 'Česká nová vlna - ' . static::$data['_page']['title'];
			static::$data['title'] = 'Česká nová vlna';

		}

		#var_dump(static::$data['movies']); die;

		return static::render("Homepage/index");
	}

}
