<?php

switch (\Katu\Env::getPlatform()) {

	case 'dev' :

		return [

			'baseUrl'  => 'http://letnaky.local/',
			'apiUrl'   => 'http://letnaky.local/',
			'timezone' => 'Europe/Prague',

			'slim' => [
				'mode'  => 'development',
				'debug' => false,
			],

			'pagination' => [
				'pageIdent' => 'page',
			],

			'files' => [
				'dir' => 'files',
			],

			'tmp' => [
				'publicDir' => 'public/tmp',
				'publicUrl' => 'tmp',
			],

		];

	break;
	case 'prod' :

		return [

			'baseUrl'  => 'http://letnaky.zitbrno.cz/',
			'apiUrl'   => 'http://letnaky.zitbrno.cz/',
			'timezone' => 'Europe/Prague',

			'slim' => [
				'mode'  => 'production',
				'debug' => false,
			],

			'pagination' => [
				'pageIdent' => 'page',
			],

			'files' => [
				'dir' => 'files',
			],

			'tmp' => [
				'publicDir' => 'public/tmp',
				'publicUrl' => 'tmp',
			],

		];

	break;

}
