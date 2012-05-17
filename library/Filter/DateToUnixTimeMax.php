<?php

class Filter_DateToUnixTimeMax {

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
			list($month, $day, $year) = $this->getMDY($form_data[$element_name], $locale);
			return $this->toTime($month, $day, $year);
		}
	}

	/**
	 * @param  $value
	 * @param  $locale
	 * @return array
	 */
	protected function getMDY($value, $locale){
		$info = date_parse_from(Locale::getInstance()->getDateParseFromFormat($locale), $value);
		return array($info['month'], $info['day'], $info['year']);
	}

	/**
	 * @param int $month
	 * @param int $day
	 * @param int $year
	 * @return int
	 */
	protected function toTime($month, $day, $year){
		return mktime (23, 59, 59, $month, $day, $year);
	}
	
}