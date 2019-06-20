<?php
class recruitController extends backstageController {
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
		
		//form
		$column = array();
		$extra = null;
		
		//tabs
		$a_tabs = array();
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				break;
		
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					
					$m_recruit = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
					
					//recruitintent
					$html_recruitintent = Model('recruitintent')->where(array(array(array(array('recruitintent_id', '=', $m_recruit['recruitintent_id'])), 'and')))->fetch()['name'];
					
					$name = $m_recruit['name'];
					$telephone = $m_recruit['telephone'];
					$email = $m_recruit['email'];
					$country = $m_recruit['country'];
					$zipcode = $m_recruit['zipcode'];
					$address = $m_recruit['address'];
					$vatnumber = $m_recruit['vatnumber'];
					$website = $m_recruit['website'];
					$intro = nl2br(htmlspecialchars($m_recruit['intro']));
					$proposal = nl2br(htmlspecialchars($m_recruit['proposal']));
					
					$tmp0 = array();
					foreach (json_decode($m_recruit['contact'], true) as $k0 => $v0) {
						$tmp0[] = $k0.': '.nl2br(htmlspecialchars($v0));
					}
					$contact = implode('<br>', $tmp0);
					
					$state = $m_recruit['state'];
					$inserttime = $m_recruit['inserttime'];
					$modifytime = $m_recruit['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_recruit['modifyadmin_id'])['name'];
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('recruitintent'), 'value'=>$html_recruitintent);
		
		$column[] = array('key'=>_('Name'), 'value'=>$name);
		
		$column[] = array('key'=>_('Telephone'), 'value'=>$telephone);
		
		$column[] = array('key'=>_('Email'), 'value'=>$email);
		
		$column[] = array('key'=>_('Country'), 'value'=>$country);
		
		$column[] = array('key'=>_('Zip Code'), 'value'=>$zipcode);
		
		$column[] = array('key'=>_('Address'), 'value'=>$address);
		
		$column[] = array('key'=>_('VAT Number'), 'value'=>$vatnumber);
		
		$column[] = array('key'=>_('Website'), 'value'=>$website);
		
		$column[] = array('key'=>_('Intro'), 'value'=>$intro);
		
		$column[] = array('key'=>_('Proposal'), 'value'=>$proposal);
		
		$column[] = array('key'=>_('Contact'), 'value'=>$contact);
		
		$a_state = array();
		foreach (json_decode(Core::settings('RECRUIT_STATE'), true) as $k0 => $v0) {
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
		die;
	}
	
	function json() {
		$response = array();
		
		//column
		$column = array(
				M_CLASS.'_id',
				'recruitintent_id',
				'name',
				'telephone',
				'email',
				'country',
				'zipcode',
				'address',
				'vatnumber',
				'website',
				'intro',
				'proposal',
				'contact',
				'state',
				'modifytime',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		foreach ($fetchAll as &$v0) {
			$v0['recruitintentX'] = parent::get_grid_display('recruitintent', $v0['recruitintent_id']);
			$v0['intro'] = nl2br(htmlspecialchars($v0['intro']));
			$v0['proposal'] = nl2br(htmlspecialchars($v0['proposal']));
			
			$tmp0 = array();
			foreach (json_decode($v0['contact'], true) as $k1 => $v1) {
				$tmp0[] = $k1.': '.nl2br(htmlspecialchars($v1));
			}
			$v0['contact'] = implode("<br>", $tmp0);
		}
		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
}