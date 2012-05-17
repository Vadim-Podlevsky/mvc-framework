<?php

class Filter_UnixTimeToDateTime {

	/**
	 * @param array $form_data
	 * @param string $element_name
	 * @param string $locale [optional]
	 * @return string
	 */
	public function filter($form_data, $element_name, $locale = null){
		if (isset($form_data[$element_name])) {
			return date(Locale::getInstance()->getDateFormat($locale).' H:i', $form_data[$element_name]);
		}
	}
	
}