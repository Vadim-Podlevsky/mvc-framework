<?php 
require('securitymage.php'); 
session_start(); 
isset($_GET['width']) ? $iWidth = (int)$_GET['width'] : $iWidth = 150; 
isset($_GET['height']) ? $iHeight = (int)$_GET['height'] : $iHeight = 30; 
$oSecurityImage = new SecurityImage($iWidth, $iHeight); 
if ($oSecurityImage->Create()) { 
	$_SESSION['code'] = $oSecurityImage->GetCode(); 
} else { 
	echo 'Image GIF library is not installed.';
} 
?>