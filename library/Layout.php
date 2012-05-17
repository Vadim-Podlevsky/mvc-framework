<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 05.07.11
 * Time: 1:43
 */
 
class Layout {

	private $_title;

	private $_ns = array();

	private $_meta = array();

	private $_css = array();
	
	private $_js = array();

	/**
	 * @var bool
	 */
	private $_is_enabled = true;

	/**
	 * @var string
	 */
	private $_template_name = 'default';

	/**
	 * @var View
	 */
	private $_view;

	/**
	 * @var Layout
	 */
	private static $_instance;

	/**
	 * @return Layout
	 */
	public static function getInstance(){
		if (!isset(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	protected function __construct(){
		$this->_initView(null, Request::getInstance(), Response::getInstance());
	}

	/**
	 * @param null $view_driver
	 * @param Request $request
	 * @param Request $response
	 * @return void
	 */
	protected function _initView($view_driver = null, $request, $response){
		$this->_view = new View($view_driver, $request, $response);
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title){
		$this->_title = $title;
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function appendTitle($title){
		$this->_title .= $title;
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function prependTitle($title){
		$this->_title = $title.$this->_title;
	}

	/**
	 * @return string
	 */
	public function getTitle(){
		return htmlspecialchars($this->_title);
	}

	/**
	 * @param string $ns
	 * @param string $url
	 * @return void
	 */
	public function addNs($ns, $url) {
		$this->_ns[] = array('ns'=>$ns, 'url'=>$url);
	}

	/**
	 * @return void
	 */
	public function renderNs(){
		foreach ($this->_ns as $ns) {
			echo $ns['ns'].'="'.$ns['url'].'" ';
		}
	}

	/**
	 * @param string $name
	 * @param string $content
	 * @param string $property
	 * @return void
	 */
	public function addMeta($name = '', $content, $property = ''){
		$this->_meta[$name.$property] = array('name'=>$name, 'content'=>strip_tags($content), 'property'=>$property);
	}

	/**
	 * @param string $name
	 * @param string $content
	 * @param string $property
	 * @return void
	 */
	public function appendMeta($name = '', $content, $property = '') {
		if (isset($this->_meta[$name.$property])) {
			$this->_meta[$name.$property]['content'] .= $content;
		} else {
			$this->addMeta($name, $content, $property);
		}
	}

	/**
	 * @return void
	 */
	public function renderMeta(){
		foreach ($this->_meta as $meta) {
			echo "\t".'<meta ';
			if ($meta['name']) {
				echo 'name="'.$meta['name'].'" ';
			}
			if ($meta['property']) {
				echo 'property="'.$meta['property'].'" ';
			}
			echo 'content="'.htmlspecialchars($meta['content']).'" ';
			echo '/>'."\n\r";;
		}
	}

	/**
	 * @param  $css_url
	 * @param string $media
	 * @param string $rel
	 * @param string $type
	 * @return void
	 */
	public function addCss($css_url, $media = '', $rel = 'stylesheet', $type = 'text/css'){
		$this->_css[] = array('href'=>$css_url, 'media'=>$media, 'rel'=>$rel, 'type'=>$type);
	}

	/**
	 * @return void
	 */
	public function renderCss(){
		foreach ($this->_css as $css) {
			echo "\t".'<link rel="'.$css['rel'].'"';
			if ($css['media']) {
				echo ' media="'.$css['media'].'"';
			}
			echo ' type="'.$css['href'].'" href="'.$css['href'].'" />'."\n\r";
		}
	}

	/**
	 * @param string $script_url
	 * @return void
	 */
	public function addJs($script_url){
		$this->_js[$script_url] = array('src'=>$script_url);
	}

	/**
	 * @return void
	 */
	public function renderJs(){
		foreach ($this->_js as $js) {
			echo "\t".'<script type="text/javascript" src="'.$js['src'].'"></script>'."\n\r";
		}
	}

	/**
	 * @return View
	 */
	public function getView(){
		return $this->_view;
	}

	/**
	 * @param bool $flag
	 * @return bool
	 */
	public function enabled($flag = null){
		if ($flag !== null) {
			$this->_is_enabled = $flag;
		}
		return $this->_is_enabled;
	}

	/**
	 * Convenient alias
	 * @return void
	 */
	public function disable(){
		$this->enabled(false);
	}

	/**
	 * @param string $template_name
	 * @return void
	 */
	public function setTemplateName($template_name){
		$this->_template_name = $template_name;
	}

	/**
	 * @return string
	 */
	public function getTemplateName(){
		return $this->_template_name;
	}

	/**
	 * @param  $name
	 * @param  $value
	 * @return void
	 */
	public function setData($name, $value){
		$this->_view->setData($name, $value);
	}

	/**
	 * @param string $tag_name
	 * @param array $base_parameters
	 * @param array $parameters
	 * @return void
	 */
	public function loadBlock($tag_name, $base_parameters = array(), $parameters = array()){
		if ($this->_view->hasData($tag_name)) {
			return;
		}
		$this->_view->loadBlock($tag_name, $base_parameters, $parameters);
	}

	/**
	 * @return void
	 */
	public function render(){
		if (!$this->enabled()) {
			return;
		}
		$this->_view->setData('content', $this->_view->getResponse()->getBody());
		$this->_view->render($this->_template_name, 'layouts');
	}

}
