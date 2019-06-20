<?php
class taskController extends backstageController {
	function __construct() {}
	
	function index() {
		list($html0, $js0) = parent::$html->grid();
		list($html1, $js1) = parent::$html->browseKit(['selector'=>'.grid-img']);
		parent::$data['index'] = $html0.$html1;
		parent::$html->set_js($js0.$js1);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function form() {
		if (is_ajax()) {

			//task
			$name = $_POST['name'];
			$platform = $_POST['platform'];
			$task_for = $_POST['task_for'];
			$event_id = $_POST['event_id'];
			$reward = $_POST['reward'];
			$reward_value = $_POST['reward_value'];
			$restriction = $_POST['restriction'];
			$restriction_value = $_POST['restriction_value'];
			$condition = $_POST['condition'];
			$condition_value = $_POST['condition_value'];
			$whitelist = $_POST['whitelist'];
			$blacklist = $_POST['blacklist'];
			$feedback_message_success = $_POST['feedback_message_success'];
			$feedback_message_fail = $_POST['feedback_message_fail'];
			$upperlimit = $_POST['upperlimit'];
			$description = $_POST['description'];
			$starttime = $_POST['starttime'];
			$endtime = $_POST['endtime'];
			$act = $_POST['act'];
			
			$a_feedback_message = [
				'success' => $feedback_message_success,
				'fail' =>$feedback_message_fail,
			];

			if($reward != 'point') $upperlimit = 0 ; //point upperlimit防呆

			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->column(['count(1)'])->where([[[['name', '=', $name]], 'and']])->fetchColumn()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					//task
					$add = [
							'name'=>$name,
							'platform'=>$platform,
							'task_for'=>$task_for,
							'event_id'=>$event_id,
							'reward'=>$reward,
							'restriction'=>$restriction,
							'restriction_value'=>$restriction_value,
							'`condition`'=>$condition,
							'condition_value'=>$condition_value,
							'whitelist'=>$whitelist,
							'blacklist'=>$blacklist,
							'reward_value'=>$reward_value,
							'upperlimit'=>$upperlimit,
							'description'=>$description,
							'starttime'=>$starttime,
							'endtime'=>$endtime,
							'feedback_message'=>json_encode($a_feedback_message),
							'act'=>$act,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					];
					Model(M_CLASS)->add($add);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
				
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					if (Model(M_CLASS)->column(['count(1)'])->where([[[[M_CLASS.'_id', '!=', $M_CLASS_id], ['name', '=', $name]], 'and']])->fetchColumn()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					//task
					$edit = [
							'task_id'=>$M_CLASS_id,
							'name'=>$name,
							'platform'=>$platform,
							'task_for'=>$task_for,
							'event_id'=>$event_id,
							'reward'=>$reward,
							'reward_value'=>$reward_value,
							'restriction'=>$restriction,
							'restriction_value'=>$restriction_value,
							'`condition`'=>$condition,
							'condition_value'=>$condition_value,
							'whitelist'=>$whitelist,
							'blacklist'=>$blacklist,
							'upperlimit'=>$upperlimit,
							'description'=>$description,
							'starttime'=>$starttime,
							'endtime'=>$endtime,
							'feedback_message'=>json_encode($a_feedback_message),
							'act'=>$act,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					];
					Model(M_CLASS)->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->edit($edit);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$task_id = null;
		$name = null;
		$platform = null;
		$task_for = null;
		$event_id = 0;
		$reward = null;
		$reward_value = null;
		$restriction = null;
		$restriction_value = 0;
		$condition = null;
		$condition_value = 0;
		$whitelist = 0;
		$blacklist = 0;
		$feedback_message = ['success'=>'', 'fail' => ''];
		$upperlimit = null;
		$description = null;
		$starttime = null;
		$endtime = null;
		$act = 'close';
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_name = null;
		
		$report = null ;

		$a_task_enum_list = ['a_platform', 'a_task_for', 'a_restriction', 'a_condition', 'a_reward'];
		foreach ($a_task_enum_list as $k0 => $v0) {
			$$v0 = []; $tmp = null;
			$tmp = Model('task')->fetchEnum( str_replace('a_', '',  $v0));
			foreach ($tmp as $k1 => $v1) {
				if($v1 == 'none') continue;
				array_push($$v0, ['value' => $v1, 'text'=>$v1]);
			}
		}

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
				$M_CLASS_id = 0;
				break;
				
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					
					$m_task = Model(M_CLASS)->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->fetch();
					$name = $m_task['name'];
					$platform = $m_task['platform'];
					$task_for = $m_task['task_for'];
					$event_id = $m_task['event_id'];
					$reward = $m_task['reward'];
					$reward_value = $m_task['reward_value'];
					$restriction = $m_task['restriction'];
					$restriction_value = $m_task['restriction_value'];
					$condition = $m_task['condition'];
					$condition_value = $m_task['condition_value'];
					$whitelist = $m_task['whitelist'];
					$blacklist = $m_task['blacklist'];
					$feedback_message = json_decode($m_task['feedback_message'], true );
					$upperlimit = $m_task['upperlimit'];
					$description = $m_task['description'];
					$starttime = $m_task['starttime'];
					$endtime = $m_task['endtime'];
					$act = $m_task['act'];
					$inserttime = $m_task['inserttime'];
					$modifytime = $m_task['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_task['modifyadmin_id'])['name'];
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				switch ($reward) {
					case 'point':
						$report = Model('taskqueue')->column(['SUM(`reward_value`)'])->where([[[['task_id', '=', $M_CLASS_id]] ,'and']])->fetchColumn();
						$report = $report.' P';
						break;
					
					case 'grade':
						//0711 - 暫無功能
						break;
				}
				

				parent::$data['action'] = parent::url(M_CLASS, 'form', ['act'=>'edit']);
				break;
		}
		
		parent::$data['task_id'] = $M_CLASS_id;
		parent::$data['report'] = $report;
		parent::$data['task_name'] = $name;

		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" size="64" maxlength="64" required');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->selectKit(['id'=>'platform', 'name'=>'platform'], $a_platform, $platform);
		$column[] = ['key'=>'Platform', 'value'=>$html, 'key_remark'=>'任務關聯平台'];
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->selectKit(['id'=>'task_for', 'name'=>'task_for'], $a_task_for, $task_for);
		$column[] = ['key'=>'Task_for', 'value'=>$html, 'key_remark'=>'任務目的'];
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->selectKit(['id'=>'event_id', 'name'=>'event_id'], parent::get_form_select('event'), $event_id);
		$column[] = ['key'=>'Event_id', 'value'=>$html, 'key_remark'=>'任務活動連結'];
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->selectKit(['id'=>'reward', 'name'=>'reward'], $a_reward, $reward, ['grade']);
		$column[] = ['key'=>'Reward', 'value'=>$html, 'key_remark'=>'獎勵類型'];
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->text('id="reward_value" name="reward_value" value="'.$reward_value.'" size="32" maxlength="32" required');
		$column[] = array('key'=>_('Reward_value'), 'value'=>$html, 'key_remark'=>'任務獎勵');
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->text('id="upperlimit" name="upperlimit" value="'.$upperlimit.'" size="32" maxlength="32" required');
		$column[] = array('key'=>_('Upperlimit'), 'value'=>$html, 'key_remark'=>'獎勵點數預計發放上限<br>無上限請設為0<br>(單一關聯平台)');
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->selectKit(['id'=>'restriction', 'name'=>'restriction'], $a_restriction, $restriction, ['total']);
		$column[] = ['key'=>'Restriction', 'value'=>$html, 'key_remark'=>'任務限制(無限制/個人領取/總領取)'];
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->text('id="restriction_value" name="restriction_value" value="'.$restriction_value.'" size="32" maxlength="32"');
		$column[] = array('key'=>_('Restriction_value'), 'value'=>$html, 'key_remark'=>'數量');
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->selectKit(['id'=>'condition', 'name'=>'condition'], $a_condition, $condition, ['level', 'grade']);
		$column[] = ['key'=>'Condition', 'value'=>$html, 'key_remark'=>'領取條件(無限制/等級/身分)'];
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->text('id="condition_value" name="condition_value" value="'.$condition_value.'" size="32" maxlength="32"');
		$column[] = array('key'=>_('Condition_value'), 'value'=>$html, 'key_remark'=>'條件內容');
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->text('id="whitelist" name="whitelist" value="'.$whitelist.'" size="32"');
		$column[] = array('key'=>_('White List'), 'value'=>$html, 'key_remark'=>'白名單<br>(多筆請用逗號分開)');
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->text('id="blacklist" name="blacklist" value="'.$blacklist.'" size="32"');
		$column[] = array('key'=>_('Black List'), 'value'=>$html, 'key_remark'=>'黑名單<br>(多筆請用逗號分開)');
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->text('id="feedback_message_success" name="feedback_message_success" value="'.$feedback_message['success'].'" size="64" maxlength="64" required');
		$column[] = array('key'=>_('Feedback_message_success'), 'value'=>$html, 'key_remark'=>'領取成功回傳訊息');
		parent::$html->set_js($js);
	
		list($html, $js) = parent::$html->text('id="feedback_message_fail" name="feedback_message_fail" value="'.$feedback_message['fail'].'" size="64" maxlength="64"');
		$column[] = array('key'=>_('Feedback_message_fail'), 'value'=>$html, 'key_remark'=>'領取失敗回傳訊息(可留空)');
		parent::$html->set_js($js);
	
		list($html, $js) = parent::$html->text('id="description" name="description" value="'.$description.'" size="64" maxlength="64"');
		$column[] = array('key'=>_('Description'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->datetime('id="starttime" name="starttime" value="'.$starttime.'" required');
		$column[] = array('key'=>_('Start Time'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->datetime('id="endtime" name="endtime" value="'.$endtime.'" required');
		$column[] = array('key'=>_('End Time'), 'value'=>$html);
		parent::$html->set_js($js);

		$a_act = array();
		foreach (json_decode(Core::settings('AD_ACT'), true) as $k0 => $v0) {
			$a_act[] = [
					'name'=>'act',
					'value'=>$k0,
					'text'=>$v0,
			];
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
		
		//taskqueue
		$column = array();
		$extra = null;
		
		list($html, $js) = parent::$html->grid('taskqueue-grid');
		$column[] = array('key'=>_('TaskQueue'), 'value'=>$html);
		parent::$html->set_js($js);
		parent::$data[M_CLASS.'_id'] = empty($M_CLASS_id)? '[]' : $M_CLASS_id;
		
		list($html, $js) = parent::$html->table('class="table"', $column, $extra);
		$a_tabs[1] = array('href'=>'#tabs-1', 'name'=>_('TaskQueue'), 'value'=>$html);
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
		if (!empty($_POST)) {
			Model(M_CLASS)->where([[[[M_CLASS.'_id', '=', $_POST[M_CLASS.'_id']]], 'and']])->delete();
			json_encode_return(1, _('Success'));
		}
		die;
	}
	
	function json() {
		$response = [];
		
		$case = isset($_POST['case'])? $_POST['case'] : null;

		switch ($case) {
			case 'taskqueue':
				//column
				$column = [
						'user_id',
						'inserttime',
						'type',
						'type_id',
						'reward',
						'reward_value',
				];
				
				list($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$fetchAll = Model('taskqueue')->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
				foreach ($fetchAll as &$v0) {
					$v0['albumX'] = parent::get_grid_display('user', $v0['user_id']);
					$v0['albumX2'] = parent::get_grid_display('album', $v0['type_id']);
				}
				$response['data'] = $fetchAll;

				break;
				
			
			default:
				//column
				$column = [
						M_CLASS.'_id',				
						'name',
						'platform',
						'task_for',
						'reward',
						'reward_value',
						'act',
						'modifytime',
				];
				
				list($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();

				$response['data'] = $fetchAll;
				
				//total
				$response['total'] = Model(M_CLASS)->column(['count(1)'])->where($where)->group($group)->fetchColumn();
				break;
		}
		
		
		die(json_encode($response));
	}
}