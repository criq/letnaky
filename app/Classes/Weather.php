<?php

namespace App\Classes;

class Weather {

	static function createFromApi($item) {
		$object = new static;

		$object->item = $item;

		return $object;
	}

	public function getTemperature() {
		return round($this->item->main->temp);
	}

	public function getWeather() {
		try {

			return \Katu\Config::get('weather', $this->item->weather[0]->description);

		} catch (\Exception $e) {

			$res = \Katu\Utils\Cache::getUrl(\Katu\Types\TUrl::make('https://www.googleapis.com/language/translate/v2', [
				'key' => \Katu\Keychain::get('google', 'api', 'key'),
				'target' => 'cs',
				'q' => $this->item->weather[0]->description,
			]));

			return $res->data->translations[0]->translatedText;

		}
	}

}
