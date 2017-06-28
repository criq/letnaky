<?php

use \Katu\Route;

return [
	'homepage' => Route::create('/', 'Homepage', 'index'),
	'clearCache' => Route::create('/clear-cache', 'Homepage', 'clearCache'),
	'addToCalendar' => Route::create('/do-kalendare/:movieHash', 'Homepage', 'addToCalendar'),
	'publish.facebook' => Route::create('/publish/facebook', 'Homepage', 'publishToFacebook'),
	'publish.twitter' => Route::create('/publish/twitter', 'Homepage', 'publishToTwitter'),
];
