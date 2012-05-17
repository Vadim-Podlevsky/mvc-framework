<?php

class Filter_DateTimeToUnixTime {

	/**
	 * @param array $form_data
	 * @param string $element_name
	 * @param string $locale [optional]
	 * @return int
	 */
	public function filter($form_data, $element_name, $locale = null){
		if (!empty($form_data[$element_name])) {
			if (!isset($locale)) {
				$locale = Request::getInstance()->getLocale();
			}
			$info = $this->getMDY($form_data[$element_name], $locale);
			return mktime ($info['hour'], $info['minute'], 0, $info['month'], $info['day'], $info['year']);
		}
	}

	/**
	 * @param  $value
	 * @param  $locale
	 * @return array
	 */
	protected function getMDY($value, $locale){
		return date_parse_from(Locale::getInstance()->getDateParseFromFormat($locale).' HH:ii', $value);
	}

}