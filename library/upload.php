<?php

class Upload {

	private $file;

	public $filesRoot;

	private $errorCodeMessages = array(
		'E_UPLOAD_INI_SIZE'=>1,
		'E_UPLOAD_FORM_SIZE'=>2,
		'E_UPLOAD_PARTIAL'=>3,
		'E_UPLOAD_NO_FILE'=>4,
		'E_UPLOAD_MIME'=>101,
		'E_UPLOAD_EXT'=>102,
		'E_UPLOAD_EMPTY_EXT'=>103,
		'E_UPLOAD_SIZE_GT'=>104,
		'E_UPLOAD_SIZE_LT'=>105,
		'E_UPLOAD_SIZE_EQ'=>106,
		'E_UPLOAD_WIDTH_GT'=>107,
		'E_UPLOAD_WIDTH_LT'=>108,
		'E_UPLOAD_WIDTH_EQ'=>109,
		'E_UPLOAD_HEIGHT_GT'=>110,
		'E_UPLOAD_HEIGHT_LT'=>111,
		'E_UPLOAD_HEIGHT_EQ'=>112,
		'E_UPLOAD_CREATE_DIR'=>113,
		'E_UPLOAD_MOVE'=>114,
		'E_UPLOAD_EMPTY_FILE'=>115,
	);

	private $phpErrorCodes = array(
		1=>'E_UPLOAD_INI_SIZE',
		2=>'E_UPLOAD_FORM_SIZE',
		3=>'E_UPLOAD_PARTIAL',
		4=>'E_UPLOAD_NO_FILE'
	);

	private $allowedMimeTypes;

	private $errors;

	public function __construct($file = array()){
		$this->setFile($file);
		$this->filesRoot = Registry::RetrieveSetting('config')->filestorageRoot;
		$this->setErrorMessages();
	}

	protected function setErrorMessages(){

		$this->setErrorCodeMessage('E_UPLOAD_INI_SIZE' , 'The uploaded file exceeds the upload_max_filesize directive in php.ini');
		$this->setErrorCodeMessage('E_UPLOAD_FORM_SIZE', 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
		$this->setErrorCodeMessage('E_UPLOAD_PARTIAL', 'The uploaded file was partially uploaded');
		$this->setErrorCodeMessage('E_UPLOAD_NO_FILE', 'File was not loaded');

		$this->setErrorCodeMessage('E_UPLOAD_MIME', 'File type {mimetype} not allowed!');
		$this->setErrorCodeMessage('E_UPLOAD_EXT', 'File extension {ext} not allowed!');
		$this->setErrorCodeMessage('E_UPLOAD_EMPTY_EXT', 'Empty file extension is not allowed!');

		$this->setErrorCodeMessage('E_UPLOAD_SIZE_GT', 'Uploaded file size of {filesize} is more then {maxsize} allowed!');
		$this->setErrorCodeMessage('E_UPLOAD_SIZE_LT', 'Uploaded file size of {filesize} is less then {minsize} allowed!');
		$this->setErrorCodeMessage('E_UPLOAD_SIZE_EQ', 'Uploaded file size of {filesize} is not equal to {equalsize} allowed!');

		$this->setErrorCodeMessage('E_UPLOAD_WIDTH_GT', 'Image width of {width}px is more then {maxwidth}px allowed!');
		$this->setErrorCodeMessage('E_UPLOAD_WIDTH_LT', 'Image width of {width}px is less then {minwidth}px allowed!');
		$this->setErrorCodeMessage('E_UPLOAD_WIDTH_EQ', 'Image width of {width}px is not equal to {equalwidth}px allowed!');

		$this->setErrorCodeMessage('E_UPLOAD_HEIGHT_GT', 'Image height of {height}px is more then {maxheight}px allowed!');
		$this->setErrorCodeMessage('E_UPLOAD_HEIGHT_LT', 'Image height of {height}px is less then {minheight}px allowed!');
		$this->setErrorCodeMessage('E_UPLOAD_HEIGHT_EQ', 'Image height of {height}px is not equal to {equalheight}px allowed!');

		$this->setErrorCodeMessage('E_UPLOAD_CREATE_DIR', 'Directory "{dir}" cannot be created');
		$this->setErrorCodeMessage('E_UPLOAD_MOVE', 'Failed to move uploaded file');
		$this->setErrorCodeMessage('E_UPLOAD_EMPTY_FILE', 'Filename is empty');
	}

	private function setFile($file){
		$this->file = new stdClass();
		$this->file->name = '';

		$this->file->size = '';
		$this->file->type = '';
		$this->file->error = '4';
		$this->file->tmp_name = '';
		foreach ($this->file as $attr => $value) {
			if (isset($file[$attr])) $this->file->$attr = $file[$attr];
		}
		$this->file->ext = $this->getFileExt($this->file->name);
	}

	// lookup for mimetypes list here
	// http://www.utoronto.ca/webdocs/HTMLdocs/Book/Book-3ed/appb/mimetype.html
	public function addAllowedMimeType($mimeGroup, $mimeType, $extensions = array()){
		$this->allowedMimeTypes[$mimeGroup][$mimeType] = $extensions;
	}

	private function getAllowedMimeTypes($mimeGroup){
		return isset($this->allowedMimeTypes[$mimeGroup]) ? $this->allowedMimeTypes[$mimeGroup] : false;
	}

	public function setErrorCodeMessage($errorCode, $message) {
		$this->errorCodeMessages[$errorCode] = isset($this->errorCodeMessages[$errorCode])
		? $message :
		(__CLASS__.': Message "'.$errorCode.'" doesn\'t exist');
	}

	private function getErrorMessage($errorCode, $params = array()){
		$error = isset($this->errorCodeMessages[$errorCode]) ? $this->errorCodeMessages[$errorCode] : false;
		if (is_array($params) and sizeof($params)) {
			foreach ($params as $key => $value) {
				$error = str_replace('{'.$key.'}', $value, $error);
			}
		}
		return $error;
	}

	public function isError(){
		return sizeof($this->errors);
	}

	public function getErrors(){
		return $this->errors;
	}

	//use params $width and $height for images only
	public function validate($mimeGroups = array(), $isRequired = true, $size = '', $width = '', $height = ''){
		if ($this->file->error == 4 and $isRequired == false) return true;

		if ($this->file->error) {
			$this->errors[] =  $this->errorCodeMessages[$this->phpErrorCodes[$this->file->error]];
			return;
		}

		if ($this->file->ext == '') {
			$this->errors[] = $this->getErrorMessage('E_UPLOAD_EMPTY_EXT');
			return;
		}
		//
		// <!--Mime type and file extension check
		//
		if (is_array($mimeGroups) and sizeof($mimeGroups)) {
			$is_mime_ok = false;
			$is_ext_ok = false;
			foreach ($mimeGroups as $group) {
				$mimeTypes = $this->getAllowedMimeTypes($group);
				foreach ($mimeTypes as $mimeType => $extensions) {
					if (strtolower($this->file->type) == strtolower($mimeType)) {
						$is_mime_ok = true;
						if (is_array($extensions) and sizeof($extensions)) {
							foreach ($extensions as $extension) {
								if (strtolower($this->file->ext) == strtolower($extension)) $is_ext_ok = true;
							}
						} else $is_ext_ok = true;
					}
				}
			}
			if (!$is_mime_ok) {
				$this->errors[] = $this->getErrorMessage('E_UPLOAD_MIME', array('mimetype'=>$this->file->type));
				return;
			}
			if (!$is_ext_ok) {
				$this->errors[] = $this->getErrorMessage('E_UPLOAD_EXT', array('ext'=>$this->file->ext));
				return;
			}
		}
		// -->

		//
		// <!--File size check
		//
		if (is_array($size)) {
			if (!empty($size['max'])) {
				if ($this->file->size > $size['max']) {
					$this->errors[] = $this->getErrorMessage('E_UPLOAD_SIZE_GT', array('filesize'=>$this->formatSize($this->file->size), 'maxsize'=>$this->formatSize($size['max'])));
					return;
				}
			}
			if (!empty($size['min'])) {
				if ($this->file->size < $size['min']) {
					$this->errors[] = $this->getErrorMessage('E_UPLOAD_SIZE_LT', array('filesize'=>$this->formatSize($this->file->size), 'minsize'=>$this->formatSize($size['min'])));
					return;
				}
			}
		} else if (!empty($size)) {
			if ($this->file->size != $size) {
				$this->errors[] = $this->getErrorMessage('E_UPLOAD_SIZE_EQ', array('filsize'=>$this->formatSize($this->file->size), 'equalsize'=>$this->formatSize($size)));
				return;
			}
		}
		// -->

		//
		// <!--Image dimensions check
		// 	WARNING!!! We consider that if $width and(or) $height param is specified we deal with image files (No check is performed)
		if (!empty($width) || !empty($height)) {
			$image_size = getimagesize($this->file->tmp_name);
			$this->file->width = $image_size[0];
			$this->file->height = $image_size[1];
			if (is_array($width)) {
				if (!empty($width['max'])) {
					if ($this->file->width > $width['max']) {
						$this->errors[] = $this->getErrorMessage('E_UPLOAD_WIDTH_GT', array('width'=>$this->file->width, 'maxwidth'=>$width['max']));
						return;
					}
				}
				if (!empty($width['min'])) {
					if ($this->file->width < $width['min']) {
						$this->errors[] = $this->getErrorMessage('E_UPLOAD_WIDTH_LT', array('width'=>$this->file->width, 'minwidth'=>$width['min']));
						return;
					}
				}
			} else if (!empty($width)) {
				if ($this->file->width != $width) {
					$this->errors[] = $this->getErrorMessage('E_UPLOAD_WIDTH_EQ', array('width'=>$this->file->width, 'equalwidth'=>$width));
					return;
				}
			}
			if (is_array($height)) {
				if (!empty($height['max'])) {
					if ($this->file->height > $height['max']) {
						$this->errors[] = $this->getErrorMessage('E_UPLOAD_HEIGHT_GT', array('height'=>$this->file->height, 'maxheight'=>$height['max']));
						return;
					}
				}
				if (!empty($height['min'])) {
					if ($this->file->height < $height['min']) {
						$this->errors[] = $this->getErrorMessage('E_UPLOAD_HEIGHT_LT', array('height'=>$this->file->height, 'minheight'=>$height['min']));
						return;
					}
				}
			} else if (!empty($height)) {
				if ($this->file->height != $height) {
					$this->errors[] = $this->getErrorMessage('E_UPLOAD_HEIGHT_EQ', array('height'=>$this->file->height, 'equalheight'=>$height));
					return;
				}
			}
		}
		// -->
		return true;
	}

	public function canUpload(){
		return !empty($this->file->tmp_name) and !$this->file->error and !$this->isError();
	}

	public function upload($path, $fileName) {
		if (empty($fileName)) {
			$this->errors[] = $this->getErrorMessage('E_UPLOAD_EMPTY_FILE');
			return;
		}
		$path = $this->filesRoot.$path.'/';
		if (!is_dir($path) ) {
			$old_mask = umask(0);
			if (!mkdir($path, 0777, true)) {
				umask($old_mask);
				$this->errors[] = $this->getErrorMessage('E_UPLOAD_CREATE_DIR', array('dir', $path));
				return;
			}
			umask($old_mask);
		}
		if (false === copy($this->file->tmp_name, $path.$fileName)){
			$this->errors[] = $this->getErrorMessage('E_UPLOAD_MOVE');
			return;
		}
		chmod($path.$fileName, 0777);
		return true;
	}

	public function unlink($path, $filename){
		$path = $this->filesRoot.$path.'/';
		if (!is_dir($path) ) {
			return false;
		}
		if (!is_file($path.$filename)) {
			return false;
		}
		if (!unlink($path.$filename)){
			return false;
		}
		return true;
	}

	private function getFileExt($file_name){
		preg_match('/\.([^\.]+)$/i', $file_name, $ext);
		return isset($ext[1]) ? strtolower($ext[1]) : '';
	}
	
	
	public  function generateImageThumbnail($filename, $filepath) {
		/**
		 * filename like '650_890_1255_1638.jpg'
		 * filepath like '/srv/www/htdocs/d-pvv/filestorage//Thisistest/Category2/1/'
		 */
		if (empty($filename) || !is_file($filepath.$filename)) return false;

		$cfg = Registry::RetrieveSetting('config');
		if (empty($cfg->thumbnails) || empty($cfg->thumbnails->converterPath)) return false;

		
		$filepath_small = $filepath . 'small_' . $filename;
		
		
		list($width, $height, $type, $attr) = getimagesize($filepath.$filename);
		
		$command = "{$cfg->thumbnails->converterPath} -size {$width}x{$height} \"{$filepath}{$filename}\" -thumbnail '{$cfg->thumbnails->contentmModules->width}x{$cfg->thumbnails->contentmModules->height}>' \"{$filepath_small}\"";
		$output = system($command, $return_value);
		@chmod($filepath_small, 0777);
		
		return file_exists($filepath_small);
	}
	

	public function getFileExtension(){
		return $this->file->ext;
	}	
	
	public function getFileName(){
		return $this->file->name;
	}

	public function generateFileName($field, $id) {
		$field = trim(preg_replace('~\W+~', '_', $field), '_');
		if (!empty($id)){$id='_'.$id;}
		return $field.$id.'.'.$this->file->ext;
	}

	protected function formatSize($size){
		return sprintf("%.0f", $size/1024).' kb';
	}

	public function setFilesRootFolder($filesRootFolder) {
		$this->filesRoot = $filesRootFolder;
	}

	public function getFilesRootFolder() {
		return $this->filesRoot;
	}
}
?>