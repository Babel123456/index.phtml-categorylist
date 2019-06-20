<?php
class intro extends controller {
	function __construct() {}
	
	function index() {
		$seo = $this->seo(
			Core::settings('SITE_TITLE') .' | '._('intro'),
			array(_('intro'))
		);
		
		parent::$data['seo'] = $seo;
		
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
}