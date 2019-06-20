<?php
class adminselfController extends backstageController {
	function __construct() {}
	
	function index() {
		parent::$data['action'] = parent::url(M_CLASS, 'edit');
		
		//form for edit
		$m_admin = adminModel::newly()->where([[[['admin_id', '=', adminModel::getSession()['admin_id']]], 'and']])->fetch();
		
		$account = $m_admin['account'];
		$name = $m_admin['name'];
		$email = $m_admin['email'];
		$inserttime = $m_admin['inserttime'];
		$modifytime = $m_admin['modifytime'];
		$modifyadmin_name = adminModel::getOne($m_admin['modifyadmin_id'])['name'];
		
		//form
		$column = [];
		$extra = null;
		
		$column[] = array('key'=>_('Account'), 'value'=>$account);
		
		list($html, $js) = parent::$html->password(['id'=>'oldpassword', 'name'=>'oldpassword']);
		$column[] = array('key'=>_('Old Password'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->password(['id'=>'password', 'name'=>'password']);
		$column[] = array('key'=>_('Password'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->password(['id'=>'repassword', 'name'=>'repassword']);
		$column[] = array('key'=>_('Re Password'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'"');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Email'), 'value'=>$email);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$inserttime);
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>$modifytime);
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name);
		
		list($html, $js) = parent::$html->submit('value="'._('Submit').'"');
		$column[] = array('key'=>'&nbsp;', 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->table('class="table"', $column, $extra);
		$a_tabs[0] = array('href'=>'#tabs-0', 'name'=>_('Form'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->tabs($a_tabs);
		$formcontent = $html;
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->form('id="form" action="" method="post" onsubmit="false"', $formcontent);
		parent::$data['form'] = $html;
		parent::$html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function edit() {
		if (is_ajax()) {
			$s_admin = adminModel::getSession();
			
			$oldpassword = (isset($_POST['oldpassword']) && $_POST['oldpassword'] !== '')? $_POST['oldpassword'] : null;
			$name = (isset($_POST['name']) && trim($_POST['name']) !== '')? trim($_POST['name']) : null;
			$password = (isset($_POST['password']) && $_POST['password'] !== '')? $_POST['password'] : null;
			
			if (Model('admin')->column(['count(1)'])->where([[[['admin_id', '!=', $s_admin['admin_id']], ['name', '=', $name]], 'and']])->fetchColumn()) {
				json_encode_return(0, _('Data already exists by : ').'Name');
			}
			
			$edit = [];
			if ($password !== null) {
				$m_admin = Model('admin')->column(['`password`'])->where([[[['admin_id', '=', $s_admin['admin_id']]], 'and']])->fetch();
				if (!password_verify($oldpassword, $m_admin['password'])) {
					json_encode_return(0, _('Password is incorrect.'));
				}
				$edit['password'] = password_hash($password, PASSWORD_DEFAULT);
			}
			$edit['name'] = $name;
			$edit['modifyadmin_id'] = $s_admin['admin_id'];
			Model('admin')->where([[[['admin_id', '=', $s_admin['admin_id']]], 'and']])->edit($edit);
			
			json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
		}
		die;
	}
}