<?php
class reportModel extends Model {
	protected $database = 'site';
	protected $table = 'report';
	protected $memcache = 'site';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
		];
	
		return $return;
	}
	
	function report($reportintent_id, $user_id, $type, $type_id, $description=null) {
		$result = 1;
		$message = null;
		
		switch ($type) {
			case 'album':
				$m_album = Model('album')->column(['act'])->where([[[['album_id', '=', $type_id]], 'and']])->fetch();
				if (empty($m_album)) {
					$result = 0;
					$message = _('Album does not exist.');
					goto _return;
				} else {
					if ($m_album['act'] == 'close') {
						$result = 0;
						$message = _('Album is not open.');
						goto _return;
					} elseif ($m_album['act'] == 'delete') {
						$result = 0;
						$message = _('Album does not exist.');
						goto _return;
					}
				}
				break;
				
			case 'template':
				$m_template = Model('template')->column(['act', 'state'])->where([[[['template_id', '=', $type_id]], 'and']])->fetch();
				if (empty($m_template)) {
					$result = 0;
					$message = _('Template does not exist.');
					goto _return;
				} else {
					if ($m_template['act'] == 'close') {
						$result = 0;
						$message = _('Template is closed.');
						goto _return;
					} elseif ($m_template['state'] != 'success') {
						$result = 0;
						$message = _('Template\'s state is not in finished.');
						goto _return;
					}
				}
				break;
				
			default:
				throw new Exception('Unknown case');
				break;
		}
		
		//同相本/版型重複檢舉且未處理數量超過三筆 , 十分鐘內檢舉過
		$m_report = Model('report')->column(['inserttime'])->where([[[['user_id', '=', $user_id], ['`type`', '=', $type], ['id', '=', $type_id], ['state', '=', 'pretreat']], 'and']])->order(['inserttime'=>'desc'])->fetchAll();
		if ($m_report) {
			if (count($m_report) > 3) {
				$result = 0;
				switch ($type) {
					case 'album':
						$message = _('You have been report this album, we will deal with as soon as possible.');
						break;
						
					case 'template':
						$message = _('You have been report this template, we will deal with as soon as possible.');
						break;
				}
				goto _return;
			} elseif (strtotime('+10 minute', strtotime($m_report[0]['inserttime'])) >= time()) {
				$result = 0;
				$message = _('This operation cannot redo within 10 minutes.');
				goto _return;
			}
		}
		
		$add = [
				'reportintent_id'=>$reportintent_id,
				'user_id'=>$user_id,
				'type'=>$type,
				'id'=>$type_id,
				'description'=>$description,
				'state'=>'pretreat',
				'inserttime'=>inserttime(),
		];
		Model('report')->add($add);
		
		_return: return array_encode_return($result, $message);
	}
}