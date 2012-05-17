<?php

class phpTimer {

	function phpTimer () {	
		$this->_version = '0.1';
		$this->_enabled = true;
	}

	function start ($name = 'default') {
		if($this->_enabled) {
			$this->_timing_start_times[$name] = explode(' ', microtime());
		}
	}

	function stop ($name = 'default') {
		if($this->_enabled) {
			$this->_timing_stop_times[$name] = explode(' ', microtime());
		}
	}

	function get_current ($name = 'default') {
		if($this->_enabled) {
			if (!isset($this->_timing_start_times[$name])) {
				return 0;
			}
			if (!isset($this->_timing_stop_times[$name])) {
				$stop_time = explode(' ', microtime());
			} else {
				$stop_time = $this->_timing_stop_times[$name];
			}
			// do the big numbers first so the small ones aren't lost
			$current = $stop_time[1] - $this->_timing_start_times[$name][1];
			$current += $stop_time[0] - $this->_timing_start_times[$name][0];
			return sprintf("%.10f",$current);
		} else {
			return 0;
		}
	}

}
?>