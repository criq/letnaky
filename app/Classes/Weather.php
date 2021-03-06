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
		#var_dump($this->item->weather[0]->description);

		try {

			return \Katu\Config::get('weather', $this->item->weather[0]->description);

		} catch (\Exception $e) {

			try {

				$res = \Katu\Cache\Url::get(\Katu\Types\TUrl::make('https://www.googleapis.com/language/translate/v2', [
					'key' => \Katu\Keychain::get('google', 'api', 'key'),
					'target' => 'cs',
					'q' => $this->item->weather[0]->description,
				]));

				return $res->data->translations[0]->translatedText;

			} catch (\Exception $e) {

				return $this->item->weather[0]->description;

			}

		}
	}

	public function getRain() {
		if (isset($this->item->rain)) {
			$rain = ((array) $this->item->rain);
			if (isset($rain['3h'])) {
				return $rain['3h'];
			}
		}

		return 0;
	}

}
