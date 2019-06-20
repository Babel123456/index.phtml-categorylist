<?php
class newsareaController extends backstageController {
	function __construct() {}
	
	function index() {
		list($jqgrid_html, $jqgrid_js) = parent::$html->jqgrid();
		parent::$data['index'] = $jqgrid_html;
		parent::$html->set_js($jqgrid_js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function form() {
		if (is_ajax()) {
			//form
			$name = $_POST['name'];
			$act = $_POST['act'];
			$sequence = $_POST['sequence'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					$level = $_POST['level'];
					$up = $_POST['up'];
					
					if (Core::model(M_CLASS)->get(sql_select_encode(null, null, array(array('name', '=', $name))))) {
						json_encode_return(0, _('Data already exists by [Name]'));
					}
					
					//form
					$param = array();
					$param['name'] = $name;
					$param['level'] = $level;
					$param['up'] = $up;
					$param['act'] = $act;
					$param['sequence'] = $sequence;
					
					Core::model(M_CLASS)->add($param);
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
		
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					if (Core::model(M_CLASS)->get(sql_select_encode(null, null, array(array(M_CLASS.'_id', '!=', $M_CLASS_id), array('name', '=', $name))))) {
						json_encode_return(0, _('Data already exists by [Name]'));
					}
					
					//form
					$param = array();
					$param['name'] = $name;
					$param['act'] = $act;
					$param['sequence'] = $sequence;
					
					Core::model(M_CLASS)->edit($M_CLASS_id, $param);
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$name = null;
		$level = 1;
		$up = null;
		$act = 'preview';
		$sequence = 65535;
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_name = null;
		
		//table-form
		$column = array();
		$extra = null;
		
		//tabs
		$a_tabs = array();
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				//不能將資料在修改時更動在自身層級路徑中的層級(不論向上向下), 否則會造成無限迴圈, 因此只能在新增時操作, 修改時則不行; 另一考量是統計方面, area 在建立後就應該不能搬移
				list($html, $js) = parent::$html->select2('id="level" name="level" onchange="changelevel()"', array(1=>1, 2=>2));
				$html_level = $html;
				$js .= "
				function changelevel() {
					var level = $('#level'), up = $('#up');
					up.prop('disabled', true).select2('val', '');
					if (parseInt(level.val(), 10) > 1) {
						$.post('".parent::url(M_CLASS, 'get_area_up')."', {
							'level': parseInt(level.val(), 10) - parseInt(1, 10)
						}, function(response){
							response = $.parseJSON(response);
							var result = response.result, message = response.message, data = response.data;
							if (result) {
								up.empty().append('<option value=\"\"></option>');
		   						$.each(data, function(k1, v1){
									up.append('<option value=\"'+ k1 +'\">'+ v1 +'</option>');
								});
								up.prop('disabled', false);
							} else {
								alert(message);
							}
						});
					}
				}
				$(function(){changelevel();});
				";
				parent::$html->set_js($js);
				
				list($html, $js) = parent::$html->select2('id="up" name="up"', $this->get_area_up(array(array('level', '=', $level - 1))));
				$html_up = $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'add'));
				break;
		
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					
					$m_newsarea = Core::model(M_CLASS)->get(sql_select_encode(null, null, array(array(M_CLASS.'_id', '=', $M_CLASS_id))));
					
					//form
					$name = $m_newsarea['name'];
					$level = $m_newsarea['level'];
					$up = $m_newsarea['up'];
					$act = $m_newsarea['act'];
					$sequence = $m_newsarea['sequence'];
					$inserttime = $m_newsarea['inserttime'];
					$modifytime = $m_newsarea['modifytime'];
					$modifyadmin_name = parent::get_admin_name_by_admin_id($m_newsarea['modifyadmin_id']);
				}
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				$html_level = $level;
				$html_up = parent::get_area_level_format_string(M_CLASS, $up);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" maxlength="32" size="64"');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Level'),'value'=>$html_level);
		
		$column[] = array('key'=>_('Up'),'value'=>$html_up);
		
		$a_act = array();
		foreach (Core::$_config['CONFIG']['NEWSAREA_ACT'] as $k1 => $v1) {
			$tmp1 = array();
			$tmp1['name'] = 'act';
			$tmp1['value'] = $k1;
			$tmp1['text'] = $v1;
			$a_act[] = $tmp1;
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
		$column[] = array('key'=>_('Act'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="'.$sequence.'" min="0" max="65535" required');
		$column[] = array('key'=>_('Sequence'),'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$inserttime);
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>$modifytime);
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name);
		
		list($html, $js) = parent::$html->jqgrid();
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('newsarea_news'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html1, $js1) = parent::$html->submit('value="'._('Submit').'"');
		list($html2, $js2) = parent::$html->back('value="'._('Back').'"');
		$column[] = array('key'=>'&nbsp;', 'value'=>$html1.'&emsp;'.$html2);
		parent::$html->set_js($js1.$js2);
		
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
		if (!empty($_POST)) {
			$M_CLASS_id = $_POST[M_CLASS.'_id'];
			Core::model(M_CLASS)->delete($M_CLASS_id);
			json_encode_return(1, _('Success'));
		}
		die;
	}
	
	function json() {
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
		$sql = "SELECT ".M_CLASS.".".M_CLASS."_id, ".M_CLASS.".name, ".M_CLASS.".level, ".M_CLASS.".act, ".M_CLASS.".sequence, ".M_CLASS.".modifytime, ".M_CLASS.".modifyadmin_id
		FROM ".M_CLASS."
		$where ORDER BY $sidx $sord LIMIT $start, $limit";
		$fetchAll = $db->fetchAll($sql);
		$response = array();
		$response['page'] = $page;
		$response['total'] = $total_pages;
		$response['records'] = $count;
		foreach ($fetchAll as $k => &$v) {
			$v['name'] = '<a href="'.parent::url(M_CLASS, 'form', array('act'=>'edit', M_CLASS.'_id'=>$v[M_CLASS.'_id'])).'">'.parent::get_area_level_format_string(M_CLASS, $v[M_CLASS.'_id']).'</a>';
			
			$v['modifyadmin_id'] = parent::get_admin_name_by_admin_id($v['modifyadmin_id']);
			
			$tmp = array_values($v);
			$v = array();
			$v['cell'] = $tmp;
		}
		$response['rows'] = $fetchAll;
		
		die(json_encode($response));
	}
	
	function get_area_up($param=array()) {
		if (is_ajax()) {
			$param = array();
			if (isset($_POST['level'])) $param[] = array('level', '=', (int)$_POST['level']);
		}
		$a_up = array();
		if (!empty($param)) {
			$m_newsarea = Core::model(M_CLASS)->get(sql_select_encode(null, null, $param), 'fetchAll');
			foreach ($m_newsarea as $v1) {
				$a_up[$v1[M_CLASS.'_id']] = parent::get_area_level_format_string(M_CLASS, $v1[M_CLASS.'_id']);
			}
		}
		if (is_ajax()) {
			json_encode_return(1, _('Success'), null, $a_up);
		} else {
			return $a_up;
		}
	}
	
	function jqgrid_edit() {
		if (isset($_REQUEST['oper'])) {
			$oper = $_REQUEST['oper'];
			switch ($oper) {
				case 'edit':
					$M_CLASS_id = $_REQUEST[M_CLASS.'_id'];
					$celname = $_REQUEST['celname'];
					$value = $_REQUEST['value'];
	
					Core::model(M_CLASS)->edit($M_CLASS_id, array($celname=>$value));
					break;
	
				default:
					break;
			}
		}
		die;
	}
}