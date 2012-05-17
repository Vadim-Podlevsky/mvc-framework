<?php
FileLoader::loadClass('Abstract', 'TestCase/Listener');

class TestCase_Listener_Html extends TestCase_Listener_Abstract {

	public function startTestCase($name){
		echo "<table border='1' width='800'>\r\n";
		echo "<tr><td colspan='2' style='background-color: #AFAFAF'><b>$name</b></td></tr>\r\n";
	}
	
	public function endTestCase($name){
		echo "</table>\r\n";
	}
	
	public function startUnitTest($name){
		echo "<tr>\r\n<td>$name</td>\r\n";
	}
	
	public function renderStackTrace($isError){
		$str = '';
		$str .= '<table width="1024px">';
		foreach ($isError as $row){
			$__id = rand(1000,9999);
			$__id++;
			$str .= '<tr>';
				$str .= '<td style="cursor: help;" onclick="window.document.getElementById(\'i'.$__id.'\').style.display=\'block\';">'.$row['file'].':'.$row['line'].'</td>';
			$str .= '</tr>';
			
			$text = file($row['file']);
			$line_start = max(1, $row['line']-5);
			$line_end = min(count($text), $row['line']+5);
			
			
			$str .= '<tr style="display: none;" id="i'.$__id.'">';
				$str .= '<td style="background-color: #FFFFFF; width: 1024px;">';
					$str .= '<pre>';
					for($i=$line_start; $i<$line_end; $i++){
						if( ($i+1) == $row['line']){
							$str .= '<div style="background-color: #FFAAFF;"><b>'.($i+1).' ' . str_replace("\n", '', $text[$i]) . '</b>'. "</div>\n";
						} else {
							$str .= ($i+1).' ' . str_replace("\n", '', $text[$i]) . ''. "\n";
						}
					}
					$str .= '</pre>';
				$str .= '</td>';
			$str .= '</tr>';

		}
		$str .= '</table>';
		return $str;
	}
	
	public function endUnitTest($name, $failedException = null){
		/* @var $failedException TestCaseFailedException */
		if( $failedException !== null ){
			echo "<td><font color='red'>ERROR</font></td>\r\n";
			echo "<tr><td colspan='2' style='background-color: #FF5555; color: white;'>{$failedException->getMessage()}(\"{$failedException->getA()}\",\"{$failedException->getB()}\")</td></tr>\r\n";
			echo "<tr><td colspan='2' style='background-color: yellow; color: blue;'>
			".$this->renderStackTrace($failedException->getTrace())."
			</td></tr>\r\n";
		} else {
			echo "<td width='100px' align='center'><font color='green'>OK</font></td>\r\n";	
		}
		echo '</tr>'."\r\n";
		flush();
	}
}