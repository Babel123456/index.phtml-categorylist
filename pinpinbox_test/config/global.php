<?php
echo "<script>console.log(".json_encode("\config\global.php:start(設檔案上傳錯誤訊息, newsarea...)".date ("Y-m-d H:i:s" , mktime(date('H')+6, date('i'), date('s'), date('m'), date('d'), date('Y')))).");</script>";

/**
 * 含有貨幣單位 (unit) 的欄位 table 有
 * 1. cashflow
 * 以上, 由於欄位型態是 enum, 所以如果要寫入新的值, 必須先建立
 */

$CONFIG['UPLOAD']['ERROR_MESSAGE'] = array(
		0 => 'There is no error, the file uploaded with success.',
		1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
		2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
		3 => 'The uploaded file was only partially uploaded.',
		4 => 'No file was uploaded.',
		6 => 'Missing a temporary folder.',
		7 => 'Failed to write file to disk.',
		8 => 'A PHP extension stopped the file upload.',
);

/**
 * X_site
 */
//news.act
$CONFIG['NEWS_ACT'] = array('close'=>_('Close'), 'preview'=>_('Preview'), 'open'=>_('Open'));

//news.class (目前 magazine 有獨立的顯示頁面)
$CONFIG['NEWS_CLASS'] = array('blog'=>_('婚享名人'), 'company'=>_('婚享好友'), 'event'=>_('婚享優惠'), 'fan'=>_('婚享報報'), 'lectures'=>_('婚享講座'), 'magazine'=>_('婚享誌'));

//newsarea.act
$CONFIG['NEWSAREA_ACT'] = array('close'=>_('Close'), 'preview'=>_('Preview'), 'open'=>_('Open'));

echo "<script>console.log(".json_encode("\config\global.php:end(設檔案上傳錯誤訊息等)".date ("Y-m-d H:i:s" , mktime(date('H')+6, date('i'), date('s'), date('m'), date('d'), date('Y')))).");</script>";
