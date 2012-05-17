<?php


class Session {

	private static $_storage = '/tmp';
	private static $_cache_expire = 360;
	private static $_cache_limiter = 'nocache';

	/**
	 * @static
	 * @return void
	 */
	public static function start () {
		//if (!file_exists(self::$_storage)) {
		//	mkdir(self::$_storage, 0777, true);
		//}
		//session_cache_limiter(self::$_cache_limiter);
		//session_cache_expire(self::$_cache_expire);
		//session_save_path(self::$_storage);
		//session_set_cookie_params(self::$_cache_expire * 60, Config::get('applicationUrlRoot'));
		session_start();
	}

	/**
	 * @static
	 * @param string $storage
	 * @return void
	 */
	public static function setStorage($storage){
		self::$_storage = $storage;
	}

	/**
	 * @static
	 * @param string $cache_limiter
	 * @return void
	 */
	public static function setCacheLimiter($cache_limiter){
		self::$_cache_limiter = $cache_limiter;
	}

	/**
	 * @static
	 * @param int $cache_expire
	 * @return void
	 */
	public static function setCacheExpire($cache_expire){
		self::$_cache_expire = $cache_expire;
	}
	
	/**
	* Regenerates session identifier
	*
	* @param	bool	$delete_old_session
	* @access	public static
	* @return	void
	*/
	public static function regenerateId ($delete_old_session = null) {
		session_regenerate_id($delete_old_session);
	}
	
	/**
	* Destroys session
	*
	* @access	public static
	* @return	void
	*/
	public static function destroy() {
		session_destroy();
	}

	/**
	 * @static
	 * @param  $name
	 * @return bool
	 */
	public static function hasVar($name){
		return isset($_SESSION[$name]);
	}

	/**
	* Retrieves variable stored in session
	*
	* @param	string	$varName
	* @access	public static
	* @return	mixed
	*/
	public static function getVar($varName) {
		if (isset($_SESSION[$varName])) {
            return $_SESSION[$varName];
        }
	}

	/**
	* Stores variable in session
	*
	* @param	string	$varName
	* @param	mixed	$value
	* @access	public static
	* @return	void
	*/
	public static function setVar($varName, $value) {
		$_SESSION[$varName] = $value;
	}

	/**
	 * @static
	 * @param string $varName
	 * @return void
	 */
	public static function unsetVar($varName) {
		unset($_SESSION[$varName]);
	}

    /**
     * @static
	 * @deprecated
     * @return string
     */
	public static function id() {
		return session_id();
	}

	/**
	 * @static
	 * @return string
	 */
	public static function getId(){
		return session_id();
	}

	/**
	 * @static
	 * @param string $id
	 * @return void
	 */
	public static function setId($id){
		session_id($id);
	}

    /**
     * @param string $name
     * @return void
     */
	public static function setName($name){
		session_name($name);
	}

    /**
     * @return string
     */
	public static function getName(){
		return session_name();
	}

    /**
     * @static
     * @return void
     */
    public static function sessionWriteClose(){
        session_write_close();
    }

}

class SessionException extends FrameworkException {}
?>