<?php

namespace App\Extensions;

use \Katu\Utils\Formatter;

class View extends \Katu\View {

	static function extendTwig($twig) {
		\Kleur\Kleur::extendTwig($twig);

		$twig->addFilter(new \Twig_SimpleFilter('localPercent', function($number) {
			return Formatter::getLocalPercent(null, $number);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('localDate', function($date) {
			$locale = \Locale::acceptFromHttp(\Katu\App::get()->request->headers->get('Accept-Language'));

			$intlDateFormatter = new \IntlDateFormatter($locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE);
			try {
				$intlDateFormatter->setTimeZoneId(\Katu\Config::get('app', 'timezone'));
			} catch (\Exception $e) {
				$intlDateFormatter->setTimeZone(\Katu\Config::get('app', 'timezone'));
			}

			if (is_string($date)) {
				$date = new \DateTime($date);
			}

			return $intlDateFormatter->format($date);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('localDateTime', function($date) {
			$locale = \Locale::acceptFromHttp(\Katu\App::get()->request->headers->get('Accept-Language'));

			$intlDateFormatter = new \IntlDateFormatter($locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT);
			try {
				$intlDateFormatter->setTimeZoneId(\Katu\Config::get('app', 'timezone'));
			} catch (\Exception $e) {
				$intlDateFormatter->setTimeZone(\Katu\Config::get('app', 'timezone'));
			}

			if (is_string($date)) {
				$date = new \DateTime($date);
			}

			return $intlDateFormatter->format($date);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('average', function($array) {
			return array_sum($array) / count($array);
		}));

	}

}
