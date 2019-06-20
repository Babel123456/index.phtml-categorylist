<?php
class orderController extends backstageController {
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
		
		//init value-form
		
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
					
					$m_order = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
				}
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('cashflow'), 'value'=>parent::get_grid_display('cashflow', $m_order['cashflow_id']));
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('user'), 'value'=>parent::get_grid_display('user', $m_order['user_id']));
		
		$column[] = array('key'=>_('Platform'), 'value'=>$m_order['platform']);
		
		$column[] = array('key'=>_('Assets'), 'value'=>$m_order['assets']);
		
		$column[] = array('key'=>_('Assets Info'), 'value'=>parent::grid_json_decode($m_order['assets_info']));
		
		$column[] = array('key'=>_('Total'), 'value'=>$m_order['total']);
		
		$column[] = array('key'=>_('Currency'), 'value'=>$m_order['currency']);
		
		$column[] = array('key'=>_('State'), 'value'=>$m_order['state']);
		
		$column[] = array('key'=>_('Remote IP'), 'value'=>$m_order['remote_ip']);
		
		$column[] = array('key'=>_('Callback'), 'value'=>$m_order['callback']);
		
		$column[] = array('key'=>_('Request'), 'value'=>parent::grid_json_decode($m_order['request']));
		
		$column[] = array('key'=>_('Return'), 'value'=>parent::grid_json_decode($m_order['return']));
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$m_order['inserttime']);
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>$m_order['modifytime']);
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>adminModel::getOne($m_order['modifyadmin_id'])['name']);
		
		list($html, $js) = parent::$html->back('value="'._('Back').'"');
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
						'cashflow_id',
						'user_id',
						'platform',
						'assets',
						'assets_info',
						'total',
						'currency',
						'state',
						'modifytime',
				);
				
				list($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
				foreach ($fetchAll as &$v0) {
					$v0['assets_info'] = parent::grid_json_decode($v0['assets_info']);
					$v0['cashflowX'] = parent::get_grid_display('cashflow', $v0['cashflow_id']);
					$v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
				}
				$response['data'] = $fetchAll;
				
				//total
				$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
				break;
		}
		
		die(json_encode($response));
	}
}