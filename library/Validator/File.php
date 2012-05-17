<?php

require_once('Validator/Abstract.php');
require_once('Upload.php');

class Validator_File extends Validator_Abstract {
	
	protected $_error = 'file_upload_error';
	
	protected $_message = '{label} is not a file input type';
	
	/**
	 * @param array $form_data
	 * @param string $element_name
	 * @param string array $file_type
	 * @param int array $size
	 * @param int $width
	 * @param int $height
	 * @return bool
	 */
	public function isValid($form_data, $element_name, $file_type, $options = array()){
		if (!isset($form_data[$element_name])) {
			return false;
		}
		$file = new Upload($form_data[$element_name]);
		$file->addAllowedMimeType('jpeg', 'image/pjpeg', array('jpg','jpeg','jpe'));
		$file->addAllowedMimeType('jpeg', 'image/jpeg',  array('jpg', 'jpeg', 'jpe'));
        $file->addAllowedMimeType('gif', 'image/gif',   array('gif'));
        $file->addAllowedMimeType('png', 'image/png',   array('png'));
        $file->addAllowedMimeType('bmp', 'image/x-ms-bmp',   array('bmp'));
        $file->addAllowedMimeType('txt', 'text/plain', array('txt'));
        $file->addAllowedMimeType('pdf', 'application/pdf', array('pdf'));
        $file->addAllowedMimeType('pdf', 'application/x-pdf', array('pdf'));
        $file->addAllowedMimeType('excel', 'application/x-msexcel', array('xls'));
        $file->addAllowedMimeType('excel', 'application/ms-excel', array('xls'));
        $file->addAllowedMimeType('excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', array('xlsx'));
		$file->addAllowedMimeType('csv', 'text/csv', array('csv'));
		$file->addAllowedMimeType('csv', 'application/vnd.ms-excel', array('csv'));		
        $file->addAllowedMimeType('doc', 'application/msword', array('doc'));
        $file->addAllowedMimeType('doc', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', array('docx'));
        if (!is_array($file_type)) {
        	$file_type = array($file_type);
        }
		list($size, $width, $height) = self::parseOptions($options);
        $file->validate($file_type, true, $size, $width, $height);
        if ($file->isError()) {
        	$this->setMessage(current($file->getErrors()));
        	return false;
        } else {
        	return true; 
        }
	}
	
	public function getMessage(){
		return $this->_applyElementLabel($this->_message);
	}
	
	private static function parseOptions($options){
        if (!is_array($options)) {
        	return array(null, null, null);
        }
    	$size = array();
    	if (isset($options['maxsize'])) {
    		$size['max'] = $options['maxsize'];
    	}
    	if (isset($options['minsize'])) {
    		$size['min'] = $options['minsize'];
    	}
    	if (isset($options['size'])) {
    		$size = $options['size'];
    	}
    	$width = array();
    	if (isset($options['maxwidth'])) {
    		$width['max'] = $options['maxwidth'];
    	}
    	if (isset($options['minwidth'])) {
    		$width['min'] = $options['minwidth'];
    	}
    	if (isset($options['width'])) {
    		$width = $options['width'];
    	}
    	$height = array();
    	if (isset($options['maxheight'])) {
    		$height['max'] = $options['maxheight'];
    	}
    	if (isset($options['minheight'])) {
    		$height['min'] = $options['minheight'];
    	}
    	if (isset($options['height'])) {
    		$height = $options['height'];
    	}
        return array($size, $width, $height);
	}
	
}
