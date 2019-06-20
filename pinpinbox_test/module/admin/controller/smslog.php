<?php
class smslogController extends backstageController {
	function __construct() {}
	
	function index() {
		list($html, $js) = parent::$html->grid();
		parent::$data['index'] = $html;
		parent::$html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function form() {
		die;
	}
	
	function delete() {
		die;
	}
	
	function json() {
		$response = array();
		
		$case = isset($_POST['case'])? $_POST['case'] : null;
		
		switch ($case) {
			default:
				//column
				$column = array(
						'sms_id',
						'cellphone',
						'message',
						'callback',
						'request',
						'`return`',
						'inserttime',
				);
				
				list($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
				foreach ($fetchAll as &$v0) {
					$v0['message'] = nl2br(htmlspecialchars($v0['message']));
					$v0['request'] = parent::grid_json_decode($v0['request']);
					$v0['return'] = parent::grid_json_decode($v0['return']);
				}
				$response['data'] = $fetchAll;
				
				//total
				$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
				break;
		}
		
		die(json_encode($response));
	}
}