<?
function clean($dir, $level = 0){
	$_tabulate = '';
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry != '.' && $entry != '..') {
			$path = $dir.'/'.$entry;
			if (is_dir($path)) {
				clean($path, ++$level);
				$ok = rmdir($path);
				$color = $ok ? 'green' : 'red';
				echo str_repeat($_tabulate, $level).'(DIR)'.$path.'&nbsp;<font color=\''.$color.'\'>'.($ok ? 'removed' : 'failed')."</font><br />";
			} else if (is_file($path)) {
				$ok = unlink($path);
				$color = $ok ? 'green' : 'red';
				echo str_repeat($_tabulate, $level).'(FILE)'.$path.'&nbsp;<font color=\''.$color.'\'>'.($ok ? 'removed' : 'failed')."</font><br />";
			} 
		}
	}
	$d->close();
}
function cleansvn($dir, $level = 0){
	$d = dir($dir);
	static $i = 0;
	while (false !== ($entry = $d->read())) {
		if ($entry != '.' && $entry != '..') {
			$i++;
			$path = $dir.'/'.$entry;
			if (is_dir($path)) {
				if ($entry === '.svn') {
					clean($path, ++$level);
					$ok = rmdir($path);
					$color = $ok ? 'green' : 'red';
					echo str_repeat($_tabulate, $level).'(DIR)'.$path.'&nbsp;<font color=\''.$color.'\'>'.($ok ? 'removed' : 'failed')."</font><br />";
				} else {
					cleansvn($path, ++$level);
				}
			}
		}
	}
	$d->close();
	if (!$i) echo $dir.' is empty...';
}
cleansvn('./');
?>