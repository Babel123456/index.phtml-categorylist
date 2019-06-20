<?php
class companyController extends backstageController {
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
			$account = $_POST['account'];
			$password = $_POST['password'];
			$repassword = $_POST['repassword'];
			$name = $_POST['name'];
			$telphone = $_POST['telphone'];
			$address = $_POST['address'];
			$vatnumber =  $_POST['vatnumber'];
			$title = $_POST['title'];
			$description = $_POST['description'];
			$image = $_POST['image'];
			$sequence = $_POST['sequence'];
			$act = $_POST['act'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->where(array(array(array(array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					if (Model(M_CLASS)->where(array(array(array(array('account', '=', $account)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Account');
					}
					
					//先替新廠商設定密碼
					if(empty($password) || empty($repassword)) {
						json_encode_return(0, _('Please enter password.'));
					}elseif($password != $repassword){
						json_encode_return(0, _('The password that you entered twice do not match.'));
					}
					
					//company
					$add = array(
							'account'=>$account,
							'password'=>$password,
							'name'=>$name,
							'telphone'=>$telphone,
							'address'=>$address,
							'vatnumber'=>$vatnumber,
							'title'=>$title,
							'description'=>$description,
							'image'=>$image,
							'sequence'=>$sequence,
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
					
					if (Model(M_CLASS)->where(array(array(array(array('account', '=', $account), array('company_id', '!=', $M_CLASS_id)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Account');
					}

					if( empty($password) && empty($repassword) ) {
						$password = Model(M_CLASS)->column(['password'])->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetchColumn();
					}elseif($password != $repassword){
						json_encode_return(0, _('The password that you entered twice do not match.'));	
					}

					$edit = array(
							'account'=>$account,
							'password'=>$password,
							'name'=>$name,
							'telphone'=>$telphone,
							'address'=>$address,
							'vatnumber'=>$vatnumber,
							'title'=>$title,
							'description'=>$description,
							'image'=>$image,
							'sequence'=>$sequence,
							'act'=>$act,
							'inserttime'=>inserttime(),
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
		
		$account = null;
		$password = null;
		$name = null;
		$telphone = null;
		$address = null;
		$vatnumber = null;
		$title = null;
		$description = null;
		$image = null;
		$act = 'close';
		$sequence = null;
		$lastloginip = null;
		$lastlogintime = null;
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_id = null;
		
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

					$m_company = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();

					//form
					$company_id = $m_company['company_id'];
					$account = $m_company['account'];
					$password = $m_company['password'];
					$name = $m_company['name'];
					$telphone = $m_company['telphone'];
					$address = $m_company['address'];
					$vatnumber = $m_company['vatnumber'];
					$title = $m_company['title'];
					$description = $m_company['description'];
					$image = $m_company['image'];
					$sequence = $m_company['sequence'];
					$act = $m_company['act'];
					$lastloginip = $m_company['lastloginip'];
					$lastlogintime = $m_company['lastlogintime'];
					$inserttime = $m_company['inserttime'];
					$modifytime = $m_company['modifytime'];
					$modifyadmin_id = $m_company['modifyadmin_id'];
				}
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}

		list($html, $js) = parent::$html->text('id="account" name="account" value="'.$account.'" size="64" maxlength="64" require' );
		$column[] = array('key'=>_('Account'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="password" name="password" value="" size="64" maxlength="64"');
		$column[] = array('key'=>_('Password'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="repassword" name="repassword" value="" size="64" maxlength="64"');
		$column[] = array('key'=>_('Re Password'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" size="64" maxlength="64" require');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="telphone" name="telphone" value="'.$telphone.'" size="64" maxlength="64" require');
		$column[] = array('key'=>_('Telphone'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="address" name="address" value="'.$address.'" size="64" maxlength="64" require');
		$column[] = array('key'=>_('Address'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="vatnumber" name="vatnumber" value="'.$vatnumber.'" size="64" maxlength="64" require');
		$column[] = array('key'=>_('VATnumber'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="title" name="title" value="'.$title.'" size="64" maxlength="64" require');
		$column[] = array('key'=>_('Title'), 'value'=>$html);
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->ckeditor('id="description" name="description" required', $description);
		$column[] = array('key'=>_('Description'), 'value'=>$html);
		parent::$html->set_js($js);
	
		list($html, $js) = parent::$html->image('id="image" name="image" value="'.$image.'" required');
		$column[] = array('key'=>_('Image'), 'value'=>$html);
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="'.$sequence.'" min="0" max="9999" required');
		$column[] = array('key'=>_('sequence'), 'value'=>$html);
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

		$column[] = array('key'=>_('Lastloginip'), 'value'=>$lastloginip);
		$column[] = array('key'=>_('Lastlogintime'), 'value'=>$lastlogintime);
		$column[] = array('key'=>_('Inserttime'), 'value'=>$inserttime);
		$column[] = array('key'=>_('Modifytime'), 'value'=>$modifytime);
		$column[] = array('key'=>_('Modifyadmin_id'), 'value'=>$modifyadmin_id);
		
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

		//column
		$column = array(
				M_CLASS.'_id',
				'account',
				'password',
				'name',
				'telphone',
				'address',
				'vatnumber',
				'title',
				'description',
				'image',
				'act',
				'sequence',
				'lastloginip',
				'lastlogintime',
				'inserttime',
				'modifytime',
				'modifyadmin_id',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		foreach ($fetchAll as &$v0) {
			$v0['image'] = parent::get_gird_img(array('alt'=>$v0['name'], 'src'=>$v0['image']));
		}
		$response['data'] = $fetchAll;
		
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
}