<?php
class adminmenuController extends backstageController {
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
			//adminmenu
			$name = $_POST['name'];
			$class = $_POST['class'];
			$sequence = $_POST['sequence'];
			
			//admingroup_adminmenu
			$a_admingroup_adminmenu = $_POST['admingroup_adminmenu'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					$level = $_POST['level'];
					$up = $_POST['up'];
					
					if (Model(M_CLASS)->where(array(array(array(array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by [Name]'));
					}
					
					Model(M_CLASS);
					Model('admingroup_adminmenu');
					Model()->beginTransaction();
					
					//adminmenu
					$add = array(
							'name'=>$name,
							'level'=>$level,
							'up'=>$up,
							'class'=>$class,
							'sequence'=>$sequence,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					$M_CLASS_id = Model(M_CLASS)->add($add);
					
					//admingroup_adminmenu
					if (!empty($a_admingroup_adminmenu)) {
						$add = array();
						foreach ($a_admingroup_adminmenu as $v0) {
							$add[] = array('admingroup_id'=>(int)$v0['admingroup_id'], 'adminmenu_id'=>$M_CLASS_id, 'act'=>$v0['act']);
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
					Model('admingroup_adminmenu');
					Model()->beginTransaction();
					
					//adminmenu
					$edit = array(
							'name'=>$name,
							'class'=>$class,
							'sequence'=>$sequence,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->edit($edit);
					
					//admingroup_adminmenu
					Model('admingroup_adminmenu')->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->delete();
					if (!empty($a_admingroup_adminmenu)) Model('admingroup_adminmenu')->add($a_admingroup_adminmenu);
					
					Model()->commit();
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$name = null;
		$level = 0;
		$up = null;
		$class = null;
		$sequence = 255;
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_name = null;
		$a_admingroup_adminmenu = array();
		
		//form
		$column = array();
		$extra = null;
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				list($html, $js) = parent::$html->selectKit(['id'=>'level', 'name'=>'level', 'onchange'=>'changelevel()'], array(array('value'=>0, 'text'=>0), array('value'=>1, 'text'=>1)), $level);
				$html_level = $html;
				$js .= "
				function changelevel() {
					if (0 == $('#level').val()) {
						$('#up').prop('disabled', true).val('').trigger('chosen:updated');
						$('#class').prop('disabled', true).val('').trigger('chosen:updated');
					} else {
						$('#up').prop('disabled', false).trigger('chosen:updated');
						$('#class').prop('disabled', false).trigger('chosen:updated');
					}
				}
				$(function(){changelevel();});
				";
				parent::$html->set_js($js);
				
				$m_adminmenu = Model(M_CLASS)->where(array(array(array(array('level', '=', 0)), 'and')))->fetchAll();
				$a_up = array();
				foreach ($m_adminmenu as $v0) {
					$a_up[] = array(
							'value'=>$v0['adminmenu_id'],
							'text'=>$v0['name'],
					);
				}
				list($html, $js) = parent::$html->selectKit(['id'=>'up', 'name'=>'up'], $a_up, $up);
				$html_up = $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'add'));
				break;
	
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					
					$m_adminmenu = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
					$name = $m_adminmenu['name'];
					$level = $m_adminmenu['level'];
					$up = $m_adminmenu['up'];
					$class = $m_adminmenu['class'];
					$sequence = $m_adminmenu['sequence'];
					$inserttime = $m_adminmenu['inserttime'];
					$modifytime = $m_adminmenu['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_adminmenu['modifyadmin_id'])['name'];
					
					//admingroup
					$join = array();
					$join[] = array('left join', 'admingroup_adminmenu', 'using(adminmenu_id)');
					$m_adminmenu = Model(M_CLASS)->column(array('admingroup_adminmenu.admingroup_id', 'admingroup_adminmenu.act'))->join($join)->where(array(array(array(array(M_CLASS.'.'.M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetchAll();
					foreach ($m_adminmenu as $v1) {
						$a_admingroup_adminmenu[] = array('admingroup_id'=>$v1['admingroup_id'], 'act'=>$v1['act']);
					}
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				$html_level = $level;
				$html_up = parent::get_area_level_format_string(M_CLASS, $up);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'edit'));
				break;
		}
		
		$m_admingroup = Model('admingroup')->fetchAll();
		$admingroup = array();
		foreach ($m_admingroup as $k => $v) {
			$admingroup[$k]['name'] = 'admingroup_id';
			$admingroup[$k]['value'] = $v['admingroup_id'];
			$admingroup[$k]['text'] = $v['name'];
			$admingroup[$k]['checked'] = (array_multiple_search($a_admingroup_adminmenu, 'admingroup_id', $v['admingroup_id']))? true : false;
			
			//child checkboxtable
			$tmp2 = array();
			foreach (array('view'=>_('View'), 'add'=>_('Add'), 'edit'=>_('Edit'), 'delete'=>_('Delete')) as $k2 => $v2) {
				$tmp2[$k2]['name'] = 'admingroup_adminmenu_act['.$v['admingroup_id'].']';
				$tmp2[$k2]['value'] = $k2;
				$tmp2[$k2]['text'] = $v2;
				$tmp2[$k2]['checked'] = (array_multiple_search($a_admingroup_adminmenu, 'admingroup_id', $v['admingroup_id']) && array_multiple_search(array_multiple_search($a_admingroup_adminmenu, 'admingroup_id', $v['admingroup_id']), 'act', $k2))? true : false;
			}
			
			list($html, $js) = parent::$html->checkboxtable('100px', '35px', 4, $tmp2);
			$admingroup[$k]['extra'] = $html;
			parent::$html->set_js($js);
		}
		
		list($html, $js) = parent::$html->checkboxtable('520px', '110px', 2, $admingroup);
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('admingroup'),'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" maxlength="32" size="64"');
		$column[] = array('key'=>_('Name'),'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Level'),'value'=>$html_level);
		
		$column[] = array('key'=>_('Up'),'value'=>$html_up);
		
		$a_class = array();
		foreach (glob(PATH_ROOT.'module/admin/controller/*.php') as $v1) {
			$class_name = pathinfo($v1, PATHINFO_FILENAME);
			if (in_array($class_name, array('controller', 'excel', 'generate_qrcode', 'index', 'upload'))) continue;
			$a_class[] = array(
					'value'=>$class_name,
					'text'=>$class_name,
			);
		}
		$m_adminmenu = Model(M_CLASS)->where(array(array(array(array('class', '!=', '')), 'and')))->fetchAll();
		$a_class_disabled = array();
		foreach ($m_adminmenu as $v1) {
			if ($v1['class'] == $class) continue;
			$a_class_disabled[$v1['class']] = $v1['class'];
		}
		list($html, $js) = parent::$html->selectKit(['id'=>'class', 'name'=>'class'], $a_class, $class, $a_class_disabled);
		$column[] = array('key'=>_('Class'),'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="'.$sequence.'" min="0" max="255" required');
		$column[] = array('key'=>_('Sequence'),'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$inserttime);
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>$modifytime);
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name);
		
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
		
		list($html, $js) = parent::$html->form('id="form" action="" method="post" onsubmit="false"', $formcontent);
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
			Model('admingroup_adminmenu');
			Model()->beginTransaction();
			
			//adminmenu
			Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $_POST[M_CLASS.'_id'])), 'and')))->delete();
			
			//admingroup_adminmenu
			Model('admingroup_adminmenu')->where(array(array(array(array(M_CLASS.'_id', '=', $_POST[M_CLASS.'_id'])), 'and')))->delete();
				
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
		$sql = "SELECT ".M_CLASS.".".M_CLASS."_id, ".M_CLASS.".name, ".M_CLASS.".level, ".M_CLASS.".class, ".M_CLASS.".sequence, ".M_CLASS.".modifytime, ".M_CLASS.".modifyadmin_id
		FROM ".M_CLASS."
		$where ORDER BY $sidx $sord LIMIT $start, $limit";
		$fetchAll = $db->fetchAll($sql);
		$response = array();
		$response['page'] = $page;
		$response['total'] = $total_pages;
		$response['records'] = $count;
		foreach ($fetchAll as $k => &$v) {
			$v['name'] = '<a href="'.parent::url(M_CLASS, 'form', array('act'=>'edit', M_CLASS.'_id'=>$v[M_CLASS.'_id'])).'">'.implode(' > ', array_reverse($this->get_adminmenu_from_level_desc($v[M_CLASS.'_id']))).'</a>';
			
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
				'level',
				'class',
				'sequence',
				'modifytime',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		foreach ($fetchAll as &$v0) {
			$v0['name'] = implode(' > ', array_reverse($this->get_adminmenu_from_level_desc($v0[M_CLASS.'_id'])));
		}
		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
	
	function grid_edit() {
		/*
		if (isset($_REQUEST['oper'])) {
			$oper = $_REQUEST['oper'];
			switch ($oper) {
				case 'edit':
					$M_CLASS_id = $_REQUEST[M_CLASS.'_id'];
					$celname = $_REQUEST['celname'];
					$value = $_REQUEST['value'];
					
					Model(M_CLASS)->edit($M_CLASS_id, array($celname=>$value));
					break;
					
				default:
					break;
			}
		}
		die;
		*/
		if (!empty($_REQUEST['models'])) {
			foreach ($_REQUEST['models'] as $v1) {
				Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', (int)$v1[M_CLASS.'_id'])), 'and')))->edit(array('sequence'=>$v1['sequence'], 'modifyadmin_id'=>adminModel::getSession()['admin_id']));
			}
			json_encode_return(1, 'Edit success.');
		}
		die;
	}
	
	function get_adminmenu_from_level_desc($adminmenu_id) {
		$m_adminmenu = Model(M_CLASS)->where(array(array(array(array('adminmenu_id', '=', (int)$adminmenu_id)), 'and')))->fetch();
		
		$return = array();
		$return[] = $m_adminmenu['name'];
		
		if ($m_adminmenu['up'] > 0) {
			$return = array_merge($return, $this->get_adminmenu_from_level_desc($m_adminmenu['up']));
		}
		
		return $return;
	}
}