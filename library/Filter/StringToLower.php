<?php

class Filter_StringToLower {
	
	public function filter($form_data, $element_name){
		if (isset($form_data[$element_name])) {
			return strtolower($form_data[$element_name]);
		}
		return null;
	}
	
}