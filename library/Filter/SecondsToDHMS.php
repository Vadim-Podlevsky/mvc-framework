<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vadim
 * Date: 07.09.11
 * Time: 20:29
 */
 
class Filter_SecondsToDHMS {

	public function filter($form_data, $element_name){
		if (isset($form_data[$element_name])) {
			$seconds = $form_data[$element_name];
			$days = floor($seconds / 86400);
			$seconds = $seconds % 86400;
			$hours = floor($seconds / 3600);
			$seconds = $seconds % 3600;
			$minutes = floor($seconds / 60);
			$seconds = $seconds % 60;
			$result = array($days, $hours, $minutes, $seconds);
			foreach ($result as $k => $number) {
				$result[$k] = str_pad($number, 2, '0', STR_PAD_LEFT);
			}
			return $result;
		}
	}

}
