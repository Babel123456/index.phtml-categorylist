<?php
class incomeController extends backstageController {
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
			$state = $_POST['state'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					break;
				
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					$edit = array(
							'state'=>$state,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->edit($edit);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$state = 'pretreat';
		
		//form
		$column = array();
		$extra = null;
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				break;
				
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					
					$m_income = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
					
					//user
					$html_user = parent::get_grid_display('user', $m_income['user_id']);
					
					//payment
					$html_payment = parent::get_grid_display('payment', $m_income['payment_id']);
					
					$total = $m_income['total'];
					$currency = $m_income['currency'];
					$remittance = $m_income['remittance'];
					$remittance_info = parent::grid_json_decode($m_income['remittance_info']);
					$state = $m_income['state'];
					$inserttime = $m_income['inserttime'];
					$modifytime = $m_income['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_income['modifyadmin_id'])['name'];
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('user'), 'value'=>$html_user);
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('payment'), 'value'=>$html_payment);
		
		$column[] = array('key'=>_('Total'), 'value'=>$total);
		
		$column[] = array('key'=>_('Currency'), 'value'=>$currency);
		
		$column[] = array('key'=>_('Remittance'), 'value'=>$remittance);
		
		$column[] = array('key'=>_('Remittance Info'), 'value'=>$remittance_info);
		
		$a_state = array();
		foreach (json_decode(Core::settings('INCOME_STATE'), true) as $k0 => $v0) {
			$a_state[] = array(
					'name'=>'state',
					'value'=>$k0,
					'text'=>$v0,
			);
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_state, $state);
		$column[] = array('key'=>_('State'), 'value'=>$html);
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
		
		//settlement
		$column = array();
		$extra = null;
		
		list($html, $js) = parent::$html->grid();
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('settlement'), 'value'=>$html);
		parent::$html->set_js($js);
		parent::$data[M_CLASS.'_id'] = empty($M_CLASS_id)? '[]' : $M_CLASS_id;
		
		list($html, $js) = parent::$html->table('class="table"', $column, $extra);
		$a_tabs[1] = array('href'=>'#tabs-1', 'name'=>parent::get_adminmenu_name_by_class('settlement'), 'value'=>$html);
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
		
		$case = isset($_POST['case'])? $_POST['case'] : null;
		
		switch ($case) {
			default:
				//column
				$column = array(
						M_CLASS.'_id',
						'user_id',
						'payment_id',
						'total',
						'currency',
						'remittance',
						'remittance_info',
						'state',
						'modifytime',
				);
				
				list($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
				foreach ($fetchAll as &$v0) {
					$v0['remittance_info'] = parent::grid_json_decode($v0['remittance_info']);
					$v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
					$v0['paymentX'] = parent::get_grid_display('payment', $v0['payment_id']);
					$v0['settlementX'] = Model('settlement')->column(array('count(1)'))->where(array(array(array(array(M_CLASS.'_id', '=', $v0[M_CLASS.'_id'])), 'and')))->fetchColumn();
				}
				$response['data'] = $fetchAll;
				
				//total
				$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
				break;
				
			case 'settlement':
				//column
				$column = array(
						'settlement_id',
						'user_id',
						'income_id',
						'point_album',
						'point_template',
						'starttime',
						'endtime',
						'state',
						'modifytime',
				);
				
				list($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$fetchAll = Model('settlement')->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
				foreach ($fetchAll as &$v0) {
					$v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
					$v0['incomeX'] = parent::get_grid_display('income', $v0['income_id']);
					$v0['exchangeX'] = (new exchangeModel)->column(array('count(1)'))->where(array(array(array(array('settlement_id', '=', $v0['settlement_id'])), 'and')))->fetchColumn();
				}
				$response['data'] = $fetchAll;
				
				//total
				$response['total'] = Model('settlement')->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
				break;
		}
		
		die(json_encode($response));
	}
}