<?php
class generate_qrcodeController extends backstageController {
	function __construct() {}
	
	function url() {
		if (! empty ( $_POST )) {
			$url = $_POST ['url'];
			$file = (empty ( $_POST ['file'] )) ? false : urldecode ( $_POST ['file'] );
			
			if (! class_exists ( 'QRcode' ))
				include PATH_LIB . '/phpqrcode_1.1.4/phpqrcode.php';
			$QRcode = new QRcode ();
			$QRcode->png ( $url, PATH_TMP_FILE . $file );
			
			json_encode_return(1, _('Success'), null, '<img src="' . URL_TMP_FILE . $file . '?='.time().'" />');
		}
		die ();
	}
	
	function phone($phone) {
		//^parent::png ( 'tel:' . $phone );
		die ();
	}
	
	function email($email, $subject = null, $message = null) {
		//^
		/*
		$tmp1 = array ();
		if (! empty ( $subject ))
			$tmp1 ['subject'] = urlencode ( $subject );
		if (! empty ( $message ))
			$tmp1 ['body'] = urlencode ( $message );
		$tmp1 = (empty ( $tmp1 )) ? null : '?' . http_build_query ( $tmp1 );
		parent::png ( 'mailto:' . $email . $tmp1 );
		*/
		die ();
	}
}