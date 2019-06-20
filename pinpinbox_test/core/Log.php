<?php
namespace Core;
class Log {
	function __construct() {}
	
	function write($info=null) {
		$array0 = [];
		
		//info
		if ($info !== null && $info !== '') $array0['info'] = $info;
		
		//ip
		$array0['ip'] = remote_ip();
		
		//server
		$array3 = [];
		if (isset($_SERVER['HTTP_REFERER'])) $array3['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
		if (isset($_SERVER['REQUEST_URI'])) $array3['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
		ksort($array3);
		$array4 = [];
		foreach ($array3 as $k0 => $v0) {
			$array4[] = $k0.': '.$v0;
		}
		$array0['server'] = implode(', ', $array4);
		
		//session
		$session = \Session::get();
		if ($session !== null) $array0['session'] = json_encode($session);
		
		//trace
		$e = new \Exception();
		$trace = explode("\n", $e->getTraceAsString());
		$trace = array_reverse($trace); // reverse array to make steps line up chronologically
		array_shift($trace); // remove {main}
		array_pop($trace); // remove call to this method
		$length = count($trace);
		$array1 = [];
		for ($i = 0; $i < $length; ++$i) {
			$array1[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
		}
		$array0['trace'] = implode(' ', $array1);
		
		//f
		ksort($array0);
		$array2 = [];
		foreach ($array0 as $k0 => $v0) {
			$array2[] = '<'.$k0.'> '.$v0;
		}
		$date = date('Ymd');
		$md5 = md5($date);
		$filename = $date.'$'.substr($md5, 0, 2).substr($md5, -2);
		$handle = fopen(mkdir_p_v2(PATH_LOG . M_PACKAGE . DIRECTORY_SEPARATOR . M_CLASS . DIRECTORY_SEPARATOR) . $filename . '.log', 'a');
		fwrite($handle, '['.date('Y-m-d H:i:s').'] Execute '.number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3)." seconds\r\n".implode("\r\n", $array2)."\r\n");
		fclose($handle);
	}
}