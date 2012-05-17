<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pva
 * Date: 6/3/11
 * Time: 6:46 PM
 * To change this template use File | Settings | File Templates.
 */

FileLoader::loadClass('Dumper');

/**
 * @param mixed $var
 * @return void
 */
function dump($var){
    Dumper::dump($var);
}

/**
 * @param string $file
 * @return bool
 */
function file_exists_include_path($file){
    if (file_exists($file)) {
        return true;
    }
    $paths = explode(PATH_SEPARATOR, get_include_path());

    foreach ($paths as $path) {
        if (@file_exists($path.'/'.$file)){
            return true;
        }
    }
    return false;
}

if (!function_exists('array_replace_recursive'))
{
	function recurse($array, $array1)
	{
		foreach ($array1 as $key => $value)
		{
			// create new key in $array, if it is empty or not an array
			if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key])))
			{
				$array[$key] = array();
			}
			// overwrite the value in the base array
			if (is_array($value))
			{
				$value = recurse($array[$key], $value);
			}
			$array[$key] = $value;
		}
		return $array;
	}
	function array_replace_recursive($array, $array1)
	{

		// handle the arguments, merge one by one
		$args = func_get_args();
		$array = $args[0];
		if (!is_array($array))
		{
			return $array;
		}
		for ($i = 1; $i < count($args); $i++)
		{
			if (is_array($args[$i]))
			{
				$array = recurse($array, $args[$i]);
			}
		}
		return $array;
	}
}

// to use this function to totally remove a directory, write:
// recursive_remove_directory('path/to/directory/to/delete');

// to use this function to empty a directory, write:
// recursive_remove_directory('path/to/full_directory',TRUE);

/**
 * @param string $directory
 * @param bool $empty
 * @return bool
 */
function recursive_remove_directory($directory, $empty=FALSE)
{
	// if the path has a slash at the end we remove it here
	if(substr($directory,-1) == '/')
	{
		$directory = substr($directory,0,-1);
	}

	// if the path is not valid or is not a directory ...
	if(!file_exists($directory) || !is_dir($directory))
	{
		// ... we return false and exit the function
		return FALSE;

	// ... if the path is not readable
	}elseif(!is_readable($directory))
	{
		// ... we return false and exit the function
		return FALSE;

	// ... else if the path is readable
	}else{

		// we open the directory
		$handle = opendir($directory);

		// and scan through the items inside
		while (FALSE !== ($item = readdir($handle)))
		{
			// if the filepointer is not the current directory
			// or the parent directory
			if($item != '.' && $item != '..')
			{
				// we build the new path to delete
				$path = $directory.'/'.$item;

				// if the new path is a directory
				if(is_dir($path))
				{
					// we call this function with the new path
					recursive_remove_directory($path);

				// if the new path is a file
				}else{
					// we remove the file
					unlink($path);
				}
			}
		}
		// close the directory
		closedir($handle);

		// if the option to empty is not set to true
		if($empty == FALSE)
		{
			// try to delete the now empty directory
			if(!rmdir($directory))
			{
				// return false if not possible
				return FALSE;
			}
		}
		// return success
		return TRUE;
	}
}

/**
 * @param string $format
 * @param string $date
 * @return string
 */
function date_parse_from($format, $date) {
	$dMask = array(
		'H'=>'hour',
		'i'=>'minute',
		's'=>'second',
		'y'=>'year',
		'm'=>'month',
		'd'=>'day'
	);
	$format = preg_split('//', $format, -1, PREG_SPLIT_NO_EMPTY);
	$date = preg_split('//', $date, -1, PREG_SPLIT_NO_EMPTY);
	$dt = array();
	foreach ($date as $k => $v) {
		if (isset($dMask[$format[$k]])) {
			if (!isset($dt[$dMask[$format[$k]]])) {
				$dt[$dMask[$format[$k]]] = $v;
			} else {
				$dt[$dMask[$format[$k]]] .= $v;
			}
		}
	}
	return $dt;
}

/**
 * @param string $text
 * @param string $encoding
 * @return string
 */
function mb_ucfirst($text, $encoding = 'UTF-8'){
	return mb_convert_case(mb_substr($text, 0, 1, $encoding), MB_CASE_UPPER, $encoding).mb_substr($text, 1, mb_strlen($text, $encoding), $encoding);
}

function mb_wordwrap($str, $width=74, $break="\r\n") {
    // Return short or empty strings untouched
    if(empty($str) || mb_strlen($str, 'UTF-8') <= $width)
        return $str;

    $br_width  = mb_strlen($break, 'UTF-8');
    $str_width = mb_strlen($str, 'UTF-8');
    $return = '';
    $last_space = false;

    for($i=0, $count=0; $i < $str_width; $i++, $count++)
    {
        // If we're at a break
        if (mb_substr($str, $i, $br_width, 'UTF-8') == $break)
        {
            $count = 0;
            $return .= mb_substr($str, $i, $br_width, 'UTF-8');
            $i += $br_width - 1;
            continue;
        }

        // Keep a track of the most recent possible break point
        if(mb_substr($str, $i, 1, 'UTF-8') == " ")
        {
            $last_space = $i;
        }

        // It's time to wrap
        if ($count > $width)
        {
            // There are no spaces to break on!  Going to truncate :(
            if(!$last_space)
            {
                $return .= $break;
                $count = 0;
            }
            else
            {
                // Work out how far back the last space was
                $drop = $i - $last_space;

                // Cutting zero chars results in an empty string, so don't do that
                if($drop > 0)
                {
                    $return = mb_substr($return, 0, -$drop);
                }

                // Add a break
                $return .= $break;

                // Update pointers
                $i = $last_space + ($br_width - 1);
                $last_space = false;
                $count = 0;
            }
        }

        // Add character from the input string to the output
        $return .= mb_substr($str, $i, 1, 'UTF-8');
    }
    return $return;
}