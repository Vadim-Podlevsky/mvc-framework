<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 01.07.11
 * Time: 4:40
 */
 
class Timer {

	const DEFAULT_SCOPE = 'default';

	private static $_time;

	/**
	 * @static
	 * @param string $scope
	 * @return void
	 */
	public static function init($scope = self::DEFAULT_SCOPE){
		self::$_time[$scope] = microtime(true);
	}

	/**
	 * @static
	 * @param string $scope
	 * @return mixed
	 */
	public static function getTime($scope = self::DEFAULT_SCOPE){
		return microtime(true) - self::$_time[$scope];
	}
}
