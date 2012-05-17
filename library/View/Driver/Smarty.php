<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 04.07.11
 * Time: 21:59
 */

require_once('View/Driver/Abstract.php');
require_once('../externals/smarty/Smarty.class');

class View_Driver_Smarty extends View_Driver_Abstract {
	
	/**
	 * @var Smarty
	 */
	protected $_smarty;
	
	protected $_template_extension = '.tpl';

	public function init(){
		$smarty_config = Config::get()->smarty;
		$this->_smarty = new Smarty();
		$this->_smarty->template_dir = $this->getTemplatesRoot();
		foreach($smarty_config as $parameter_name => $value) {
			$this->_smarty->$parameter_name = $value;
		}
	}

	/**
	 * @throws View_Driver_SmartyException
	 * @param  $templateName
	 * @return string
	 */
	public function render($templateName){
		if (!file_exists($templateName) or !is_file($templateName)) {
			throw new View_Driver_SmartyException('Cannot load template file: '.$templateName);
		}
		foreach ($this->_template_data as $name => $value) {
			$this->_smarty->assign($name, $value);
		}
		return $this->_smarty->fetch($templateName);
	}
}

class View_Driver_SmartyException extends FrameworkException {}