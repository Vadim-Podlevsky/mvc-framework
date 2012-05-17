<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 05.07.11
 * Time: 1:57
 */
 
class Response {

	/**
	 * @var array
	 */
	private $_status_code_messages = array(
		200 => 'OK',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		500 => 'Internal Server Error'
	);

	private $_status_code = 200;

	/**
	 * @var array
	 */
	private $_content_type_strings = array(
		'json' => 'text/javascript',
		'html' => 'text/html',
	);

	/**
	 * @var string
	 */
	private $_body;

	/**
	 * @var Response
	 */
	private static $_instance;

	/**
	 * @return Response
	 */
	public static function getInstance(){
		if (!isset(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * @param string $text
	 * @return void
	 */
	public function setBody($text){
		$this->_body = $text;
	}

	/**
	 * @param string $text
	 * @return void
	 */
	public function appendBody($text){
		$this->_body .= $text;
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return $this->_body;
	}

	/**
	 * @param string $content_type
	 * @return void
	 */
	public function send($content_type = 'html'){
		if (!headers_sent()) {
			$this->setContentType($content_type);
		}
		echo $this->_body;
	}

	/**
	 * @param string $url
	 * @return void
	 */
	public function redirect($url){
		header('Location: '.$url);
		exit;
	}

	/**
	 * @throws Response_Exception
	 * @param string $type
	 * @param string $charset
	 * @return void
	 */
	public function setContentType($type = 'html', $charset = null){
		if (!isset($this->_content_type_strings[$type])) {
			throw new Response_Exception('Content type "'.$type.'" is not set');
		}
		$header = 'Content-type: '.$this->_content_type_strings[$type];
		if (!$charset) {
			$charset = Config::get('encoding');
		}
		header($header .= '; charset='.$charset);
	}

	/**
	 * @return void
	 */
	public function disableCache(){
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sun, 26 May 1985 12:00:00 GMT");
	}

	/**
	 * @param int $code
	 * @return void
	 */
	public function setStatusCode($code){
		if (isset($this->_status_code_messages[$code])) {
			$this->_status_code = $code;
			header("HTTP/1.0 ".$code." ".$this->_status_code_messages[$code]);
		}
	}

	/**
	 * @return int
	 */
	public function getStatusCode(){
		return $this->_status_code;
	}


}

class Response_Exception extends FrameworkException{}