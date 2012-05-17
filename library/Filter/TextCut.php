<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vadim
 * Date: 19.09.11
 * Time: 19:23
 */
 
class Filter_TextCut {

	private $_strlen;
	private $_substr;

	public function __construct(){
		$this->_strlen = function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';
		$this->_substr = function_exists('mb_substr') ? 'mb_substr' : 'substr';
	}

	public function filter($form_data, $element_name, $limit, $cut_ending = '...', $more = null, $more_always = true){
		if (isset($form_data[$element_name])) {
			$offset = 0;
			$text = rtrim(str_replace('&nbsp;', ' ',($form_data[$element_name])));
			if ($this->_strlen($text) > $limit) {
				$whole_word_limit = strrpos($text, ' ', -($this->_strlen($text) - $limit));
				$text = rtrim($this->_substr($text, $offset, $whole_word_limit), ',');
				if (!$this->endsWithDot($text) && isset($cut_ending)) {
					$text .= $cut_ending;
				}
				if (isset($more)) {
					$this->addMore($text, $more);
				}
			} else if (isset($more) && $more_always) {
				$this->addMore($text, $more);
			}
			return $text;
		}
	}

	/**
	 * @param string $name
	 * @param array $_arguments
	 * @return mixed
	 */
	public function __call($name, $_arguments) {
		if (!isset($this->$name)) {
			return;
		}
		$function = $this->$name;
		return call_user_func_array($function, $_arguments);
	}

	/**
	 * @param string $text
	 * @param string $more
	 * @return void
	 */
	private function addMore(&$text, $more){
		if ($this->endsWithDot($text)) {
			$more = $this->ucMore($more);
		}
		$text .= $more;
	}

	/**
	 * @param string $text
	 * @return bool
	 */
	private function endsWithDot($text){
		return $this->_substr($text, -1) === '.';
	}

	/**
	 * @param string $more
	 * @return mixed
	 */
	private function ucMore($more){
		$more_text = trim(strip_tags($more));
		return str_replace($more_text, ucfirst($more_text), $more);
	}

}