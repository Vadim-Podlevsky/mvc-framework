<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 07.07.11
 * Time: 3:15
 */
require_once('Collection.php');

class FrontController_Plugin_Collection extends Collection {

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return void
	 */
	public function before(Request $request, Response $response){
		foreach ($this->_entities as $plugin) {
			/** @var $plugin FrontController_Plugin_Abstract */
			$plugin->before($request, $response);
		}
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return void
	 */
	public function after(Request $request, Response $response){
		foreach ($this->_entities as $plugin) {
			/** @var $plugin FrontController_Plugin_Abstract */
			$plugin->after($request, $response);
		}
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param Exception $e
	 * @return void
	 */
	public function _catch(Request $request, Response $response, Exception $e){
		foreach ($this->_entities as $plugin) {
			/** @var $plugin FrontController_Plugin_Abstract */
			$plugin->_catch($request, $response, $e);
		}
	}

}
