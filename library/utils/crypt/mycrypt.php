<?php
class MyCrypt {
	
	static $cryptinstall = 'application/crypt/cryptographp.fct.php';
	
	static function getCrypt($cfg = 0, $reload = 1){
		if (!function_exists('get_crypt')) {
			 $cryptinstall=self::$cryptinstall;
			 include "cryptographp.fct.php"; 
		}
		return get_crypt($cfg, $reload);
	}
	
	static function checkCrypt($code){
		if (!function_exists('chk_crypt')) {
			 $cryptinstall=self::$cryptinstall;
			 include "cryptographp.fct.php"; 
		}
		return chk_crypt($code);
	}
	
}

?>