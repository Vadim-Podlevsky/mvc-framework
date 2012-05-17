<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vadim
 * Date: 03.10.11
 * Time: 18:41
 */
 
class Filter_StripTagAttribute {

	public function filter($form_data, $element_name, $tag, $attr, $suffix = ''){
		$msg = $form_data[$element_name];
		$lengthfirst = 0;
		while (strstr(substr($msg, $lengthfirst), "<$tag ") != '') {
			$tag_start = $lengthfirst + strpos(substr($msg, $lengthfirst), "<$tag ");
			$partafterwith = substr($msg, $tag_start);
			$img = substr($partafterwith, 0, strpos($partafterwith, '>') + 1);
			$img = str_replace(' =', '=', $img);
			$out = "<$tag";
			for($i = 0; $i < count($attr); $i++) {
				if (empty($attr[$i])) {
					continue;
				}
				if (strpos($img, ' ', strpos($img, $attr[$i] . '=')) === false) {
					$long_val = (strpos($img, '>', strpos($img, $attr[$i] . '=')) - (strpos($img, $attr[$i] . '=') + strlen($attr[$i]) + 1));
				} else {
					$long_val = (strpos($img, ' ', strpos($img, $attr[$i] . '=')) - (strpos($img, $attr[$i] . '=') + strlen($attr[$i]) + 1));
				}
				$val = substr($img, strpos($img, $attr[$i] . '=') + strlen($attr[$i]) + 1, $long_val);
				if (!empty($val)) {
					$out .= ' ' . $attr[$i] . '=' . $val;
				}
			}
			if (!empty($suffix)) {
				$out .= ' ' . $suffix;
			}
			$out .= '>';
			$partafter = substr($partafterwith, strpos($partafterwith, '>') + 1);
			$msg = substr($msg, 0, $tag_start) . $out . $partafter;
			$lengthfirst = $tag_start + 3;
		}
		return $msg;
		
	}

}