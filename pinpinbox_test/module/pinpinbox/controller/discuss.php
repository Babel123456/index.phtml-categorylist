<?php
class discussController extends frontstageController {
	function __construct() {}
	
	function index() {
		if (empty($_GET['albumid'])) {
			throw new Exception('Abnormal process');
		}
		
		$album_id = $_GET['albumid'];
		
		$m_album = albumModel::newly()->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
		if (empty($m_album)) {
			throw new Exception(_('Album does not exist.'));
		} 
		
		//disqus
		parent::$data['disqus'] = parent::disqus('album', $m_album['album_id']);
		
		parent::$data['album_id'] = $m_album['album_id'];
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
}