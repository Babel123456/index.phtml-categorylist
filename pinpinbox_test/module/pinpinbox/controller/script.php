<?php
class scriptController extends frontstageController {
	private $model = [];
	private $return;
	private $scriptlog_id;
	private $time;
	
	function __construct() {
		$mode = isset($_POST['mode'])? $_POST['mode'] : null;
		$sign = isset($_POST['sign'])? $_POST['sign'] : null;
		
		if ($mode === null || $sign === null) redirect(frontstageController::url('_', '_404'));//參數匹配下才作用
		
		ksort($_POST);
		
		if ($sign != encrypt(array('mode'=>$mode))) {
			$result = 0;
			$message = _('Sign error.');
			$add = array(
					'script_id'=>M_FUNCTION,
					'request'=>json_encode($_POST),
					'`return`'=>json_encode(array_encode_return($result, $message)),
					'state'=>'fail',
					'inserttime'=>inserttime(),
			);
			Model('scriptlog')->add($add);
			json_encode_return($result, $message);
		}
		
		$m_script = Model('script')->where(array(array(array(array('script_id', '=', M_FUNCTION)), 'and')))->fetch();
		if (empty($m_script)) {
			$result = 0;
			$message = _('Script is not exist.');
			$add = array(
					'script_id'=>M_FUNCTION,
					'request'=>json_encode($_POST),
					'`return`'=>json_encode(array_encode_return($result, $message)),
					'state'=>'fail',
					'inserttime'=>inserttime(),
			);
			Model('scriptlog')->add($add);
			json_encode_return($result, $message);
		} elseif ($m_script['act'] != 'open') {
			$result = 0;
			$message = _('Script is not open.');
			$add = array(
					'script_id'=>M_FUNCTION,
					'request'=>json_encode($_POST),
					'`return`'=>json_encode(array_encode_return($result, $message)),
					'state'=>'fail',
					'inserttime'=>inserttime(),
			);
			Model('scriptlog')->add($add);
			json_encode_return($result, $message);
		}
		
		if ($mode == 'test') {
			$result = 1;
			$message = _('Script can be triggered.');
			$add = array(
					'script_id'=>M_FUNCTION,
					'request'=>json_encode($_POST),
					'`return`'=>json_encode(array_encode_return($result, $message)),
					'state'=>'success',
					'inserttime'=>inserttime(),
			);
			Model('scriptlog')->add($add);
			json_encode_return($result, $message);
		}
	}
	
	function __destruct() {
		die;
	}
	
	function __start($singleton=true) {
		set_time_limit(0);
		
		foreach (array_merge($this->model, array('scriptlog')) as $v0) {
			Model($v0);
		}
		
		Model()->beginTransaction();
		
		$this->time = time();
		
		$add = array(
				'script_id'=>M_FUNCTION,
				'request'=>json_encode($_POST),
				'state'=>'pretreat',
				'inserttime'=>inserttime(),
		);
		$this->scriptlog_id = Model('scriptlog')->add($add);
		
		//擋下執行相同的 script
		if ($singleton) {
			$m_scriptlog = Model('scriptlog')->where(array(array(array(array('scriptlog_id', '!=', $this->scriptlog_id), array('script_id', '=', M_FUNCTION), array('state', 'in', array('pretreat', 'process'))), 'and')))->lock('for update')->fetch();
			if (!empty($m_scriptlog)) {
				$this->return = array_encode_return(0, _('Script is being processed.'));
				$this->__end();
			}
		}
		
		//process
		$where = array(
				array(array(array('scriptlog_id', '=', $this->scriptlog_id)), 'and'),
		);
		$edit = array(
				'state'=>'process',
		);
		Model('scriptlog')->where($where)->edit($edit);
	}
	
	function __end() {
		list ($result, $message, $redirect, $data) = array_decode_return($this->return);
		$where = array(
				array(array(array('scriptlog_id', '=', $this->scriptlog_id)), 'and'),
		);
		$edit = array(
				'runtime'=>time() - $this->time,
				'`return`'=>json_encode($this->return),
				'state'=>$result == 1? 'success' : 'fail',
		);
		Model('scriptlog')->where($where)->edit($edit);
		Model()->commit();
		json_encode_return($result, $message);
	}
	
	function zippedAlbum() {
		//自訂參數
		$startalbum_id = isset($_POST['startalbum_id'])? $_POST['startalbum_id'] : 1;
		$endalbum_id = isset($_POST['endalbum_id'])? $_POST['endalbum_id'] : 10;
		
		if (isset($_POST['ready']) && $_POST['ready']) {
			json_encode_return(1, null, null, ['Start Album ID'=>$startalbum_id, 'End Album ID'=>$endalbum_id]);
		}
		
		$this->model = array_merge($this->model, ['album', 'audio', 'photo']);
		
		$this->__start();
		 
		ini_set('memory_limit', -1);
		
		$m_album = Model('album')->column(['album_id'])->where([[[['album_id', 'between', [$startalbum_id, $endalbum_id]], ['zipped', '=', 1]], 'and']])->order(['album_id'=>'asc'])->fetchAll();
		
		foreach ($m_album as $v0) {
			Model('album')->zip($v0['album_id']);
		}
		
		//success
		$this->return = array_encode_return(1, 'Script is succeeded. Execute [Album] x '.number_format(count($m_album)).', Album ID between '.$startalbum_id.' ~ '.$endalbum_id.'.');
		$this->__end();
	}
}