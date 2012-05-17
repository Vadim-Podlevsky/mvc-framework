<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 01.07.11
 * Time: 0:06
 */

require_once('functions.inc.php');
require_once('FrameworkException.php');

class FileLoader {

	/**
	 * @var array
	 */
	protected static $_application_paths = array();

	/**
	 * @var string
	 */
	protected static $_library_dir = 'library';

	/**
	 * @var bool
	 */
	protected static $_throw_exceptions = true;

	/**
	 * @static
	 * @param string $path
	 * @return void
	 */
	public static function addApplicationPath($path){
		self::$_application_paths[] = rtrim($path, '/');
	}

	/**
	 * @static
	 * @return array
	 */
	public static function getApplicationPaths(){
		return self::$_application_paths;
	}

	/**
	 * @static
	 * @param bool|null $flag
	 * @return bool
	 */
	public static function throwExceptions($flag = null){
		if (isset($flag)) {
			self::$_throw_exceptions = $flag;
		}
		return self::$_throw_exceptions;
	}

	/**
	 * Example : FileLoader::loadClass('My_Class_Name', 'module')
	 * Will search for Module/library/My/Class/Name.php
	 *
	 * @static
	 * @throws FileLoaderException
	 * @param  $class_name
	 * @param null $module
	 * @param null $sub_path
	 * @return bool
	 */
	public static function loadClass($class_name, $module = null, $sub_path = null){
		$file_name = str_replace('_', '/', $class_name).'.php';
		$path_parts = array();
		if (isset($module)) {
			$path_parts[] = $module;
		}
		$path_parts[] = self::$_library_dir;
		if (isset($sub_path)) {
			$path_parts[] = $sub_path;
		}
		foreach (self::$_application_paths as $path) {
			$path_parts_tmp = array_merge(array($path), $path_parts, array($file_name));
			$full_file_path = implode($path_parts_tmp, '/');
			if (file_exists($full_file_path)) {
				require_once($full_file_path);
				return true;
			}
		}
		if (file_exists_include_path($file_name)) {
			require_once($file_name);
			return true;
		}
		if (self::throwExceptions()) {
			self::reset();
			throw new FileLoaderException('Class file '.$file_name.' could not be found');
		}
		return false;
	}

	/**
	 * @static
	 * @param string $file_name
	 * @return bool
	 */
	public static function loadFile($file_name){
		foreach (self::$_application_paths as $path) {
			$full_file_path = $path.'/'.$file_name;
			if (file_exists($full_file_path)) {
				return require_once($full_file_path);
			}
		}
		return false;
	}

	/**
	 * @static
	 * @return void
	 */
	private static function reset(){
		self::$_throw_exceptions = true;
	}

}

class FileLoaderException extends FrameworkException {}
