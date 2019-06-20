<?php
if (!class_exists('PclZip')) require PATH_LIB.'pclzip-2-8-2/pclzip.lib.php';
class zip extends PclZip {
	function __construct($zipname) {
		parent::PclZip($zipname);
	}
	
	function add($path_file) {
		parent::add($path_file, PCLZIP_OPT_REMOVE_PATH, PATH_TMP_FILE);
	}
	
	function create($path_file) {
		parent::create($path_file, PCLZIP_OPT_REMOVE_PATH, PATH_TMP_FILE);
	}
}