<?php

require_once('Logger/Exception.php');
require_once('Logger/Writer/Abstract.php');

class Logger {
	
	/**
	 * @var array
	 */
	protected static $_type_color_codes = array(
		'default'	=>'default',
		'ok'		=>'green',
		'debug'		=>'yellow',
		'warning'	=>'magenta',
		'error'		=>'red',
		'fatal'		=>'red',
	);
	
	/**
	 * @var array
	 */
	protected static $_writers = array();
	
	/**
	 * @var bool
	 */
	protected static $_enabled = true;

	/**
	 * @var string
	 */
	protected static $_scope = 'global';

	/**
	 * @static
	 * @param  $scope
	 * @return void
	 */
	public static function setScope($scope){
		self::$_scope = $scope;
	}

	/**
	 * @static
	 * @return string
	 */
	public static function getScope(){
		return self::$_scope;
	}

	/**
	 * @static
	 * @return void
	 */
	public static function resetScope(){
		self::$_scope = 'global';
	}
	
	/**
	 * @param string $type
	 * @return string
	 */
	protected static function getColorCodeByType($type){
		return isset(self::$_type_color_codes[$type]) ? self::$_type_color_codes[$type] : 'default';
	}

	/**
	 * @param Logger_Writer_Abstract $writer
	 */
	public static function addWriter(Logger_Writer_Abstract $writer){
		if (!isset(self::$_writers[self::$_scope])) {
			self::$_writers[self::$_scope] = array();
		}
		if (!in_array($writer, self::$_writers[self::$_scope])) {
			self::$_writers[self::$_scope][] = $writer;
		}
	}

	/**
	 * @static
	 * @return void
	 */
	public static function resetWriters(){
		self::$_writers[self::$_scope] = array();
	}
	
	/**
	 * @return bool
	 */
	public static function hasWriters(){
		return isset(self::$_writers[self::$_scope]) && sizeof(self::$_writers[self::$_scope]) > 0;
	}

	/**
	 * @static
	 * @return void
	 */
	public static function enable(){
		self::$_enabled = true;
	}

	/**
	 * @static
	 * @return void
	 */
	public static function disable(){
		self::$_enabled = false;
	}
	
	/**
	 * @return bool
	 */
	protected static function isEnabled(){
		return self::$_enabled;
	}

	/**
	 * @param mixed $message
	 * @param string $type
	 */
	public static function log($message, $type = 'default'){
		if (!self::isEnabled()) {
			return ;
		}
		if (!self::hasWriters()) {
			throw new Logger_Exception('No writers defined', 3042);
		}
		foreach (self::$_writers[self::$_scope] as $writer) {
			/* @var $writer Logger_Writer_Abstract */
			$writer->write($message, $type, self::getColorCodeByType($type));
		}
	}

	/**
	 * @static
	 * @return void
	 */
	public static function clear(){
		foreach (self::$_writers as $writer) {
			/* @var $writer Logger_Writer_Abstract */
			$writer->clear();
		}
	}

	/**
	 * @static
	 * @param  $message
	 * @return void
	 */
	public static function debug($message) {
		self::log($message, __FUNCTION__);
	}

	/**
	 * @static
	 * @param  $message
	 * @return void
	 */
	public static function ok($message) {
		self::log($message, __FUNCTION__);
	}

	/**
	 * @static
	 * @param  $message
	 * @return void
	 */
	public static function warning($message) {
		self::log($message, __FUNCTION__);
	}

	/**
	 * @static
	 * @param  $message
	 * @return void
	 */
	public static function error($message) {
		self::log($message, __FUNCTION__);
	}	

	/**
	 * @static
	 * @param  $message
	 * @return void
	 */
	public static function fatal($message) {
		self::log($message, __FUNCTION__);
		exit(-1);
	}
	
}