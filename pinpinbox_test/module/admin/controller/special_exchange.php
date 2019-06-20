<?php
class special_exchangeController extends backstageController {
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
			$state = $_POST['state'];	
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					break;
				
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					//form
					$edit = array(
							'state'=>$state,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->edit($edit);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-from
		$user_id = null;
		$event_id = null;
		$special_id = null;
		$special_award_id = null;
		$award_exchange_before = null;
		$receipt = null;
		$state = null;
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
					
					$m_special_exchange = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
					
					//form
					$user_id = $m_special_exchange['user_id'];
					$event_id = $m_special_exchange['event_id'];
					$special_id = $m_special_exchange['special_id'];
					$special_award_id = $m_special_exchange['special_award_id'];
					$award_exchange_before = $m_special_exchange['award_exchange_before'];
					$receipt = ($m_special_exchange['receipt']);
					$state = $m_special_exchange['state'];
					$inserttime = $m_special_exchange['inserttime'];
					$modifytime = $m_special_exchange['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_special_exchange['modifyadmin_id'])['name'];
				}
				
				
				$m_user = Model('user')->column(['name'])->where([[[['user_id', '=', $user_id]], 'and']])->fetchColumn();
				$user = implode('<br>', ['id : '.$user_id, 'name : '.$m_user]);
				
				$m_event = Model('event')->column(['name'])->where([[[['event_id', '=', $event_id]], 'and']])->fetchColumn();
				$event = implode('<br>', ['id : '.$event_id, 'name : '.$m_event]);
				
				$m_special = Model('special')->column(['name'])->where([[[['special_id', '=', $special_id]], 'and']])->fetchColumn();
				$special = implode('<br>', ['id : '.$special_id, 'name : '.$m_special]);
				
				$m_special_award = Model('special_award')->column(['name'])->where([[[['special_award_id', '=', $special_award_id]], 'and']])->fetchColumn();
				$special_award = implode('<br>', ['id : '.$special_award_id, 'name : '.$m_special_award]);
				
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				parent::$data['exchange_id'] = $M_CLASS_id;
				break;
		}
		
		$m_special_exchange_enum = Model('special_exchange')->fetchEnum('state');
		foreach($m_special_exchange_enum as $v0) {$a_state[] = ['text'=>$v0, 'value'=>$v0];}
		
		$column[] = array('key'=>_('User'), 'value'=>$user);
		
		$column[] = array('key'=>_('Event'), 'value'=>$event);
		
		$column[] = array('key'=>_('Special_id'), 'value'=>$special);
		
		$column[] = array('key'=>_('Special_award'), 'value'=>$special_award);
		
		$column[] = array('key'=>_('Award exchange before'), 'value'=>$award_exchange_before);
		
		$column[] = array('key'=>_('Receipt'), 'value'=>parent::grid_json_decode($receipt));

		list($html, $js) = parent::$html->selectKit(['id'=>'state', 'name'=>'state'], $a_state, $state);
		$column[] = ['key'=>_('State'), 'value'=>$html];
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
				'user_id',
				'event_id',
				'special_id',
				'special_award_id',
				'award_exchange_before',
				'receipt',
				'state',
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
			$v0['award_name'] = Model('special_award')->column(['name'])->where([[[['special_award_id', '=', $v0['special_award_id']]], 'and']])->fetchColumn();
			$v0['user_name'] = Model('user')->column(['name'])->where([[[['user_id', '=', $v0['user_id']]], 'and']])->fetchColumn();
			$v0['special_name'] = Model('special')->column(['name'])->where([[[['special_id', '=', $v0['special_id']]], 'and']])->fetchColumn();
			
			foreach(json_decode($v0['receipt'], true) as $k1 => $v1) {
				$v0['receipt_'.$k1] = $v1;
			}
		}
		
		$response['data'] = $fetchAll;
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
}