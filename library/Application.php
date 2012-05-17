<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 30.06.11
 * Time: 22:48
 */

require_once('functions.inc.php');
require_once('Config.php');
require_once('Session.php');
require_once('Route.php');
require_once('Router.php');
require_once('Request.php');
require_once('Response.php');
require_once('ExceptionHandler.php');
require_once('FrontController.php');

class Application {

	public function __construct () {
		$methods = get_class_methods($this);
		foreach ($methods as $method) {
			if (strpos($method, '_init') !== false) {
				$this->$method();
			}
		}
	}

	public function run () {
		if (Request::isCli()) {
			return;
		}
		FrontController::getInstance()->dispatch(Request::getInstance(), Response::getInstance());
	}

	public function display () {
		Response::getInstance()->send();
	}

}