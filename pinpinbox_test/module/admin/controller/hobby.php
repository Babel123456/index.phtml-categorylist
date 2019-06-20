<?php
class hobbyController extends backstageController {
	function __construct() {}
	
	function index() {
		$Html = new Lib\html();
		
		list ($html, $js) = $Html->grid();
		parent::$data['index'] = $html;
		$Html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function form() {
		if (is_ajax()) {
			//form
			$name = $_POST['name'];
			$a_category = $_POST['category'];
			$image = $_POST['image'];
			$act = $_POST['act'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					if ((new hobbyModel)->where([[[['name', '=', $name]], 'and']])->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					//form
					$hobby_id = (new hobbyModel)->add([
							'name'=>$name,
							'image'=>$image,
							'inserttime'=>inserttime(),
							'act'=>$act,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					]);
					
					if ($a_category) {
						foreach ($a_category as $v0) {
							$add[] = [
									'hobby_id'=>$hobby_id,
									'category_id'=>$v0,
							];
						}
						
						(new hobby2categoryModel)->add($add);
					}
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
						
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					if ((new hobbyModel)->where([[[[M_CLASS.'_id', '!=', $M_CLASS_id], ['name', '=', $name]], 'and']])->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					//form
					(new hobbyModel)->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->edit([
							'name'=>$name,
							'image'=>$image,
							'act' => $act,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					]);
					
					if ($a_category) {
						(new hobby2categoryModel)->where([[[['hobby_id', '=', $M_CLASS_id]], 'and']])->delete();
						
						foreach ($a_category as $v0) {
							$add[] = [
									'hobby_id'=>$M_CLASS_id,
									'category_id'=>$v0,
							];
						}
					
						(new hobby2categoryModel)->add($add);
					}
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$name = null;
		$a_category = [];
		$inserttime = null;
		$modifytime = null;
		$image = null;
		$modifyadmin_name = null;
		$act = 'close';
		$Html = new Lib\html();
		
		//form
		$column = [];
		$extra = null;
		
		//tabs
		$a_tabs = [];
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				parent::$data['action'] = parent::url(M_CLASS, 'form', ['act'=>'add']);
				break;
				
			//修改	
			case 'edit':
				$M_CLASS_id = $_GET[M_CLASS.'_id'];
				
				$m_hobby = (new hobbyModel)->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->fetch();
				
				//form
				$name = $m_hobby['name'];
				$a_category = array_column((new hobby2categoryModel)->column(['category_id'])->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->fetchAll(), 'category_id');
				$image = $m_hobby['image'];
				$inserttime = $m_hobby['inserttime'];
				$modifytime = $m_hobby['modifytime'];
				$act = $m_hobby['act'];
				$modifyadmin_name = adminModel::getOne($m_hobby['modifyadmin_id'])['name'];
				
				list ($html, $js) = $Html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				$Html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', ['act'=>'edit']);
				break;
		}
		
		list ($html, $js) = $Html->text('id="name" name="name" value="'.$name.'" size="32" maxlength="32" required');
		$column[] = ['key'=>_('Name'), 'value'=>$html];
		$Html->set_js($js);
		
		$m_category = (new categoryModel)->column(['category_id', 'name'])->fetchAll();
		$s_category = [];
		foreach ($m_category as $v0) {
			$s_category[] = [
					'value'=>$v0['category_id'],
					'text'=>$v0['name'].' - '.$v0['category_id'],
			];
		}
		list ($html, $js) = $Html->selectKit(['id'=>'category', 'name'=>'category', 'multiple'=>true], $s_category, $a_category);
		$column[] = ['key'=>parent::get_adminmenu_name_by_class('category'), 'value'=>$html];
		$Html->set_js($js);

		list($html, $js) = parent::$html->image('id="image" name="image" value="'.$image.'"');
		$column[] = array('key'=>_('Image'), 'value'=>$html);
		parent::$html->set_js($js);

		$a_act = array();
		foreach (json_decode(Core::settings('EVENT_ACT'), true) as $k0 => $v0) {
			$a_act[] = [
					'name'=>'act',
					'value'=>$k0,
					'text'=>$v0,
			];
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
		$column[] = array('key'=>_('Act'), 'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = ['key'=>_('Insert Time'), 'value'=>$inserttime];
		
		$column[] = ['key'=>_('Modify Time'), 'value'=>$modifytime];
		
		$column[] = ['key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name];
		
		list ($html0, $js0) = $Html->submit('value="'._('Submit').'"');
		list ($html1, $js1) = $Html->back('value="'._('Back').'"');
		$column[] = ['key'=>'&nbsp;', 'value'=>$html0.'&emsp;'.$html1];
		$Html->set_js($js0.$js1);
		
		list ($html, $js) = $Html->table('class="table"', $column, $extra);
		$a_tabs[0] = ['href'=>'#tabs-0', 'name'=>_('Form'), 'value'=>$html];
		$Html->set_js($js);
		
		//user
		$column = [];
		$extra = null;
		
		list ($html, $js) = $Html->grid();
		$column[] = ['key'=>parent::get_adminmenu_name_by_class('user'), 'value'=>$html];
		$Html->set_js($js);
		parent::$data[M_CLASS.'_id'] = empty($M_CLASS_id)? '[]' : $M_CLASS_id;
		
		list ($html, $js) = $Html->table('class="table"', $column, $extra);
		$a_tabs[1] = ['href'=>'#tabs-1', 'name'=>parent::get_adminmenu_name_by_class('user'), 'value'=>$html];
		$Html->set_js($js);
		
		list ($html, $js) = $Html->tabs($a_tabs);
		$formcontent = $html;
		$Html->set_js($js);
		
		list ($html, $js) = $Html->form('id="form"', $formcontent);
		parent::$data['form'] = $html;
		$Html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function delete() {
		die;
	}
	
	function json() {
		$response = [];
		
		$case = isset($_POST['case'])? $_POST['case'] : null;
		
		switch ($case) {
			default:
				//column
				$column = [
						'hobby.hobby_id',
						'hobby.name',
						'hobby.act',
						'hobby.modifytime',
				];
				
				list ($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$fetchAll = (new hobbyModel)->column($column)
				->where($where)
				->group($group)
				->order($order)
				->limit($limit)
				->fetchAll();
				
				foreach ($fetchAll as &$v0) {
					$m_category = (new categoryModel)
					->column(['category_id', 'name'])
					->join([['inner join', 'hobby2category', 'USING(category_id)']])
					->where([[[['hobby2category.hobby_id', '=', $v0['hobby_id']]], 'and']])
					->order(['category_id'=>'ASC'])
					->fetchAll();
					
					$array_0 = [];
					foreach ($m_category as $v1) {
						$array_0[] = $v1['name'].' - '.$v1['category_id'];
					}
					$v0['categoryX'] = implode('<br>', $array_0);
					
					$v0['userX'] = (new hobby_userModel)->column(['COUNT(1)'])->where([[[['hobby_id', '=', $v0['hobby_id']]], 'and']])->fetchColumn();
				}
				$response['data'] = $fetchAll;
				
				//total
				$response['total'] = (new hobbyModel)->column(['COUNT(1)'])->where($where)->group($group)->fetchColumn();
				break;
			
			case 'user':
				//column
				$column = [
						'user_id',
						'inserttime',
				];
				
				list ($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$fetchAll = Model('hobby_user')->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
				foreach ($fetchAll as &$v0) {
					$v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
				}
				$response['data'] = $fetchAll;
				
				//total
				$response['total'] = Model('hobby_user')->column(['count(1)'])->where($where)->group($group)->fetchColumn();
				break;
		}
		
		die(json_encode($response));
	}
}