<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vadim
 * Date: 03.10.11
 * Time: 18:41
 */

require_once('utils/simple_html_dom_1_5/simple_html_dom.php');
 
class Filter_AddTagAttribute {

	/**
	 * @param array $form_data
	 * @param string $element_name
	 * @param string $tag
	 * @param string $attr
	 * @param string $value
	 * @return string
	 */
	public function filter($form_data, $element_name, $tag, $attr, $value){
		$msg = $form_data[$element_name];
		$html_dom = str_get_html($msg);
		$tags = $html_dom->find($tag);
		if (sizeof($tags)) {
			foreach ($tags as $dom_tag) {
				$dom_tag->$attr = $value;
			}
		}
		return (string) $html_dom;
	}

}