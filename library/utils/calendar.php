<?php
/**
* Helper for displaying calendar based navigation
*
* @package Framework
* @subpackage Helper
*
*/

final class CalendarHelper {

	private static $startDate = false;
	private static $endDate   = false;
	
	private static function constructDays ($date = array()) {
		$year  = $date['year'];
		$month = $date['month'];
		$day   = $date['day'];
		$daysQty  = self::calculateDaysInMonth ($month, $year);
		$startDay = self::calculateStartDay ($month, $year);
		$endDay   = self::calculateEndDay ($month, $year, $daysQty);
		$i       = 1;
		$dayNum  = 1;
		$days    = array();
		while ($i < 7) {
			$j = 1;
			while ($j < 8) {
				if ($j < $startDay and $i == 1) $days[$i][$j] = '';
				elseif ($dayNum > $daysQty) $days[$i][$j] = '';
				else {
					$days[$i][$j] = $dayNum;
					$dayNum++;
				}
				$j++;
			}
			if ($dayNum > $daysQty) break;
			$i++;
		}
		return $days;
	}
	
	public static function renderXMLCalendar ($date = array(), $availableDates = array(), $controller, $action, $arguments = array()) {
		$year  = $date['year'];
		$month = $date['month'];
		$day   = $date['day'];
		$data = self::constructDays ($date);
		$output  = '<calendar>';
		$output .= '<currentYear>'.$year.'</currentYear>';
		$output .= '<currentMonth>'.date('F', mktime(1, 1, 1, $month, $day, $year)).'</currentMonth>';
		$output .= self::renderXMLCalendarNavigation ($controller, $action, $arguments, $year, $month, $day);
		$arguments['year']  = $year;
		$arguments['month'] = $month;
		foreach ($data as $week) {
			$i = 1;
			while ($i < 8) {
				//if (isset($arguments['day'])) unset($arguments['day']);
				$arguments['day'] = (isset($week[$i]) ? $week[$i] : '');
				$output .= '<item>';
				$output .= '<dayNum>'.$i.'</dayNum>';
				$output .= '<day>'.(isset($week[$i]) ? $week[$i] : '').'</day>';
				if (in_array($month.'/'.(isset($week[$i]) ? $week[$i] : '').'/'.$year, $availableDates)) $output .= '<link>'.URIHelper::constructUrl($controller, $action, $arguments).'</link>';
				$output .= '</item>';
				$i++;
			}
		}
		$output .= '</calendar>';
		return $output;
	}
	
	private static function renderXMLCalendarNavigation ($controller, $action, $arguments, $year, $month, $day) {
		$arguments['year']  = $year + 1;
		$output  = '<nextYear>'.(self::$endDate ? (mktime(1, 1, 1, 1, 1, $year + 1) > self::$endDate ? '' : URIHelper::constructUrl($controller, $action, $arguments)) : URIHelper::constructUrl($controller, $action, $arguments)).'</nextYear>';
		$arguments['year']  = $year - 1;
		$output .= '<previousYear>'.(self::$startDate ? (mktime(1, 1, 1, 1, 1, $year - 1) < self::$startDate ? '' : URIHelper::constructUrl($controller, $action, $arguments)) : URIHelper::constructUrl($controller, $action, $arguments)).'</previousYear>';
		$arguments['year']  = $month == 12 ? $year + 1 : $year;
		$arguments['month'] = $month == 12 ? 1 : $month + 1;
		$output .= '<nextMonth>'.(self::$endDate ? (mktime(1, 1, 1, $arguments['month'], 1, $arguments['year']) > self::$endDate ? '' : URIHelper::constructUrl($controller, $action, $arguments)) : URIHelper::constructUrl($controller, $action, $arguments)).'</nextMonth>';
		$arguments['year']  = $month == 1 ? $year - 1 : $year;
		$arguments['month'] = $month == 1 ? 12 : $month - 1;
		$output .= '<previousMonth>'.(self::$startDate ? (mktime(1, 1, 1, $arguments['month'], 1, $arguments['year']) < self::$startDate ? '' : URIHelper::constructUrl($controller, $action, $arguments)) : URIHelper::constructUrl($controller, $action, $arguments)).'</previousMonth>';
		$nextDay = strtotime('+1 day', mktime(1, 1, 1, $month, $day, $year));
		$prevDay = strtotime('-1 day', mktime(1, 1, 1, $month, $day, $year));
		$arguments['day']   = date('j', $nextDay);
		$arguments['year']  = date('Y', $nextDay);
		$arguments['month'] = date('n', $nextDay);
		$output .= '<nextDay>'.(self::$endDate ? ($nextDay > self::$endDate ? '' : URIHelper::constructUrl($controller, $action, $arguments)) : URIHelper::constructUrl($controller, $action, $arguments)).'</nextDay>';
		$arguments['day']   = date('j', $prevDay);
		$arguments['year']  = date('Y', $prevDay);
		$arguments['month'] = date('n', $prevDay);
		$output .= '<prevDay>'.(self::$startDate ? ($prevDay < self::$startDate ? '' : URIHelper::constructUrl($controller, $action, $arguments)) : URIHelper::constructUrl($controller, $action, $arguments)).'</prevDay>';
		return $output;
	}

	public function setStartDate ($year, $month = 1, $day = 1) {
		self::$startDate = mktime (1, 1, 1, $month, $day, $year);
	}

	public function setEndDate ($year, $month = 1, $day = 1) {
		self::$endDate = mktime (1, 1, 1, $month, $day, $year);
	}

	private static function calculateDaysInMonth ($month, $year) {
		return date('t', mktime(1, 1, 1, $month, 1, $year));
	}
	
	private static function calculateStartDay ($month, $year) {
		$startDay = date('w', mktime(1, 1, 1, $month, 1, $year));
		if ($startDay == 0) $startDay = 7;
		return $startDay;
	}
	
	private static function calculateEndDay ($month, $year, $daysInMonth) {
		$endDay = date('w', mktime(1, 1, 1, $month, $daysInMonth, $year));
		if ($endDay == 0) $endDay = 7;
		return $endDay;
	}
	
	
}
?>