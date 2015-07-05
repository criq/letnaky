<?php

namespace App\Extensions;

class ErrorHandler extends \Katu\ErrorHandler {

	static function resolveException($e) {
		try {

			$app = \Katu\App::get();

			throw $e;

		// Not found.
		} catch (\Katu\Exceptions\NotFoundException $e) {

			$app->response->setStatus(404);

			return \Katu\Controller::renderNotFound();

		// Unauthorized.
		} catch (\Katu\Exceptions\UnauthorizedException $e) {

			$app->response->setStatus(401);

			return \Katu\Controller::renderUnauthorized();

		// Bad request.
		} catch (\Katu\Exceptions\UserErrorException $e) {

			$app->response->setStatus(400);

			return \Katu\Controller::renderError();

		// Another error.
		} catch (\Exception $e) {

			if (@!error_log($e)) {
				file_put_contents(ERROR_LOG, $e, FILE_APPEND | LOCK_EX);
			}

			$app->response->setStatus(500);

			return \Katu\Controller::renderError();

		}
	}

}
