<?php
class categoryController extends backstageController {
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
			$name = $_POST['name'];
			$sequence = $_POST['sequence'];
			$act = $_POST['act'];
			
			/**
			 * 2015-05-13 Lion:
			 *     新增、修改時不判斷欄位 name 是否重複
			 */
			switch ($_GET['act']) {
				//新增
				case 'add':
					//form
					$add = array(
							'name'=>$name,
							'sequence'=>$sequence,
							'act'=>$act,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->add($add);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
						
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					//form
					$edit = array(
							'name'=>$name,
							'sequence'=>$sequence,
							'act'=>$act,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->edit($edit);
					
					/**
					 *  160713 - act 開關應該與 table::categoryarea_category 連動
					 * 			 前台在進行 categoryarea<->category比對時才不會相異
					 */
					$edit_2 = [
						'act' => $act,
					];
					Model('categoryarea_category')->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->edit($edit_2);

					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$name = null;
		$sequence = 255;
		$act = 'close';
		$inserttime = null;
		$modifytime = null;
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
				$M_CLASS_id = $_GET[M_CLASS.'_id'];
				
				$m_category = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
				
				//form
				$name = $m_category['name'];
				$sequence = $m_category['sequence'];
				$act = $m_category['act'];
				$inserttime = $m_category['inserttime'];
				$modifytime = $m_category['modifytime'];
				$modifyadmin_name = adminModel::getOne($m_category['modifyadmin_id'])['name'];
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" size="32" maxlength="32" required');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="'.$sequence.'" min="0" max="255" required');
		$column[] = array('key'=>_('Sequence'),'value'=>$html);
		parent::$html->set_js($js);
		
		$a_act = array();
		foreach (json_decode(Core::settings('CATEGORY_ACT'), true) as $k0 => $v0) {
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
		if (!empty($_POST)) {
			Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $_POST[M_CLASS.'_id'])), 'and')))->delete();
			json_encode_return(1, _('Success'));
		}
		die;
	}
	
	function json() {
		$response = array();
		
		//column
		$column = array(
				M_CLASS.'_id',
				'name',
				'sequence',
				'act',
				'modifytime',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$response['data'] = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
	
	function grid_edit() {
		if (!empty($_REQUEST['models'])) {
			foreach ($_REQUEST['models'] as $v1) {
				Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', (int)$v1[M_CLASS.'_id'])), 'and')))->edit(array('sequence'=>$v1['sequence'], 'modifyadmin_id'=>adminModel::getSession()['admin_id']));
			}
			json_encode_return(1, 'Edit success.');
		}
		die;
	}
}