<?php
class special_awardController extends backstageController {
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
			$special_id = $_POST['special_id'];
			$name = $_POST['name'];
			$amount = $_POST['amount'];
			$current = $_POST['current'];
			$unit = $_POST['unit'];
			$shape = $_POST['shape'];
			$exchange_message = $_POST['exchange_message'];
			$description = $_POST['description'];
			$image = $_POST['image'];
			$act = $_POST['act'];
	
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->where(array(array(array(array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					//event
					$add = array(
							'special_id'=>$special_id,
							'name'=>$name,
							'amount'=>$amount,
							'current'=>$current,
							'unit'=>$unit,
							'shape'=>$shape,
							'exchange_message'=>$exchange_message,
							'description'=>$description,
							'image'=>$image,
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
							'special_id'=>$special_id,
							'name'=>$name,
							'amount'=>$amount,
							'current'=>$current,
							'unit'=>$unit,
							'shape'=>$shape,
							'exchange_message'=>$exchange_message,
							'description'=>$description,
							'image'=>$image,
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
		$name = null;
		$special_id = null;
		$amount = 0;
		$current = 0;
		$unit = null;
		$shape = null;
		$exchange_message = null;
		$description = null;
		$image = null;
		$act = 'close';
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_name = null;
		
		/**
		 *  所有shape的型態
		 */
		$m_special_award_shape = Model('special_award')->fetchEnum('shape');
		foreach($m_special_award_shape as $v0) {$a_shape[] = ['text'=>$v0, 'value'=>$v0];}

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
					$m_special_award = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
					
					//form
					$name = $m_special_award['name'];
					$special_id = $m_special_award['special_id'];
					$amount = $m_special_award['amount'];
					$current = $m_special_award['current'];
					$unit = $m_special_award['unit'];
					$shape = $m_special_award['shape'];
					$exchange_message = $m_special_award['exchange_message'];
					$image = $m_special_award['image'];
					$act = $m_special_award['act'];
					$description = $m_special_award['description'];
					$inserttime = $m_special_award['inserttime'];
					$modifytime = $m_special_award['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_special_award['modifyadmin_id'])['name'];
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" size="64" maxlength="64" required');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);
	
		list($html, $js) = parent::$html->selectKit(['id'=>'special_id', 'name'=>'special_id'], parent::get_form_select('special'), $special_id);
		$column[] = ['key'=>_('Special'), 'value'=>$html];
		parent::$html->set_js($js);	

		list($html, $js) = parent::$html->selectKit(['id'=>'shape', 'name'=>'shape'], $a_shape, $shape);
		$column[] = ['key'=>_('Shape'), 'value'=>$html];
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->number('id="amount" name="amount" value="'.$amount.'" min="0" max="9999" required');
		$column[] = array('key'=>_('Num'), 'value'=>$html, 'key_remark'=>'(可兌換的獎品數量)');
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->number('id="current" name="current" value="'.$current.'" min="0" max="9999" required');
		$column[] = array('key'=>_('Current'), 'value'=>$html, 'key_remark'=>'(剩餘獎品數量)');
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="unit" name="unit" value="'.$unit.'" size="8" maxlength="8"');
		$column[] = array('key'=>_('Unit'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->ckeditor('id="description" name="description" required', $description);
		$column[] = array('key'=>_('Description'), 'value'=>$html);
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->image('id="image" name="image" value="'.$image.'" ');
		$column[] = array('key'=>_('Image'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="exchange_message" name="exchange_message" value="'.$exchange_message.'" size="128" maxlength="128" ');
		$column[] = array('key'=>_('Exchange message'), 'value'=>$html);
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
		
		list($html, $js) = parent::$html->form('id="form" action="" method="post" onsubmit="false"', $formcontent);
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
				'special_id',
				'name',
				'amount',
				'current',
				'unit',
				'shape',
				'exchange_message',
				'description',
				'image',
				'act',
				'inserttime',
				'modifytime',
				'modifyadmin_id',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		
		foreach ($fetchAll as &$v0) {
			if (!empty($v0['image'])) {
				$v0['image'] = parent::get_gird_img(array('alt'=>$v0['name'], 'src'=>$v0['image']));
			}
			if($v0['special_id'] != 0) {
				$v0['special_name'] = Model('special')->column(['name'])->where([[[['special_id', '=', $v0['special_id']]], 'and']])->fetchColumn();
			}
		}
		
		$response['data'] = $fetchAll;

		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
}