<?php
class userwhitelistController extends backstageController {
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
		if (is_ajax()) {
			//form
			$user_id = $_POST['user_id'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Core::model(M_CLASS)->where(array(array(array(array('user_id', '=', $user_id)), 'and')))->get()) {
						json_encode_return(0, _('Data already exists by : ').'User ID');
					}
					
					$add = array(
							'user_id'=>$user_id,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Core::model(M_CLASS)->add($add);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
						
				//修改
				case 'edit':
					break;
			}
		}
		
		//初始值-form
		$inserttime = null;
		$modifyadmin_name = null;
		
		//form
		$column = array();
		$extra = null;
		
		//tabs
		$a_tabs = array();
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'add'));
				break;
				
			//修改	
			case 'edit':
				break;
		}
		
		list($html, $js) = parent::$html->text('id="user_id" name="user_id" size="10" maxlength="10" required');
		$column[] = array('key'=>'User ID', 'value'=>$html.'&emsp;<span>avatar : <img data-info="avatar" src="" width="75" height="75" ></span>&emsp;account : <span data-info="account"></span>&emsp;name : <span data-info="name"></span>');
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$inserttime);
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name);
		
		list($html0, $js0) = parent::$html->submit('value="'._('Submit').'"');
		list($html1, $js1) = parent::$html->back('value="'._('Back').'"');
		$column[] = array('key'=>'&nbsp;', 'value'=>$html0.'&emsp;'.$html1);
		parent::$html->set_js($js0.$js1);
		
		list($html, $js) = parent::$html->table('class="table"', $column, $extra);
		$a_tabs[0] = array('href'=>'#tabs-0', 'name'=>_('Form'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->tabs($a_tabs);
		$formcontent = $html;
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->form('id="form"', $formcontent);
		parent::$data['form'] = $html;
		parent::$html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function delete() {
		if (!empty($_POST['user_id'])) {
			Core::model(M_CLASS)->where(array(array(array(array('user_id', '=', $_POST['user_id'])), 'and')))->delete();
			json_encode_return(1, _('Delete success.'));
		}
		die;
	}
	
	function json() {
		$response = array();
		
		//column
		$column = array(
				'user_id',
				'inserttime',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = Core::model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->get('fetchAll');
		foreach ($fetchAll as &$v0) {
			$v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
		}
		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Core::model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->get('fetchColumn');
		
		die(json_encode($response));
	}
	
	//顯示 user info
	function extra0() {
		if (is_ajax()) {
			$m_user = Model('user')->where(array(array(array(array('user_id', '=', $_POST['user_id'])), 'and')))->fetch();
			json_encode_return(1, null, null, array('account'=>$m_user['account'], 'name'=>$m_user['name'], 'avatar'=>URL_STORAGE.Core::get_userpicture($m_user['user_id'])));
		}
		die();
	}
}