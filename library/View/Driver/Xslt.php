<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Вадим
 * Date: 04.07.11
 * Time: 21:59
 */

require_once('View/Driver/Abstract.php');

class View_Driver_Xslt extends View_Driver_Abstract {
	
	protected $_template_extension = '.xsl';
	
	public static $debug = false,
				  $timeLoad = 0,
				  $timeImportStyleSheet = 0;
	
	/**
	 * @throws View_Driver_XsltException
	 * @param  $templateName
	 * @return string
	 */
	public function render($templateName){
		if (!file_exists($templateName) or !is_file($templateName)) {
			throw new View_Driver_XsltException('Cannot load template file: '.$templateName);
		}
		$dom = $this->dataToDOM($this->_template_data, Config::get('encoding'));
		if (self::$debug) {
			echo DumpHelper::xmldump(str_replace('<?xml version="1.0" encoding="'.Config::get('encoding').'"?>', '', $dom->saveXML()));
		}
		$xslObj       = new DOMDocument();
		$xsl          = new XSLTProcessor();
		$t = new phpTimer;
		$t->start();
		$xslObj->load($templateName);
		self::$timeLoad = $t->get_current();
		
		$t->start();
		$xsl->importStyleSheet($xslObj);
		self::$timeImportStyleSheet = $t->get_current();
		return $xsl->transformToXML($dom);
	}
	
	/**
	 * @param  $data
	 * @param  $encoding
	 * @return DOMDocument
	 */
	private function dataToDOM ($data, $encoding) {
		$dom = new DOMDocument ('1.0', $encoding);
		$root = $this->array_to_xml_recursive($dom, $data, 'template');
		$dom->appendChild($root);
		return $dom;
	}
	
	/**
	 * @param DOMDocument $dom
	 * @param  $aArr
	 * @param  $sName
	 * @param DOMElement|null $item
	 * @return DOMElement
	 */
	private function array_to_xml_recursive (DOMDocument $dom, $aArr, $sName, $item = null) {
		if (null === $item) {
			$item = $dom->createElement($sName);
		}
		// Loop through each element
		foreach ($aArr as $key => $val) {
			// integers are replaced with item
			$key = ($key === (int) $key) ? 'item' : $key;
			if (is_array($val) || is_object($val)) {
				$sub = $dom->createElement($key);
				$item->appendChild($sub);
				$this->array_to_xml_recursive($dom, $val, $key, $sub);
			} else {
				// Add this item
				$sub = $dom->createElement($key);
				$cdata = $dom->createCdataSection($val);
				$sub->appendChild($cdata);
				$item->appendChild($sub);
			}
		}
		return $item;
	}
}

class View_Driver_XsltException extends FrameworkException {}