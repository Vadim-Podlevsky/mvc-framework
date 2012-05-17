<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 07.07.11
 * Time: 3:15
 */
require_once('Collection.php');

class ActionController_Plugin_Collection extends Collection {

	/**
	 * @param ActionController $action_controller
	 * @param string $action_method
	 * @return void
	 */
	public function before(ActionController $action_controller, $action_method){
		foreach ($this->_entities as $plugin) {
			/** @var $plugin ActionController_Plugin_Abstract */
			$plugin->before($action_controller, $action_method);
		}
	}

	/**
	 * @param ActionController $action_controller
	 * @param string $action_method
	 * @return void
	 */
	public function after(ActionController $action_controller, $action_method){
		foreach ($this->_entities as $plugin) {
			/** @var $plugin ActionController_Plugin_Abstract */
			$plugin->after($action_controller, $action_method);
		}
	}

}
