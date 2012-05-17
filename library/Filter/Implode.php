<?php

class Filter_Implode {

	/**
	 * @param array $form_data
	 * @param string $element_name
	 * @param string $delimiter
	 * @return null|string
	 */
	public function filter($form_data, $element_name, $delimiter = ''){
		$result = null;
		if (is_array($element_name)){
			$return_value = array();
			$is_all_elements_empty = true;
			foreach ($element_name as $field_name) {
				$is_all_elements_empty &= empty($form_data[$field_name]);
				$return_value[] = isset($form_data[$field_name]) ? $form_data[$field_name] : '';
			}
			$result = $is_all_elements_empty ? '' : implode($delimiter, $return_value);
		} else if (is_array($form_data[$element_name])) {
			$result = implode($delimiter, $form_data[$element_name]);
		}
		return $result;
	}
	
}