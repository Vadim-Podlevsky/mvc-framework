<?php

class Dumper {
	
	private static $_objects;
	private static $_output;
	private static $_depth;
	private static $_EOL;
	private static $_spacer;
	private static $_decorator = "<pre style=\"text-align:left!important;border:1px 0 solid #DC5A38;padding:10px 0 10px 10px;margin:1px 0;background-color:#FFFDE4;color:#DC5A38;width:100%;font-size:11px;font-family: verdana\">%s</pre>";
	
	//use it when you want to dump variables of any type
	//was made to substitute var_dump() and/or print_r() functions
	public static function dump($var)
	{
		self::$_output = '';
		self::$_objects = array();
		self::$_depth = 10;
		self::$_EOL = "\n";
		self::$_spacer = ' ';
		self::_dumpRecursive($var,0);
		
		echo isset($_SERVER['SSH_CONNECTION']) ? self::$_output.PHP_EOL : str_replace('%s',self::$_output, self::$_decorator);
	}
	
	public static function xmldump($xmlstr) {
		self::$_output = '';
		self::$_EOL = "\n";
		self::$_spacer = ' ';
		$xmlstr = str_replace("\n", '', $xmlstr);
		$xmlstr = gettype($xmlstr) === 'string' ? $xmlstr : 'Error: string type required!';
		$params = array($xmlstr, 0);
		while ($params = self::_parseXml($params[0], $params[1]));
		return str_replace('%s',self::$_output, self::$_decorator);
	}
	
	private static function _parseXml($xmlstr, $level){
		$xmlstr = trim($xmlstr);
		if(strpos($xmlstr, "<![CDATA[") === 0) {
			$pos = strpos($xmlstr, ']]>');
			$data = substr($xmlstr, 9, $pos - 9);
			$spaces=str_repeat(self::$_spacer,$level*4);
			self::$_output .= $spaces.'<![CDATA['.$data.']]>'.self::$_EOL;
			return array (substr($xmlstr, $pos + 3), $level);
		} else if (preg_match("/^([^>]*)(<([\w-]+)([^>]+=(\"[^>]*\")|('[^>]*'))*[\s]*(\/)?>)(.*)/", $xmlstr, $matches)) {
			$spaces=str_repeat(self::$_spacer,$level*4);
			if ($matches[1]) self::$_output .= $spaces.$matches[1].self::$_EOL;
			if ($matches[7]) $level--;
			self::$_output .= $spaces.$matches[2].self::$_EOL;
			return array ($matches[8], $level+1);
		} else if(preg_match("/^([^>]*)(<\/([\w-]+)([^>]*)>)(.*)/", $xmlstr, $matches)) {
			$spaces=str_repeat(self::$_spacer,($level)*4);
			if ($matches[1]) self::$_output .= $spaces.$matches[1].self::$_EOL;
			$spaces=str_repeat(self::$_spacer,($level-1)*4);
			self::$_output .= $spaces.$matches[2].self::$_EOL;
			return array ($matches[5], $level-1);
		} else {
			self::$_output .= $xmlstr;
		}
		self::$_output = rtrim(self::$_output, self::$_EOL);
		return false;
	}

	private static function _dumpRecursive($var,$level)
	{
		switch(gettype($var))
		{
			case 'boolean':
				self::$_output.='bool('.($var?'true':'false').')';
				break;
			case 'integer':
				self::$_output.="int($var)";
				break;
			case 'double':
				self::$_output.="double($var)";
				break;
			case 'string':
				self::$_output.="string(".strlen($var)."):'$var'";
				break;
			case 'resource':
				self::$_output.='{resource}';
				break;
			case 'NULL':
				self::$_output.="null";
				break;
			case 'unknown type':
				self::$_output.='{unknown}';
				break;
			case 'array':
				if (self::$_depth<=$level) {
					self::$_output.='array(...)';
				} elseif (empty($var)) {
					self::$_output.='array()';
				} else {
					$keys=array_keys($var);
					$spaces=str_repeat(self::$_spacer,$level*4);
					self::$_output.="array\n".$spaces.'(';
					foreach($keys as $key)
					{
						self::$_output.=self::$_EOL.$spaces."    [$key] => ";
						self::$_output.=self::_dumpRecursive($var[$key],$level+1);
					}
					self::$_output.=self::$_EOL.$spaces.')';
				}
				break;
			case 'object':
				if (($id=array_search($var,self::$_objects,true))!==false) {
					self::$_output.=get_class($var).'#'.($id+1).'(...)';
				} elseif (self::$_depth<=$level) {
					self::$_output.=get_class($var).'(...)';
				} else {
					$id=array_push(self::$_objects,$var);
					$className=get_class($var);
					$members=(array)$var;
					$keys=array_keys($members);
					$spaces=str_repeat(self::$_spacer,$level*4);
					self::$_output.="$className#$id\n".$spaces.'(';
					foreach($keys as $key)
					{
						$keyDisplay=strtr(trim($key),array("\0"=>':'));
						self::$_output.=self::$_EOL.$spaces."    [$keyDisplay] => ";
						self::$_output.=self::_dumpRecursive($members[$key],$level+1);
					}
					self::$_output.=self::$_EOL.$spaces.')';
				}
				break;
		}
	}
}
?>