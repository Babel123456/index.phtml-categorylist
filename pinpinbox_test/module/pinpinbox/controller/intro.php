<?php
class introController extends frontstageController {
	function __construct() {}
	
	function index() {
		//#1387 此頁暫不接受訪問
		redirect(parent::url('about'));
				
		$seo = $this->seo(
			Core::settings('SITE_TITLE') .' | '._('intro'),
			array(_('intro'))
		);
		
		parent::$data['seo'] = $seo;
		
		if (SDK('Mobile_Detect')->isMobile()) redirect(parent::url('m_intro'));

		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
}