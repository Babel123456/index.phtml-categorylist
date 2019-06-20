<?php
class adminController extends backstageController {
	function __construct() {}
	
	function index() {
		list($html1, $js1) = parent::$html->grid();
		parent::$data['index'] = $html1;
		parent::$html->set_js($js1);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function form() {
		if (is_ajax()) {
			//form
			$password = $_POST['password'];
			$name = $_POST['name'];
			$email = $_POST['email'];
			$act = $_POST['act'];
			$a_admin_admingroup = $_POST['admin_admingroup'];
			$lastloginadmingroup_id = $a_admin_admingroup[0]['admingroup_id'];//預設 lastloginadmingroup_id
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					$account = $_POST['account'];
					
					if (Model(M_CLASS)->column(['count(1)'])->where([[[['account', '=', $account]], 'and']])->fetchColumn()) {
						json_encode_return(0, _('Data already exists by : ').'Account');
					}
					if (Model(M_CLASS)->column(['count(1)'])->where([[[['name', '=', $name]], 'and']])->fetchColumn()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					Model(M_CLASS);
					Model('admin_admingroup');
					Model()->beginTransaction();
					
					//admin
					$add = [
							'account'=>$account,
							'password'=>password_hash($password, PASSWORD_DEFAULT),
							'name'=>$name,
							'email'=>$email,
							'act'=>$act,
							'lastloginadmingroup_id'=>$lastloginadmingroup_id,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					];
					$M_CLASS_id = Model(M_CLASS)->add($add);
					
					//admin_admingroup
					if (!empty($a_admin_admingroup)) {
						$add = [];
						foreach ($a_admin_admingroup as $v0) {
							$add[] = [
									'admin_id'=>$M_CLASS_id,
									'admingroup_id'=>(int)$v0['admingroup_id'],
									'class'=>$v0['class'],
							];
						}
						Model('admin_admingroup')->add($add);
					}
					
					Model()->commit();
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
						
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					$oldpassword = $_POST['oldpassword'];
					
					if (Model(M_CLASS)->column(['count(1)'])->where([[[[M_CLASS.'_id', '!=', $M_CLASS_id], ['name', '=', $name]], 'and']])->fetchColumn()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					//admin
					$edit = [
							'name'=>$name,
							'email'=>$email,
							'act'=>$act,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					];
					
					if ($password !== null) {
						$m_admin = Model(M_CLASS)->column(['`password`'])->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->fetch();
						if (!password_verify($oldpassword, $m_admin['password'])) {
							json_encode_return(0, _('Password is incorrect.'));
						}
						$edit['password'] = password_hash($password, PASSWORD_DEFAULT);
					}
					
					Model(M_CLASS);
					Model('admin_admingroup');
					Model()->beginTransaction();
					
					Model(M_CLASS)->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->edit($edit);
					
					//admin_admingroup
					Model('admin_admingroup')->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->delete();
					if (!empty($a_admin_admingroup)) Model('admin_admingroup')->add($a_admin_admingroup);
					
					Model()->commit();
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$account = null;
		$name = null;
		$email = null;
		$act = 'open';
		$a_admingroup_id = array();
		$a_admingroup_class = array();
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_name = null;
		
		//form
		$column = array();
		$extra = null;
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				list($html, $js) = parent::$html->text('id="account" name="account" maxlength="32" size="32"');
				$column[] = array('key'=>_('Account'), 'value'=>$html);
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'add'));
				break;
				
			//修改	
			case 'edit':
				$M_CLASS_id = $_GET[M_CLASS.'_id'];
				
				$m_admin = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
				
				//form
				$account = $m_admin['account'];
				$name = $m_admin['name'];
				$email = $m_admin['email'];
				$act = $m_admin['act'];
				$inserttime = $m_admin['inserttime'];
				$modifytime = $m_admin['modifytime'];
				$modifyadmin_name = adminModel::getOne($m_admin['modifyadmin_id'])['name'];
				
				//admingroup
				$m_admin_admingroup = Model('admin_admingroup')->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetchAll();
				foreach ($m_admin_admingroup as $v1) {
					$a_admingroup_id[] = $v1['admingroup_id'];
					$a_admingroup_class[$v1['admingroup_id']] = $v1['class'];
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$column[] = array('key'=>_('Account'), 'value'=>$account.$html);
				parent::$html->set_js($js);
				
				list($html, $js) = parent::$html->password(['id'=>'oldpassword', 'name'=>'oldpassword']);
				$column[] = array('key'=>_('Old Password'), 'value'=>$html);
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		list($html, $js) = parent::$html->password(['id'=>'password', 'name'=>'password']);
		$column[] = array('key'=>_('Password'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->password(['id'=>'repassword', 'name'=>'repassword']);
		$column[] = array('key'=>_('Re Password'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" required');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->email(['id'=>'email', 'maxlength'=>64, 'name'=>'email', 'size'=>64, 'value'=>$email]);
		$column[] = array('key'=>_('Email'), 'value'=>$html);
		parent::$html->set_js($js);
		
		$a_act = array();
		foreach (json_decode(Core::settings('ADMIN_ACT'), true) as $k0 => $v0) {
			$a_act[] = array(
					'name'=>'act',
					'value'=>$k0,
					'text'=>$v0,
			);
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
		$column[] = array('key'=>_('Act'), 'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$inserttime);
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>$modifytime);
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name);
		
		$m_admingroup = Model('admingroup')->fetchAll();
		$admingroup = array();
		$admingroup_class = array();
		foreach ($m_admingroup as $k => $v) {
			$admingroup[$k]['key'] = $v['admingroup_id'];
			$admingroup[$k]['name'] = 'admingroup_id';
			$admingroup[$k]['value'] = $v['admingroup_id'];
			$admingroup[$k]['text'] = $v['name'];
			$admingroup[$k]['checked'] = (is_array($a_admingroup_id) && in_array($v['admingroup_id'], $a_admingroup_id))? true : false;
			foreach (json_decode(Core::settings('ADMIN_ADMINGROUP_CLASS'), true) as $k2 => $v2) {
				$admingroup_class[$v['admingroup_id']][] = array(
						'name'=>'class['.$v['admingroup_id'].']',
						'value'=>$k2,
						'text'=>$v2,
						'checked'=>(isset($a_admingroup_class[$v['admingroup_id']]) && $k2 == $a_admingroup_class[$v['admingroup_id']])? true : false,
				);
			}
		}
		list($html, $js) = parent::$html->checkedtable(array('width'=>160, 'height'=>40, 'col'=>4), 'checkbox', $admingroup, 'radio', $admingroup_class);
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('admingroup'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($tmp_html, $tmp_js) = parent::$html->submit('value="'._('Submit').'"');
		list($html, $js) = parent::$html->back('value="'._('Back').'"');
		$html = $tmp_html.'&emsp;'.$html;
		$js = $tmp_js.$js;
		$column[] = array('key'=>'&nbsp;', 'value'=>$html);
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->table('class="table"', $column, $extra);
		$formcontent = $html;
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->form('id="form"', $formcontent);
		parent::$data['form'] = $html;
		parent::$html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function delete() {
		if (!empty($_POST[M_CLASS.'_id'])) {
			Model(M_CLASS);
			Model('admin_admingroup');
			Model()->beginTransaction();
			
			//admin
			Model(M_CLASS)->where([[[[M_CLASS.'_id', '=', $_POST[M_CLASS.'_id']]], 'and']])->delete();
			
			//admin_admingroup
			Model('admin_admingroup')->where([[[[M_CLASS.'_id', '=', $_POST[M_CLASS.'_id']]], 'and']])->delete();
			
			Model()->commit();
			
			json_encode_return(1, _('Success'));
		}
		die;
	}
	
	function json() {
		$response = [];
		
		//column
		$column = [
				M_CLASS.'_id',
				'account',
				'name',
				'email',
				'act',
				'lastloginip',
				'lastlogintime',
				'modifytime',
		];
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$response['data'] = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		
		//total
		$response['total'] = Model(M_CLASS)->column(['count(1)'])->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
}