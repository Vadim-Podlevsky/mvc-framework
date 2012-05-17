<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 10.07.11
 * Time: 22:23
 */
 
abstract class FrontController_Plugin_Abstract {

	/**
	 * @return string
	 */
	public function getName(){
		return get_class($this);
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return void
	 */
	public function before(Request $request, Response $response){}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return void
	 */
	public function after(Request $request, Response $response){}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param Exception $e
	 * @return void
	 */
	public function _catch(Request $request, Response $response, Exception $e){}

}
