<?php

use \Katu\Route;

return [
	'homepage' => Route::create('/', 'Homepage', 'index'),
	'playlist.new' => Route::create('/novinky', 'Homepage', 'index'),
	'playlist.old' => Route::create('/pro-pametniky', 'Homepage', 'index'),
	'playlist.newWave' => Route::create('/ceska-nova-vlna', 'Homepage', 'index'),
];
