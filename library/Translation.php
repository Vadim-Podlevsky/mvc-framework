<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 09.08.11
 * Time: 21:21
 */
 
class Translation {

	/**
	 * @var Translation
	 */
	private static $_instance;

	/**
	 * @var string
	 */
	private $_locale;

	/**
	 * @var array
	 */
	private $_translations;

	/**
	 * @return Translation
	 */
	public static function getInstance(){
		if (!isset(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	protected function __construct(){
	}

	/**
	 * @param  $translation_file
	 * @param  $locale
	 * @return void
	 */
	public function addTranslation($translation_file, $locale){
		$translation = FileLoader::loadFile($translation_file);
		if (is_array($translation)) {
			$this->_translations[$locale] = $translation;
		}
	}

	/**
	 * @param string $mode
	 * @param null $locale
	 * @return void
	 */
	public function loadTranslation($mode, $locale = null){
		$locale = $locale ? $locale : $this->_locale;
		$translation_file = 'translations/en_'.$locale.'.php';
		if (Config::get()->isModesEnabled && $mode != Config::get()->defaultMode) {
			$translation_file = 'modes/'.$mode.'/'.$translation_file;
		}
		$this->addTranslation($translation_file, $locale);
	}

	/**
	 * @param  $locale
	 * @return void
	 */
	public function setLocale($locale) {
		$this->_locale = $locale;
	}

	/**
	 * @param string $text
	 * @param null $locale
	 * @return string
	 */
	public function _($text, $locale = null){
		$locale = $locale ? $locale : $this->_locale;
		return isset($this->_translations[$locale][$text]) ? $this->_translations[$locale][$text] : $text; 
	}

	/**
	 * @static
	 * @param string $text
	 * @param null $locale
	 * @return string
	 */
	public static function translate($text, $locale = null) {
		return self::getInstance()->_($text, $locale);
	} 

}
