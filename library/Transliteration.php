<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vadim
 * Date: 22.08.11
 * Time: 13:16
 */
 
class Transliteration {

	/**
	 * @param string $string
	 * @param string $locale
	 * @return mixed|string
	 */
	public static function getUrl($string, $locale = 'default'){
		return self::transliterate($string, '-', $locale);
	}

	/**
	 * @param string $string
	 * @param string $locale
	 * @return mixed|string
	 */
	public static function getFile($string, $locale = 'default'){
		return self::transliterate($string, '_', $locale);
	}

	/**
	 * @param string $string
	 * @param string $delimiter
	 * @param string $locale
	 * @return mixed|string
	 */
	public static function transliterate($string, $delimiter = '-', $locale = 'default') {
		$url = $string;
		$url = preg_replace('~[^\\pL0-9_]+~u', $delimiter, $url); // substitutes anything but letters, numbers and '_' with separator
		$url = trim($url, "-");
		if (isset($locale)) {
			$method = "transliterate_".$locale;
		}
		if (!method_exists(__CLASS__, $method)) {
			$method = "transliterate_default";
		}
		$url = call_user_func(__CLASS__.'::'.$method, $url);
		$url = strtolower($url);
		$url = preg_replace('~[^-a-z0-9_]+~', '', $url); // keep only letters, numbers, '_' and separator
		return $url;
	}

	/**
	 * @static
	 * @param string $st
	 * @return string
	 */
	protected static function transliterate_default($st){
		return iconv("utf-8", "us-ascii//TRANSLIT//IGNORE", $st);
	}

	/**
	 * @static
	 * @param string $st
	 * @return string
	 */
	protected static function transliterate_ru($st) {
		$converter = array(
			'а' => 'a',   'б' => 'b',   'в' => 'v',
			'г' => 'g',   'д' => 'd',   'е' => 'e',
			'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
			'и' => 'i',   'й' => 'y',   'к' => 'k',
			'л' => 'l',   'м' => 'm',   'н' => 'n',
			'о' => 'o',   'п' => 'p',   'р' => 'r',
			'с' => 's',   'т' => 't',   'у' => 'u',
			'ф' => 'f',   'х' => 'h',   'ц' => 'c',
			'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
			'ь' => "'",  'ы' => 'y',   'ъ' => "'",
			'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

			'А' => 'A',   'Б' => 'B',   'В' => 'V',
			'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
			'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
			'И' => 'I',   'Й' => 'Y',   'К' => 'K',
			'Л' => 'L',   'М' => 'M',   'Н' => 'N',
			'О' => 'O',   'П' => 'P',   'Р' => 'R',
			'С' => 'S',   'Т' => 'T',   'У' => 'U',
			'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
			'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
			'Ь' => "'",  'Ы' => 'Y',   'Ъ' => "'",
			'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
		);
		return strtr($st, $converter);
	}

}