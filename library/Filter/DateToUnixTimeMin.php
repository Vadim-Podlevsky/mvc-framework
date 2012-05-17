<?php

require_once('Filter/DateToUnixTimeMax.php');

class Filter_DateToUnixTimeMin extends Filter_DateToUnixTimeMax {
	
	/**
	 * @param int $month
	 * @param int $day
	 * @param int $year
	 * @return int
	 */
	protected function toTime($month, $day, $year){
		return mktime (0, 0, 0, $month, $day, $year);
	}
	
}