<?php

class Filter_Money {

	/**
	 * @param array $form_data
	 * @param string $element_name
	 * @param string $locale [optional]
	 * @return string
	 */
	public function filter($form_data, $element_name, $locale = null){
		if (isset($form_data[$element_name])) {
			$money_format_array = Locale::getInstance()->getMoneyFormat($locale);
			if (!is_array($money_format_array) && sizeof($money_format_array) != 3) {
				return $form_data[$element_name];
			}
			list($thousand_separator, $decimal_separator, $decimals) = $money_format_array;
			return number_format($form_data[$element_name], $decimals, $decimal_separator, $thousand_separator);
		}
	}

}