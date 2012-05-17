<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vadim
 * Date: 05.09.11
 * Time: 16:25
 * To change this template use File | Settings | File Templates.
 */
 
class Locale {

	/**
	 * @var string
	 */
	private $_default_locale;

	/**
	 * @var array
	 */
	private $_locale_data;

	/**
	 * @var Locale
	 */
	private static $_instance;

	/**
	 * @return Locale
	 */
	public static function getInstance(){
		if (!isset(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * @param string $default_locale
	 * @param array $locales
	 * @return void
	 */
	public function init($default_locale, $locales){
		$this->setDefaultLocale($default_locale);
		$this->initLocales($locales);
	}

	/**
	 * Load default locales
	 */
	protected function __construct(){}

	/**
	 * @param array $locales
	 * @return void
	 */
	public function initLocales($locales){
		foreach ($locales as $locale_data) {
			$this->initLocale($locale_data->code, $locale_data);
		}
	}

	/**
	 * Just for convenience
	 * @return string
	 */
	public function get(){
		return $this->getDefaultLocale();
	}

	/**
	 * @param string $locale
	 * @return bool
	 */
	public function isLocale($locale){
		return in_array($locale, array_keys($this->_locale_data));
	}

	/**
	 * @param string $locale
	 * @param array $data
	 * @return void
	 */
	public function initLocale($locale, $data){
		$this->_locale_data[$locale] = $data;
	}

	/**
	 * @param  $locale
	 * @return void
	 */
	public function setDefaultLocale($locale){
		$this->_default_locale = $locale;
	}

	/**
	 * @return string
	 */
	public function getDefaultLocale(){
		return $this->_default_locale;
	}

	/**
	 * @param null $locale
	 * @return string
	 */
	public function getTitle($locale = null){
		return $this->getLocaleData($locale)->title;
	}

	/**
	 * @param string $locale
	 * @return string
	 */
	public function getDateFormat($locale = null){
		return $this->getLocaleData($locale)->dateFormat;
	}

	/**
	 * @param null $locale
	 * @return string
	 */
	public function getDateParseFromFormat($locale = null){
		return $this->getLocaleData($locale)->dateParseFromFormat;
	}

	/**
	 * @param null $locale
	 * @return string
	 */
	public function getJsDateFormat($locale = null){
		return $this->getLocaleData($locale)->jsDateFormat;
	}

	/**
	 * @param null $locale
	 * @return object
	 */
	public function getLocaleData($locale = null){
		if (!$locale || !isset($this->_locale_data[$locale])) {
			$locale = $this->_default_locale;
		}
		return $this->_locale_data[$locale];
	}

	/**
	 * @param null $locale
	 * @return array array($thousand_sep, $decimal_sep, $decimal_digit)
	 */
	public function getMoneyFormat($locale = null){
		$format_string = $this->getLocaleData($locale)->moneyFormat;
		return explode('|', trim($format_string, '"'));
	}

	/**
	 * @param string $locale
	 * @return string
	 */
	public function getCurrency($locale = null){
		return $this->getLocaleData($locale)->currency;
	}

	/**
	 * @param null $locale
	 * @return string
	 */
	public function getCurrencyCode($locale = null){
		return $this->getLocaleData($locale)->currencyCode;
	}

}