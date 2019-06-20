<?php
class templateModel extends Model {
	protected $database = 'site';
	protected $table = 'template';
	protected $memcache = 'site';
	protected $join_table = ['templatestatistics', 'templatequeue', 'user', 'event_templatejoin'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function buyable($template_id, $user_id) {
		$result = 1;
		$message = null;
		
		$m_template = Model('template')->column(['act', 'kind', 'state', 'user_id'])->where([[[['template_id', '=', $template_id]], 'and']])->fetch();
		if (empty($m_template)) {
			$result = 0;
			$message = _('Template does not exist.');
			goto _return;
		}
		
		if ($m_template['act'] != 'open') {
			$result = 0;
			$message = _('Template is not open.');
			goto _return;
		}
		
		if ($m_template['state'] != 'success') {
			$result = 0;
			$message = _('Template\'s state is not in finished.');
			goto _return;
		}
		
		if ($m_template['user_id'] == $user_id) {
			$result = 2;
			$message = _('You are the author of this template.');
			goto _return;
		}
		
		$m_templatequeue = Model('templatequeue')->column(['count(1)'])->where([[[['user_id', '=', $user_id], ['template_id', '=', $template_id]], 'and']])->fetchColumn();
		if ($m_templatequeue) {
			$result = 2;
			$message = _('You already have this template.');
			goto _return;
		}
		
		_return: return array_encode_return($result, $message);
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
				['class'=>'albumModel'],
				['class'=>'templatestatisticsModel'],
				['class'=>'templatequeueModel'],
				['class'=>'exchangeModel'],
				['class'=>'event_templatejoinModel'],
		];
		
		return $return;
	}
	
	function is_own($template_id, $user_id) {
		$return = false;
		$m_template = Model('template')->column(['count(1)'])->where([[[['template_id', '=', $template_id], ['user_id', '=', $user_id]], 'and']])->fetchColumn();
		if ($m_template) {
			$return = true;
		} else {
			$m_templatequeue = Model('templatequeue')->column(['count(1)'])->where([[[['user_id', '=', $user_id], ['template_id', '=', $template_id]], 'and']])->fetchColumn();
			if ($m_templatequeue) $return = true;
		}
	
		return $return;
	}
	
	function getFree(array $where=null, array $order=null, $limit=null) {
		$column = [
				'template.template_id',
				'template.name template_name',
				'template.point',
				'template.description',
				'template.image',
				'user.name user_name',
				'templatestatistics.count'
		];
		
		$join = [
				['left join', 'user', 'using(user_id)'],
				['inner join', 'templatestatistics', 'using(template_id)'],
		];
		
		$where = array_merge([[[['template.state', '=', 'success'], ['template.act', '=', 'open'], ['template.point', '=', 0]], 'and']], (array)$where);
		
		return Model('template')->column($column)->join($join)->where($where)->order($order)->limit($limit)->fetchAll();
	}
	
	function getHot(array $where=null, array $order=null, $limit=null) {
		$column = [
				'template.template_id',
				'template.name template_name',
				'template.point',
				'template.description',
				'template.image',
				'user.name user_name',
				'templatestatistics.count'
		];
		
		$join = [
				['left join', 'user', 'using(user_id)'],
				['inner join', 'templatestatistics', 'using(template_id)'],
		];
		
		$where = array_merge([[[['template.state', '=', 'success'], ['template.act', '=', 'open']], 'and']], (array)$where);
		
		$order = array_merge(['templatestatistics.count'=>'desc'], (array)$order);
		
		return Model('template')->column($column)->join($join)->where($where)->order($order)->limit($limit)->fetchAll();
	}
	
	function getOwn($user_id, array $where=null, array $order=null, $limit=null) {
		$column = [
				'DISTINCT(template.template_id)',
				'template.name template_name',
				'template.point',
				'template.description',
				'template.image',
				'user.name user_name',
				'templatestatistics.count'
		];
		
		$join = [
				['left join', 'user', 'using(user_id)'],
				['inner join', 'templatestatistics', 'using(template_id)'],
				['inner join', 'templatequeue', 'using(template_id)'],
		];
		
		$where = array_merge([[[['template.state', '=', 'success'], ['template.act', '=', 'open'], ['templatequeue.user_id', '=', $user_id]], 'and']], (array)$where);
		
		return Model('template')->column($column)->join($join)->where($where)->order($order)->limit($limit)->fetchAll();
	}
	
	function getSponsored(array $where=null, array $order=null, $limit=null) {
		$column = [
				'template.template_id',
				'template.name template_name',
				'template.point',
				'template.description',
				'template.image',
				'user.name user_name',
				'templatestatistics.count'
		];
		
		$join = [
				['left join', 'user', 'using(user_id)'],
				['inner join', 'templatestatistics', 'using(template_id)'],
		];
		
		$where = array_merge([[[['template.state', '=', 'success'], ['template.act', '=', 'open'], ['template.point', '>', 0]], 'and']], (array)$where);
		
		return Model('template')->column($column)->join($join)->where($where)->order($order)->limit($limit)->fetchAll();
	}

	function mine_v2($user_id, array $where=null, array $order=null, $limit=null) {
		$column = [
				'template.template_id',
				'template.name template_name',
				'template.description',
				'template.image cover',
				'template.point',
				'template.act',
				'template.inserttime',
				'user.user_id',
				'user.name user_name',
				'templatestatistics.viewed',
		];
		$join = [
				['inner join', 'user', 'using(user_id)'],
				['inner join', 'templatestatistics', 'using(template_id)'],
		];
		$where = array_merge([[[['template.user_id', '=', $user_id], ['template.state', 'in', ['pretreat', 'process', 'success']], ['template.act', 'in', ['close', 'open']], ['user.act', '=', 'open']], 'and']], (array)$where);
		
		return (new \templateModel)->column($column)->join($join)->where($where)->order($order)->limit($limit)->fetchAll();
	}
	
	function usable($template_id, $user_id) {
		$result = 1;
		$message = null;
		
		//2016-04-14 Lion: 0 為沒有使用版型
		if ($template_id !== 0) {
			$m_template = Model('template')->column(['act', 'kind', 'state', 'user_id'])->where([[[['template_id', '=', $template_id]], 'and']])->fetch();
			if (empty($m_template)) {
				$result = 0;
				$message = _('Template does not exist.');
			} elseif ($m_template['act'] != 'open') {
				$result = 0;
				$message = _('Template is not open.');
			} elseif ($m_template['state'] != 'success') {
				$result = 0;
				$message = _('Template\'s state is not in finished.');
			} else {
				if ($user_id != $m_template['user_id'] && $m_template['kind'] != 'basic') {
					$m_templatequeue = Model('templatequeue')->column(['count(1)'])->where([[[['user_id', '=', $user_id], ['template_id', '=', $template_id]], 'and']])->fetchColumn();
					if (!$m_templatequeue) {
						$result = 0;
						$message = _('You do not have this template.');
					}
				}
			}
		}
		
		return array_encode_return($result, $message);
	}
	
	function visible($template_id) {
		$result = 1;
		$message = null;
	
		$m_template = Model('template')->column(['act', 'state'])->where([[[['template_id', '=', $template_id]], 'and']])->fetch();
		if (empty($m_template)) {
			$result = 0;
			$message = _('Template does not exist.');
		} elseif ($m_template['act'] != 'open') {
			$result = 0;
			$message = _('Template is not open.');
		} elseif ($m_template['state'] != 'success') {
			$result = 0;
			$message = _('Template\'s state is not in finished.');
		}
	
		return array_encode_return($result, $message);
	}
	
	function zip($template_id) {
		$column = array(
				'template.template_id',
				'template.frame_upload',
		);
		$where = [
				[[['template_id', '=', $template_id]], 'and']
		];
		$m_template = Model('template')->column($column)->where($where)->fetch();
		
		if (empty($m_template)) return array_encode_return(0);
		
		$a_frame_upload = json_decode($m_template['frame_upload'], true);
		if ($a_frame_upload) {
			$subpathname_template_storage = SITE_LANG.DIRECTORY_SEPARATOR.'template';
			$pathname_template_storage = mkdir_p(PATH_STORAGE, $subpathname_template_storage);
			
			$zip = new PclZip(PATH_STORAGE.iconv('UTF-8', 'Big5', storagefile($subpathname_template_storage.DIRECTORY_SEPARATOR.$m_template['template_id'].'.zip')));
			$zip_box = [];
			
			foreach ($a_frame_upload as $k0 => $v0) {
				$zip_box[] = [
						PCLZIP_ATT_FILE_NAME=>$k0.'.png',
						PCLZIP_ATT_FILE_CONTENT=>file_get_contents(PATH_UPLOAD.'pinpinbox'.DIRECTORY_SEPARATOR.$v0['src'])
				];
			}
			
			$zip->create($zip_box, PCLZIP_OPT_REMOVE_PATH, $pathname_template_storage);
			
			return array_encode_return(1);
		}
		
		return array_encode_return(0);
	}
}