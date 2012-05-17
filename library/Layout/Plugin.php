<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 07.07.11
 * Time: 4:01
 */

require_once('Layout.php');
require_once('FrontController/Plugin/Abstract.php');

class Layout_Plugin extends FrontController_Plugin_Abstract {

	public function after(Request $request, Response $response){
		$layout = Layout::getInstance();
		$mode_suffix = $request->getMode() ? 'Mode'.$request->getMode() : '';
		$module_suffix = $request->getModule() ? 'Module'.$request->getModule() : '';
		$init_method = '_init'.$layout->getTemplateName();
		$init_mode_method = $init_method.$mode_suffix.$module_suffix;
		if (method_exists($this, $init_mode_method)) {
			$this->$init_mode_method();
		}
		if (method_exists($this, $init_method)) {
			$this->$init_method();
		}
		$layout->render();
	}


}