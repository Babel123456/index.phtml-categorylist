<?php
class smsController extends backstageController {
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
			$M_CLASS_id = $_POST[M_CLASS.'_id'];
			$name = $_POST['name'];
			$a_customize = $_POST['customize'];
			$act = $_POST['act'];
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'ID');
					}
					
					$add = array(
							M_CLASS.'_id'=>$M_CLASS_id,
							'name'=>$name,
							'act'=>$act,
							'customize'=>json_encode($a_customize),
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->add($add);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
				
				//修改
				case 'edit':
					$edit = array(
							'name'=>$name,
							'act'=>$act,
							'customize'=>json_encode($a_customize),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->edit($edit);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$M_CLASS_id = null;
		$name = null;
		$a_customize = array();
		$act = 'close';
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
				list($html, $js) = parent::$html->text('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'" size="32" maxlength="32" required');
				$html_id = $html;
				$js_id = $js;
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'add'));
				break;
				
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					
					$m_sms = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
					$name = $m_sms['name'];
					$a_customize = json_decode($m_sms['customize'], true);
					$act = $m_sms['act'];
					$inserttime = $m_sms['inserttime'];
					$modifytime = $m_sms['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_sms['modifyadmin_id'])['name'];
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"', $M_CLASS_id);
				$html_id = $html;
				$js_id = $js;
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		$column[] = array('key'=>_('ID'), 'value'=>$html_id);
		parent::$html->set_js($js_id);
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" size="32" maxlength="32" required');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->dynamictable('keyvalueremark', 'name="customize[]"', $a_customize);
		$column[] = array('key'=>_('Customize'), 'value'=>$html, 'key_remark'=>'設定用，提交後測試一下比較保險');
		parent::$html->set_js($js);
		
		$a_act = array();
		foreach (json_decode(Core::settings('SMS_ACT'), true) as $k0 => $v0) {
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
		
		$case = isset($_POST['case'])? $_POST['case'] : null;
		
		switch ($case) {
			default:
				//column
				$column = array(
						M_CLASS.'_id',
						'name',
						'act',
						'modifytime',
				);
				
				list($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$response['data'] = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
				
				//total
				$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
				break;
		}
		
		die(json_encode($response));
	}
}