<?php
class m_introController extends frontstageController {
	function __construct() {}
	
	function index() {
		$seo = $this->seo(
			Core::settings('SITE_TITLE') .' | '._('intro'),
			array(_('intro'))
		);
		
		parent::$data['seo'] = $seo;
		
		if (!SDK('Mobile_Detect')->isMobile()) redirect(parent::url('intro'));

		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
}