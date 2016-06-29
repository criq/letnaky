<?php

namespace App\Controllers;

class Homepage extends \Katu\Controller {

	static function index() {
		$app = \Katu\App::get();

		$src = \Katu\Utils\Cache::get(['homepage', (new \Katu\Utils\DateTime)->format('Y-m-d')], function() use($app) {

			$data['movies'] = (array) \App\Classes\Movie::getAll(86400);

			$data['_page']['title'] = 'Letňáky v Brně';

			$data['movies'] = array_filter($data['movies'], function($i) {
				return $i->dateTime->isInFuture();
			});

			try {
				$url = \Katu\Types\TUrl::make('http://api.openweathermap.org/data/2.5/forecast', [
					'APPID' => '63e4953385780c78c67879c7da52482c',
					'q'     => 'Brno',
					'mode'  => 'json',
					'units' => 'metric',
					'lang'  => 'en',
				]);
				$res = \Katu\Utils\Cache::getUrl($url, 1);

				$data['weather'] = [];
				if (isset($res->list)) {
					foreach ($res->list as $i) {
						$dateTime = new \Katu\Utils\DateTime('@' . $i->dt);
						if ($dateTime->format('Hi') == '2100') {
							$data['weather'][$dateTime->format('Ymd')] = \App\Classes\Weather::createFromApi($i);
						}
					}
				}
			} catch (\Exception $e) {

			}

			return \Katu\View::render("Homepage/index", $data);

		}, 3600 * 3);

		$app->response->setStatus(200);
		$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');
		$app->response->setBody($src);
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
		//$lines[] = 'DESCRIPTION:' . $movie->getPlot();
		$lines[] = 'LOCATION:' . $movie->venue;
		$lines[] = 'URL;VALUE=URI:' . $movie->venueUrl;
		$lines[] = 'END:VEVENT';
		$lines[] = 'END:VCALENDAR';

		$app->response->headers->set('Content-Type', 'text/calendar');
		$app->response->headers->set('Content-Disposition', 'attachment; filename=film.ics');
		$app->response->setBody(implode("\n", $lines));
	}

	static function publishToFacebook() {
		$pageId = 1668385493380758;

		$accessToken = \Katu\Utils\Tmp::get('accessToken');
		if (!$accessToken) {
			$url = \Katu\Utils\Url::getFor('publish.facebook');
			\Katu\Utils\Facebook::login($url, $url, null, ['manage_pages', 'publish_actions', 'publish_pages']);

			$session = \Katu\Utils\Facebook::getSession();

			$request = new \Facebook\FacebookRequest($session, 'GET', '/' . $pageId, [
				'fields' => 'access_token',
			]);
			$accessToken = $request->execute()->getGraphObject()->getProperty('access_token');

			\Katu\Utils\Tmp::set('accessToken', $accessToken);
		}

		try {

			\Katu\Utils\Facebook::setToken($accessToken);
			$session = \Katu\Utils\Facebook::getSession();

			static::$data['movies'] = array_filter(\App\Classes\Movie::getAll(), function($i) {
				return $i->dateTime->isToday();
			});

			foreach (static::$data['movies'] as $movie) {

				$fileStorageName = ['publish', 'facebook', $movie->hash];
				$csfdUrl = $movie->getCsfdUrl();
				if (!\Katu\Utils\FileStorage::get($fileStorageName) && $csfdUrl) {

					$message = [];
					$message[] = $movie->title . ' dnes ve ' . $movie->dateTime->format('H:i') . ' ' . \Katu\Config::get('venues', $movie->venue, 'in') . '.';
					$message[] = '(' . $movie->getRating() * 100 . ' %)';

					$request = new \Facebook\FacebookRequest($session, 'POST', '/' . $pageId . '/feed', [
						'message' => implode(' ', $message),
						'link' => $csfdUrl,
						'place' => \Katu\Config::get('venues', $movie->venue, 'facebookId'),
					]);

					$id = $request->execute()->getGraphObject()->getProperty('id');

					\Katu\Utils\FileStorage::set($fileStorageName, $id);

					break;

				}

			}

		} catch (\Facebook\FacebookPermissionException $e) {
			var_dump($e);
		}

	}

	static function publishToTwitter() {
		static::$data['movies'] = \App\Classes\Movie::getAll();

		var_dump(static::$data['movies']);
	}

}
