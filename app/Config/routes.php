<?php

use \Katu\Route;

return [
	'homepage' => Route::create('/', 'Homepage', 'index'),
	'addToCalendar' => Route::create('/do-kalendare/:movieHash', 'Homepage', 'addToCalendar'),
	'playlist.new' => Route::create('/novinky', 'Homepage', 'index'),
	'playlist.old' => Route::create('/pro-pametniky', 'Homepage', 'index'),
	'playlist.newWave' => Route::create('/ceska-nova-vlna', 'Homepage', 'index'),
	'publish.facebook' => Route::create('/publish/facebook', 'Homepage', 'publishToFacebook'),
	'publish.twitter' => Route::create('/publish/twitter', 'Homepage', 'publishToTwitter'),
];
