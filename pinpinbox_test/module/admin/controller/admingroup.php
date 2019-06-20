<?php
class admingroupController extends backstageController {
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
			//admingroup
			$name = $_POST['name'];
			
			//admin_admingroup
			$a_admin_admingroup = $_POST['admin_admingroup'];
			
			//admingroup_adminmenu
			$a_admingroup_adminmenu = $_POST['admingroup_adminmenu'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->where(array(array(array(array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by [Name]'));
					}
					
					Model(M_CLASS);
					Model('admin_admingroup');
					Model('admingroup_adminmenu');
					Model()->beginTransaction();
					
					//admingroup
					$add = array(
							'name'=>$name,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					$M_CLASS_id = Model(M_CLASS)->add($add);
					
					//admin_admingroup
					if (!empty($a_admin_admingroup)) {
						$add = array();
						foreach ($a_admin_admingroup as $v0) {
							$add[] = array('admin_id'=>(int)$v0['admin_id'], 'admingroup_id'=>$M_CLASS_id, 'class'=>$v0['class']);
						}
						Model('admin_admingroup')->add($add);
					}
					
					//admingroup_adminmenu
					if (!empty($a_admingroup_adminmenu)) {
						$add = array();
						foreach ($a_admingroup_adminmenu as $v0) {
							$add[] = array('admingroup_id'=>$M_CLASS_id, 'adminmenu_id'=>(int)$v0['adminmenu_id'], 'act'=>$v0['act']);
						}
						Model('admingroup_adminmenu')->add($add);
					}
					
					Model()->commit();
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
					
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					if (Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '!=', $M_CLASS_id), array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by [Name]'));
					}
					
					Model(M_CLASS);
					Model('admin_admingroup');
					Model('admingroup_adminmenu');
					Model()->beginTransaction();
					
					//admingroup
					$edit = array(
							'name'=>$name,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->edit($edit);
					
					//admin_admingroup
					Model('admin_admingroup')->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->delete();
					if (!empty($a_admin_admingroup)) Model('admin_admingroup')->add($a_admin_admingroup);
					
					//admingroup_adminmenu
					Model('admingroup_adminmenu')->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->delete();
					if (!empty($a_admingroup_adminmenu)) Model('admingroup_adminmenu')->add($a_admingroup_adminmenu);
					
					Model()->commit();
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$a_admin_id = array();
		$a_admin_admingroup_class = array();
		$name = null;
		$class = 'administrator';
		$a_adminmenu_id = array();
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
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'add'));
				break;
				
			//修改	
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					
					$m_admingroup = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
					
					$name = $m_admingroup['name'];
					$inserttime = $m_admingroup['inserttime'];
					$modifytime = $m_admingroup['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_admingroup['modifyadmin_id'])['name'];
					
					//admin
					$join = array();
					$join[] = array('left join', 'admin_admingroup', 'using(admingroup_id)');
					$m_admingroup = Model(M_CLASS)->column(array('admin_admingroup.admin_id', 'admin_admingroup.class'))->join($join)->where(array(array(array(array(M_CLASS.'.'.M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetchAll();
					foreach ($m_admingroup as $v) {
						$a_admin_id[] = $v['admin_id'];
						$a_admin_admingroup_class[$v['admin_id']] = $v['class'];
					}
					
					//adminmenu
					$join = array();
					$join[] = array('left join', 'admingroup_adminmenu', 'using(admingroup_id)');
					$m_admingroup = Model(M_CLASS)->column(array('admingroup_adminmenu.adminmenu_id', 'admingroup_adminmenu.act'))->join($join)->where(array(array(array(array(M_CLASS.'.'.M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetchAll();
					foreach ($m_admingroup as $v) {
						$a_adminmenu_id[$v['adminmenu_id']][] = $v['act'];
					}
				}
				
				list($html, $js) = parent::$html->hidden('id="admingroup_id" name="admingroup_id" value="'.$M_CLASS_id.'"');
				$extra = $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'edit'));
				break;
		}
		
		$m_admin = Model('admin')->fetchAll();
		$admin_lv1 = array();
		$admin_lv2 = array();
		foreach ($m_admin as $v) {
			$admin_lv1[] = array(
					'key'=>$v['admin_id'],
					'name'=>'admin_id',
					'value'=>$v['admin_id'],
					'text'=>$v['name'],
					'checked'=>(is_array($a_admin_id) && in_array($v['admin_id'], $a_admin_id))? true : false,
			);
			foreach (json_decode(Core::settings('ADMIN_ADMINGROUP_CLASS'), true) as $k2 => $v2) {
				$admin_lv2[$v['admin_id']][] = array(
						'name'=>'class['.$v['admin_id'].']',
						'value'=>$k2,
						'text'=>$v2,
						'checked'=>(isset($a_admin_admingroup_class[$v['admin_id']]) && $k2 == $a_admin_admingroup_class[$v['admin_id']])? true : false,
				);
			}
		}
		list($html, $js) = parent::$html->checkedtable(array(), 'checkbox', $admin_lv1, 'radio', $admin_lv2);
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('admin'),'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'"');
		$column[] = array('key'=>_('Name'),'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$inserttime);
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>$modifytime);
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name);
		
		$m_adminmenu = Model('adminmenu')->fetchAll();
		$adminmenu_lv1 = array();
		$adminmenu_lv2 = array();
		foreach ($m_adminmenu as $k => $v) {
			$tmp2 = array();
			if (0 == $v['level']) {
				$tmp2['key'] = $v['adminmenu_id'];
				$tmp2['name'] = 'adminmenu_id';
				$tmp2['value'] = $v['adminmenu_id'];
				$tmp2['text'] = $v['name'];
				$tmp2['checked'] = (is_array($a_adminmenu_id) && array_key_exists($v['adminmenu_id'], $a_adminmenu_id))? true : false;
				$adminmenu_lv1[] = $tmp2;
			} elseif (1 == $v['level']) {
				$tmp2['name'] = 'adminmenu_id';
				$tmp2['value'] = $v['adminmenu_id'];
				$tmp2['text'] = $v['name'];
				$tmp2['checked'] = (is_array($a_adminmenu_id) && array_key_exists($v['adminmenu_id'], $a_adminmenu_id))? true : false;
		
				//child checkboxtable
				$tmp3 = array();
				foreach (json_decode(Core::settings('ADMINGROUP_ADMINMENU_ACT'), true) as $k2 => $v2) {
					$tmp3[$k2]['name'] = 'act['.$v['adminmenu_id'].']';
					$tmp3[$k2]['value'] = $k2;
					$tmp3[$k2]['text'] = $v2;
					$tmp3[$k2]['checked'] = (isset($a_adminmenu_id[$v['adminmenu_id']]) && is_array($a_adminmenu_id[$v['adminmenu_id']]) && in_array($k2, $a_adminmenu_id[$v['adminmenu_id']]))? true : false;
				}
		
				list($html, $js) = parent::$html->checkboxtable('100px', '35px', 4, $tmp3);
				$tmp2['extra'] = $html;
				parent::$html->set_js($js);
		
				$adminmenu_lv2[$v['up']][] = $tmp2;
			}
		}
		list($html, $js) = parent::$html->checkboxtable('520px', '110px', 2, $adminmenu_lv1, $adminmenu_lv2);
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('adminmenu'),'value'=>$html);
		parent::$html->set_js($js);
		
		list($tmp_html, $tmp_js) = parent::$html->submit('value="'._('Submit').'"');
		list($html, $js) = parent::$html->back('value="'._('Back').'"');
		$html = $tmp_html.'&emsp;'.$html;
		$js = $tmp_js.$js;
		$column[] = array('key'=>'&nbsp;', 'value'=>$html);
		parent::$html->set_js($js);
		
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
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function delete() {
		if (!empty($_POST[M_CLASS.'_id'])) {
			Model(M_CLASS);
			Model('admin_admingroup');
			Model('admingroup_adminmenu');
			Model()->beginTransaction();
			
			//admingroup
			Model(M_CLASS)->where([[[[M_CLASS.'_id', '=', $_POST[M_CLASS.'_id']]], 'and']])->delete();
			
			//admin_admingroup
			Model('admin_admingroup')->where([[[[M_CLASS.'_id', '=', $_POST[M_CLASS.'_id']]], 'and']])->delete();
				
			//admingroup_adminmenu
			Model('admingroup_adminmenu')->where([[[[M_CLASS.'_id', '=', $_POST[M_CLASS.'_id']]], 'and']])->delete();
			
			Model()->commit();
			
			json_encode_return(1, _('Success'));
		}
		die;
	}
	
	function json() {
		/*
		if (!class_exists('db')) include PATH_ROOT.'lib/db.php';
		$db = new db(Core::$_config['CONFIG']['DB']['site']);
		
		//取得條件
		$page = $_REQUEST['page'];
		$limit = $_REQUEST['rows'];
		$sidx = $_REQUEST['sidx'];
		$sord = $_REQUEST['sord'];
		$totalrows = isset($_REQUEST['totalrows'])? $_REQUEST['totalrows']: false;
		
		//組 where
		$where = null;
		if (!empty($_REQUEST['filters'])) {
			$filters = json_decode($_REQUEST['filters'], true);
			$groupOp = $filters['groupOp'];
			$rules = $filters['rules'];
			if (!empty($rules)) {
				$tmp = array();
				foreach ($rules as $v) {
					$field = $v['field'];
					$data = $v['data'];
					$tmp[] = $field.' like '.$db->quote($data.'%');
				}
				$where = 'where '.implode(' '.$groupOp.' ', $tmp);
			}
		}
		
		//總筆數
		$sql = "SELECT count(".M_CLASS.".".M_CLASS."_id)
		FROM ".M_CLASS."
		".$where;
		$count = $db->fetchColumn($sql);
		
		//條件
		if ($totalrows) $limit = $totalrows;
		if (!$sidx) $sidx = 1;
		$total_pages = ($count > 0)? ceil($count / $limit) : 0;
		if ($page > $total_pages) $page = $total_pages;
		if ($limit < 0) $limit = 0;
		$start = $limit * $page - $limit;
		if ($start < 0) $start = 0;
		
		//data
		$sql = "SELECT ".M_CLASS.".".M_CLASS."_id, ".M_CLASS.".name, ".M_CLASS.".modifytime, ".M_CLASS.".modifyadmin_id
		FROM ".M_CLASS."
		$where ORDER BY $sidx $sord LIMIT $start, $limit";
		$fetchAll = $db->fetchAll($sql);
		$response = array();
		$response['page'] = $page;
		$response['total'] = $total_pages;
		$response['records'] = $count;
		foreach ($fetchAll as $k => &$v) {
			$v['name'] = '<a href="'.parent::url(M_CLASS, 'form', array('act'=>'edit', 'admingroup_id'=>$v['admingroup_id'])).'">'.$v['name'].'</a>';
			
			$v['modifyadmin_id'] = adminModel::getOne($v['modifyadmin_id'])['name'];
			
			$tmp = array_values($v);
			$v = array();
			$v['cell'] = $tmp;
		}
		$response['rows'] = $fetchAll;
		
		die(json_encode($response));
		*/
		
		$response = array();
		
		//column
		$column = array(
				M_CLASS.'_id',
				'name',
				'modifytime',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$response['data'] = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
}