<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vadim
 * Date: 03.10.11
 * Time: 19:03
 */
 
class Filter_StripEmptyTags {

	/**
	 * @param array $form_data
	 * @param string $element_name
	 * @param string|array $tags
	 * @return array
	 */
	public function filter($form_data, $element_name, $tags = ''){
		$html = $form_data[$element_name];
		if (!$tags) {
			$tags = "[a-z]+";
		}
 		if (!is_array($tags)) {
			$tags = array($tags);
		}
		foreach ($tags as $tag) {
			$html = preg_replace("/<".$tag."[^>]*(?:\/>|>(?:\s|&nbsp;)*<\/".$tag.">)/s", '', $html);
		}
		return $html;
	}

}