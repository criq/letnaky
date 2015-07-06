<?php

namespace App\Controllers;

class Homepage extends \Katu\Controller {

	static function index() {
		$app = \Katu\App::get();

		static::$data['movies'] = \App\Classes\Movie::getAll();

		#var_dump(static::$data['movies']); die;

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

	static function addToCalendar($movieHash) {
		$app = \Katu\App::get();

		$movie = \App\Classes\Movie::getByHash($movieHash);

		$lines = [];

		$lines[] = 'BEGIN:VCALENDAR';
		$lines[] = 'VERSION:2.0';
		$lines[] = 'CALSCALE:GREGORIAN';
		$lines[] = 'BEGIN:VEVENT';
		$lines[] = 'UID:' . $movie->hash;
		$lines[] = 'DTSTAMP;TZID=Europe/Prague:' . $movie->dateTime->format('Ymd') . 'T' . $movie->dateTime->format('His');
		$lines[] = 'DTSTART;TZID=Europe/Prague:' . $movie->dateTime->format('Ymd') . 'T' . $movie->dateTime->format('His');
		$lines[] = 'DTEND;TZID=Europe/Prague:' . $movie->dateTime->modify('+ ' . $movie->getRuntimeInMinutes() . ' minutes')->format('Ymd') . 'T' . $movie->dateTime->format('His');
		$lines[] = 'SUMMARY:' . $movie->title;
		$lines[] = 'DESCRIPTION:' . $movie->getPlot();
		$lines[] = 'LOCATION:' . $movie->venue;
		$lines[] = 'URL;VALUE=URI:' . $movie->venueUrl;
		$lines[] = 'END:VEVENT';
		$lines[] = 'END:VCALENDAR';

		$app->response->headers->set('Content-Type', 'text/calendar');
		$app->response->headers->set('Content-Disposition', 'attachment; filename=film.ics');
		$app->response->setBody(implode("\n", $lines));
	}

}
