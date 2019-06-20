<?php

namespace lib;

if (! class_exists ( 'QRcode' ))
	include PATH_LIB . '/phpqrcode_1.1.4/phpqrcode.php';
use QRcode as phpqrcode;

class qrcode extends phpqrcode {
	function url($url, $outfile = false) {
		parent::png ( $url, $outfile );
	}
	function phone($phone) {
		parent::png ( 'tel:' . $phone );
	}
	function email($email, $subject = null, $message = null) {
		$tmp1 = array ();
		if (! empty ( $subject ))
			$tmp1 ['subject'] = urlencode ( $subject );
		if (! empty ( $message ))
			$tmp1 ['body'] = urlencode ( $message );
		$tmp1 = (empty ( $tmp1 )) ? null : '?' . http_build_query ( $tmp1 );
		parent::png ( 'mailto:' . $email . $tmp1 );
	}
}