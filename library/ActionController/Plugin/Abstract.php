<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 10.07.11
 * Time: 22:23
 */
 
abstract class ActionController_Plugin_Abstract {

	/**
	 * @return string
	 */
	public function getName(){
		return get_class($this);
	}

	/**
	 * @param ActionController $action_controller
	 * @param string $action_method
	 * @return void
	 */
	public function before(ActionController $action_controller, $action_method){}

	/**
	 * @param ActionController $action_controller
	 * @param string $action_method
	 * @return void
	 */
	public function after(ActionController $action_controller, $action_method){}

}
