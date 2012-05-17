<?php
class SecurityImage { 

	var $oImage; 
	var $iWidth; 
	var $iHeight; 
	var $iNumChars; 
	var $iNumLines; 
	var $iSpacing; 
	var $sCode; 
      
	function SecurityImage ($iWidth = 150, $iHeight = 30, $iNumChars = 5, $iNumLines = 30) { 
		$this->iWidth = $iWidth; 
		$this->iHeight = $iHeight; 
		$this->iNumChars = $iNumChars; 
		$this->iNumLines = $iNumLines; 
		$this->oImage = imagecreate($iWidth, $iHeight);
		imagecolorallocate($this->oImage, 255, 255, 255); 
		$this->iSpacing = (int)($this->iWidth / $this->iNumChars);       
	}

	function DrawLines() {
		for ($i = 0; $i < $this->iNumLines; $i++) { 
			$iRandColour = rand(190, 250);
			$iLineColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);
			imageline($this->oImage, rand(0, $this->iWidth), rand(0, $this->iHeight), rand(0, $this->iWidth), rand(0, $this->iHeight), $iLineColour);
		}
	}



	function GenerateCode () { 
		$this->sCode = ''; 
		for ($i = 0; $i < $this->iNumChars; $i++) { 
			$this->sCode .= chr(rand(65, 90)); 
		} 
	}

	function DrawCharacters () { 
		for ($i = 0; $i < strlen($this->sCode); $i++) { 
			$iCurrentFont = rand(1, 5); 
			$iRandColour  = rand(0, 128); 
			//$iTextColour  = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour); 
			$iTextColour  = imagecolorallocate($this->oImage, 0, 0, 0); 
			//imagestring($this->oImage, $iCurrentFont, $this->iSpacing / 3 + $i * $this->iSpacing, ($this->iHeight - imagefontheight($iCurrentFont)) / 2, $this->sCode[$i], $iTextColour); 
			imagestring($this->oImage, 5, $this->iSpacing / 3 + $i * $this->iSpacing, ($this->iHeight - imagefontheight($iCurrentFont)) / 2, $this->sCode[$i], $iTextColour); 
		} 
	}

	function Create ($sFilename = '') { 
		if (!function_exists('imagegif')) { 
			return false; 
		} 
		$this->DrawLines(); 
		$this->GenerateCode(); 
		$this->DrawCharacters(); 
		if ($sFilename != '') { 
			imagegif($this->oImage, $sFilename); 
		} else { 
			header('Content-type: image/gif'); 
			imagegif($this->oImage); 
		} 
		imagedestroy($this->oImage); 
		return true; 
	}

	function GetCode() { 
		return $this->sCode; 
	}

}
?>