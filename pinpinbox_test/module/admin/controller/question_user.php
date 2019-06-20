<?php
class question_userController extends backstageController {
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
			$answer = $_POST['answer'];
			$state = $_POST['state'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					break;
		
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					$edit = array(
							'answer'=>$answer,
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
					
					$m_question_user = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
					
					//question
					$html_question = Model('question')->where(array(array(array(array('question_id', '=', $m_question_user['question_id'])), 'and')))->fetch()['name'];
					
					//user
					$html_user = Model('user')->where(array(array(array(array('user_id', '=', $m_question_user['user_id'])), 'and')))->fetch()['name'];
					
					$question = $m_question_user['question'];
					$answer = $m_question_user['answer'];
					$state = $m_question_user['state'];
					$inserttime = $m_question_user['inserttime'];
					$modifytime = $m_question_user['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_question_user['modifyadmin_id'])['name'];
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('question'), 'value'=>$html_question);
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('user'), 'value'=>$html_user);
		
		$column[] = array('key'=>_('Question'), 'value'=>nl2br(htmlspecialchars($question)));
		
		list($html, $js) = parent::$html->textarea('id="answer" name="answer" style="width:400px; height:100px; font-size:14px;"', htmlspecialchars($answer));
		$column[] = array('key'=>_('Answer'), 'value'=>$html);
		parent::$html->set_js($js);
		
		$a_state = array();
		foreach (json_decode(Core::settings('QUESTION_USER_STATE'), true) as $k0 => $v0) {
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
				'question_id',
				'user_id',
				'question',
				'answer',
				'state',
				'modifytime',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		foreach ($fetchAll as &$v0) {
			$v0['questionX'] = parent::get_grid_display('question', $v0['question_id']);
			$v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
			$v0['question'] = nl2br(htmlspecialchars($v0['question']));
			$v0['answer'] = nl2br(htmlspecialchars($v0['answer']));
		}
		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
}