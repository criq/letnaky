<?php

include_once '../vendor/autoload.php';

\Katu\ErrorHandler::init();

try {

	\Katu\App::run();

} catch (\Exception $e) {

	\Katu\ErrorHandler::handle($e);

}
