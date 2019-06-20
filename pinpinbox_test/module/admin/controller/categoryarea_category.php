<?php
class categoryarea_categoryController extends backstageController {
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
			$categoryarea_id = $_POST['categoryarea_id'];
			$category_id = $_POST['category_id'];
			$act = $_POST['act'];
			$sequence = $_POST['sequence'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->where(array(array(array(array('categoryarea_id', '=', $categoryarea_id), array('category_id', '=', $category_id)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists.'));
					}
					
					//form
					$add = array(
							'categoryarea_id'=>$categoryarea_id,
							'category_id'=>$category_id,
							'act'=>$act,
							'sequence'=>$sequence,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->add($add);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
		
				//修改
				case 'edit':
					//form
					$edit = array(
							'act'=>$act,
							'sequence'=>$sequence,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array('categoryarea_id', '=', $categoryarea_id), array('category_id', '=', $category_id)), 'and')))->edit($edit);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$categoryarea_id = null;
		$category_id = null;
		$act = 'close';
		$sequence = 255;
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
				//categoryarea
				$m_categoryarea = Model('categoryarea')->fetchAll();
				$tmp0 = array();
				foreach ($m_categoryarea as $v0) {
					$tmp0[] = array(
							'value'=>$v0['categoryarea_id'],
							'text'=>parent::get_area_level_format_string('categoryarea', $v0['categoryarea_id']),
					);
				}
				list($html, $js) = parent::$html->selectKit(['id'=>'categoryarea_id', 'name'=>'categoryarea_id'], $tmp0);
				$html_categoryarea = $html;
				parent::$html->set_js($js);
				
				//category
				$m_category = Model('category')->fetchAll();
				$tmp0 = array();
				foreach ($m_category as $v0) {
					$tmp0[] = array(
							'value'=>$v0['category_id'],
							'text'=>$v0['name'].' - '.$v0['category_id'],
					);
				}
				list($html, $js) = parent::$html->selectKit(['id'=>'category_id', 'name'=>'category_id'], $tmp0);
				$html_category = $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'add'));
				break;
		
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$categoryarea_id = $_GET['categoryarea_id'];
					$category_id = $_GET['category_id'];
					
					$m_categoryarea_category = Model(M_CLASS)->where(array(array(array(array('categoryarea_id', '=', $categoryarea_id), array('category_id', '=', $category_id)), 'and')))->fetch();
					
					//form
					$act = $m_categoryarea_category['act'];
					$sequence = $m_categoryarea_category['sequence'];
					$inserttime = $m_categoryarea_category['inserttime'];
					$modifytime = $m_categoryarea_category['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_categoryarea_category['modifyadmin_id'])['name'];

					//categoryarea
					$html_categoryarea = parent::get_area_level_format_string('categoryarea', $categoryarea_id);
					
					//category
					$m_category = Model('category')->where(array(array(array(array('category_id', '=', $category_id)), 'and')))->fetch();
					$html_category = $m_category['name'];
					
					list($html0, $js0) = parent::$html->hidden('id="categoryarea_id" name="categoryarea_id" value="'.$categoryarea_id.'"');
					list($html1, $js1) = parent::$html->hidden('id="category_id" name="category_id" value="'.$category_id.'"');
					$extra .= $html0.$html1;
					parent::$html->set_js($js0.$js1);
				}
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('categoryarea'), 'value'=>$html_categoryarea);
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('category'), 'value'=>$html_category);
		
		$a_act = array();
		foreach (json_decode(Core::settings('CATEGORYAREA_CATEGORY_ACT'), true) as $k0 => $v0) {
			$a_act[] = array(
					'name'=>'act',
					'value'=>$k0,
					'text'=>$v0,
			);
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
		$column[] = array('key'=>_('Act'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="'.$sequence.'" min="0" max="255" required');
		$column[] = array('key'=>_('Sequence'),'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$inserttime);
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>$modifytime);
		
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
		
		list($html, $js) = parent::$html->form('id="form" action="" method="post" onsubmit="false"', $formcontent);
		parent::$data['form'] = $html;
		parent::$html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function delete() {
		if (!empty($_POST)) {
			Model(M_CLASS)->where(array(array(array(array('categoryarea_id', '=', $_POST['categoryarea_id']), array('category_id', '=', $_POST['category_id'])), 'and')))->delete();
			json_encode_return(1, _('Success'));
		}
		die;
	}
	
	function json() {
		$response = array();
		
		//column
		$column = array(
				'categoryarea_id',
				'category_id',
				'act',
				'sequence',
				'modifytime',
				'modifyadmin_id',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		foreach ($fetchAll as &$v0) {
			$v0['categoryareaX'] = parent::get_grid_display('categoryarea', $v0['categoryarea_id']);
			$v0['categoryX'] = parent::get_grid_display('category', $v0['category_id']);
			$v0['modifyadmin_id'] = adminModel::getOne($v0['modifyadmin_id'])['name'];
		}
		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
	
	function grid_edit() {
		if (!empty($_REQUEST['models'])) {
			foreach ($_REQUEST['models'] as $v0) {
				Model(M_CLASS)->where(array(array(array(array('categoryarea_id', '=', (int)$v0['categoryarea_id']), array('category_id', '=', (int)$v0['category_id'])), 'and')))->edit(array('sequence'=>$v0['sequence'], 'modifyadmin_id'=>adminModel::getSession()['admin_id']));
			}
			json_encode_return(1, 'Edit success.');
		}
		die;
	}
}