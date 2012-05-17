<?php

/**
 * @throws FrameworkException
 */
class Factory {

	/**
	* Class constructor
	*
	* @access	public static
	* @param	mixed constructor parameter
	* @return	new object
	*/

	public static function create($className, $param = '') {
		if (!class_exists($className)) {
			throw new FrameworkException(sprintf('Could not create class "%s"', $className), 3001);
		}
		return $param ? new $className($param) : new $className();
	}

}