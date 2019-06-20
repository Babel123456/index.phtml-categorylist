<?php
class specialController extends backstageController {
	function __construct() {}
	
	function index() {
		list($html0, $js0) = parent::$html->grid();
		list($html1, $js1) = parent::$html->browseKit(array('selector'=>'.grid-img'));
		parent::$data['index'] = $html0.$html1;
		parent::$html->set_js($js0.$js1);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function form() {
		if (is_ajax()) {
			//form
			$event_id = $_POST['event_id'];
			$name = $_POST['name'];
			$description = $_POST['description'];
			$remark = $_POST['remark'];
			$info_required = $_POST['info_required'];
			$act = $_POST['act'];

			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->where(array(array(array(array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					//special
					$add = array(
							'event_id'=>$event_id,
							'name'=>$name,
							'description'=>$description,
							'remark'=>$remark,
							'info_required'=>$info_required,
							'act'=>$act,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					$M_CLASS_id = Model(M_CLASS)->add($add);

					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
				
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					if (Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '!=', $M_CLASS_id), array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					//form
					$edit = array(
							'event_id'=>$event_id,
							'name'=>$name,
							'description'=>$description,
							'remark'=>$remark,
							'info_required'=>$info_required,
							'act'=>$act,
							'modifytime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->edit($edit);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-from
		$event_id = null;
		$name = null;
		$description = null;
		$remark = null;
		$info_required = 1;
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
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					
					$m_special = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
					
					//form
					$event_id = $m_special['event_id'];
					$name = $m_special['name'];
					$description = $m_special['description'];
					$remark = $m_special['remark'];
					$info_required = $m_special['info_required'];
					$act = $m_special['act'];
					$inserttime = $m_special['inserttime'];
					$modifytime = $m_special['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_special['modifyadmin_id'])['name'];
					$viewed = Model('eventstatistics')->column(array('viewed'))->where(array(array(array(array('event_id', '=', $M_CLASS_id)), 'and')))->fetchColumn();
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		//只能選擇此贊助原本綁定的event_id + 未被其他贊助綁定的event_id
		$m_special_selected = Model('special')->column(['event_id'])->where([[[['event_id', '!=', $event_id]], 'and']])->fetchAll();
		$a_special_selected = array();
		foreach($m_special_selected as $k0 => $v0) {$a_special_selected[] = $v0['event_id'];}
		
		list($html, $js) = parent::$html->selectKit(['id'=>'event_id', 'name'=>'event_id'], parent::get_form_select('event'), $event_id, $a_special_selected);
		$column[] = ['key'=>_('Event'), 'value'=>$html, 'key_remark'=>'僅能選擇未綁定的活動'];
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" size="64" maxlength="64" required');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->ckeditor('id="description" name="description" required', $description);
		$column[] = array('key'=>_('Description'), 'value'=>$html);
		parent::$html->set_js($js);
	
		list($html, $js) = parent::$html->ckeditor('id="remark" name="remark" required', $remark);
		$column[] = array('key'=>_('Remark'), 'value'=>$html);
		parent::$html->set_js($js);

		$a_info_required_ = [
			['name' => 'info_required' , 'value' => 1, 'text' => '是'],
			['name' => 'info_required' , 'value' => 0, 'text' => '否'],
		];
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_info_required_, $info_required);
		$column[] = array('key'=>_('info_required'), 'value'=>$html);
		parent::$html->set_js($js);

		$a_act = array();
		foreach (json_decode(Core::settings('EVENT_ACT'), true) as $k0 => $v0) {
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
		die;
	}
	
	function json() {
		$response = array();

		//column
		$column = array(
				M_CLASS.'_id',
				'event_id',
				'name',
				'description',
				'remark',
				'act',
				'inserttime',
				'modifytime',
				'modifyadmin_id',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		
		foreach ($fetchAll as &$v0) {
			if($v0['event_id'] != 0) {
				$v0['event_name'] = Model('event')->column(['name'])->where([[[['event_id', '=', $v0['event_id']]], 'and']])->fetchColumn();
			}
		}
		
		$response['data'] = $fetchAll;
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
}