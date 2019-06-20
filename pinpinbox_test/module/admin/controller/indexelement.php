<?php
class indexelementController extends backstageController {
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

	//圖片區塊html - single
	function single_img($single, $img) {
		list($html, $js) = parent::$html->image('id="e_image" name="e_image" value="'.$img.'" ');
		$single_img = '<div><input id="d_r1" type="radio" name="e_image_transform" value="single" '.$single.'> <label for="d_r1">單張</label></radio></div><div>'.$html.'</div>';
		return $single_img;
	}

	//圖片區塊html - multi
	function multi_img($multi, $hot, $inserttime) {
		$multi_img = '<div class="multi_radio"><input id="d_r2" type="radio" name="e_image_transform" value="multi" '.$multi.'> <label for="d_r2">多張</label></radio></div><br>
			<div class="multi_radio" style="padding-left:30px;"><input id="d_r3" type="radio" name="e_sort" value="hot" '.$hot.'> <label for="d_r3">條件-熱門</label></radio>&nbsp;&nbsp;&nbsp
			<input type="radio" id="d_r4" name="e_sort" value="inserttime" '.$inserttime.'> <label for="d_r4">條件-新增時間</label></radio></div><br><br>';
		return $multi_img;
	}
	
	function elementlayout() {
		if (is_ajax()) {
			$value = $_POST['value'];  $data = '';
			$id = (!empty($_POST['id'])) ? $_POST['id'] : null ;
			$single = $multi = $unuse = $hot = $inserttime = $set_target= $keyword = $disable = $image = '';
			
			$m_indexelement = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $id)), 'and')))->fetch();
			
			if($value == $m_indexelement['indexelement_for']) {
				$image_transform = json_decode($m_indexelement['image_transform'], true);
				$image  = (!file_exists(PATH_UPLOAD.$image_transform['source'])) ? null :  $image_transform['source'] ;
				$$image_transform['transform'] = 'checked';
				if($image_transform['transform'] == 'multi') $$image_transform['sort'] = 'checked';
				if(is_numeric( $image_transform['target'])) {
					$set_target = (int)$image_transform['target'] ;
					$disable = 'disabled="disabled"';
				}else{	$set_target = $image_transform['target']; }
				$keyword = $image_transform['keyword'];
			}

			switch($value) {
				case 'categoryarea':
					$data .= '<div><h4>連結目標：</h4></div><br>';
					list($html, $js) = parent::$html->selectKit(['id'=>'e_categoryarea', 'name'=>'e_categoryarea'], parent::get_form_select('categoryarea'), (int)$set_target);
					$data .= '<div>'.$html.'</div><br><br>';
					$data .= '<div><h4>二態圖片：</h4>(單張模式支援GIF圖檔)</div><br>';
					$data .= $this->single_img($single, $image);
					$data .= $this->multi_img($multi, $hot, $inserttime);
					$data .= '<div><input id="d_r5" type="radio" name="e_image_transform" value="unuse" '.$unuse.'> <label for="d_r5">不採用</label></radio></div>';
				break;

				case 'creative':
					//條件: user至少需要有相本, 相本數量先不設限
					$column = ['COUNT(album.album_id) as album_count', 'user.user_id', 'user.name as user_name'];
					$where = [[[['user.act', '=', 'open'], ['album.act', '=', 'open']], 'and']];
					$join = [['left join', 'user', 'using(user_id)']];
					$group = ['user_id'];
					$m_user = Model('album')->column($column)->where($where)->join($join)->group($group)->fetchAll();
					
					$data .= '<div><h4>連結目標：</h4></div><br>';
					$data .= '<select id="e_creative"><option value="">'._('Please select').'</option> ';
						foreach($m_user as $k => $v0) {
							$selected = ($v0['user_id'] == substr($set_target, 0, strpos($set_target, ' -'))) ? 'selected="selected"' : null;
							$data .= '<option data-img-src="'.URL_STORAGE.Core::get_userpicture($v0['user_id']).'" value="'.$v0['user_id'].' - '.$v0['user_name'].'" '.$selected.'>'.$v0['user_id'].' - '.$v0['user_name'].'</option> ';
						}
					$data .= '</select>';
					$data .= '<br><br><div><h4>二態圖片：</h4></div><br>';
					$data .= $this->single_img($single, $image);
					$data .= $this->multi_img($multi, $hot, $inserttime);
					$data .= '<div><input id="d_r5" type="radio" name="e_image_transform" value="unuse" '.$unuse.'> <label for="d_r5">不採用</label></radio></div>';

				break;
				
				case 'custom' :
					$data .= '<div><h4>連結目標：</h4></div><br>';
					list($html, $js) = parent::$html->selectKit(['id'=>'e_custom', 'name'=>'e_custom'], parent::get_form_select('custom'), $set_target);
					$data .= '<div>'.$html.'</div><br><br>';
					$data .= '<div><h4>Keyword：</h4></div><br>';
					list($html, $js) = parent::$html->text('id="e_custom_keyword" name="e_custom_keyword" value="'.$keyword.'" '.$disable.' size="64" maxlength="64" required');
					$data .= '<div>'.$html.'</div><br><br>';
					$data .= '<div><h4>二態圖片：</h4></div><br>';
					$data .= $this->single_img($single, $image);
					$data .= '<div><input id="d_r5" type="radio" name="e_image_transform" value="unuse" '.$unuse.'> <label for="d_r5">不採用</label></radio></div>';
				break;

				case 'event' :
					$data .= '<div><h4>連結目標：</h4></div><br>';
					list($html, $js) = parent::$html->selectKit(['id'=>'e_event', 'name'=>'e_event'], parent::get_form_select('event'), (int)$set_target);
					$data .= '<div>'.$html.'</div><br><br>';
					$data .= '<div><h4>二態圖片：</h4></div><br>';
					$data .= $this->single_img($single, $image);
					$data .= $this->multi_img($multi, $hot, $inserttime);
					$data .= '<div><input id="d_r5" type="radio" name="e_image_transform" value="unuse" '.$unuse.'> <label for="d_r5">不採用</label></radio></div>';
				break;

				case 'link' :
					$data .= '<div><h4>連結目標：</h4></div><br>';
					list($html, $js) = parent::$html->selectKit(['id'=>'e_link', 'name'=>'e_link'], parent::get_form_select('pinpinmenu'), $set_target);
					$data .= '<div>'.$html.'</div><br><br>';
					$data .= '<div><h4>二態圖片：</h4></div><br>';
					$data .= $this->single_img($single, $image);
					$data .= $this->multi_img($multi, $hot, $inserttime);
					$data .= '<div><input id="d_r5" type="radio" name="e_image_transform" value="unuse" '.$unuse.'> <label for="d_r5">不採用</label></radio></div>';
				break;
				
				case 'region' :
					$data .= '<div><h4>連結目標：</h4></div><br>';
					list($html, $js) = parent::$html->selectKit(['id'=>'e_region', 'name'=>'e_region'], parent::get_form_select('region'), $set_target);
					$data .= '<div>'.$html.'</div><br><br>';
					$data .= '<div><h4>Keyword：</h4></div><br>';
					list($html, $js) = parent::$html->text('id="e_region_keyword" name="e_region_keyword" value="'.$keyword.'" '.$disable.' size="64" maxlength="64" required');
					$data .= '<div>'.$html.'</div><br><br>';
					$data .= '<div><h4>二態圖片：</h4></div><br>';
					$data .= $this->single_img($single, $image);
					$data .= $this->multi_img($multi, $hot, $inserttime);
					$data .= '<div><input id="d_r5" type="radio" name="e_image_transform" value="unuse" '.$unuse.'> <label for="d_r5">不採用</label></radio></div>';
				break;

				default: break;
			}
			json_encode_return(1, null, null, $data);
		}
		json_encode_return(0, _('Error'));
	}

	function form() {
		if (is_ajax()) {
			//form
			$name = $_POST['name'];
			$image = $_POST['image'];
			$icon = $_POST['icon'];
			$indexelement_for = $_POST['indexelement_for'];
			$tmp = json_decode( $_POST['tmp'], true);
			$url = $_POST['url'];
			$sequence = $_POST['sequence'];
			$act = $_POST['act'];
			
			$image_transform = [
				'target'=> $tmp[0],
				'transform'=> $tmp[1],
				'source'=> $tmp[2],
				'sort'=> $tmp[3],
				'keyword'=> $tmp[4],
			];
			
			if( (empty($image_transform['target']) && $act =='open' )|| empty($image_transform['transform'] )) json_encode_return(0, '關聯資料不完整');
			
			//檢查其他用途所需要的資料
			switch($indexelement_for) {
				case 'custom' :
					if( !is_numeric($image_transform['target']) && empty($image_transform['keyword'] )) json_encode_return(0, '必須輸入搜尋關鍵字');
				break;
			}
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->where(array(array(array(array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					$add = array(
							'name'=>$name,
							'indexelement_for'=>$indexelement_for,
							'image'=>$image,
							'icon'=>$icon,
							'url'=> str_replace(URL_ROOT, '', $url),
							'sequence'=>$sequence,
							'image_transform'=>json_encode($image_transform),
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
							'name'=>$name,
							'indexelement_for'=>$indexelement_for,
							'image'=>$image,
							'icon'=>$icon,
							'url'=> str_replace(URL_ROOT, '', $url),
							'sequence'=>$sequence,
							'image_transform'=>json_encode($image_transform),
							'act'=>$act,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->edit($edit);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-from
		$name = null;
		$image = null;
		$icon = null;
		$url = null;
		$sequence = null;
		$act = 'close';
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_name = null;
		$indexelement_for = null;

		$m_indexelement_for = Model(M_CLASS)->fetchEnum('indexelement_for');
		foreach($m_indexelement_for as $k0 => $v0) {
			if($k0 == 0) continue;
			$a_indexelement_for[] = array(
					'value'=>$v0,
					'text'=>$k0.' - '.$v0,
			);
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
				$indexelement_for = null;
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'add'));
				break;
				
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					$m_indexelement = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();

					//form
					$name = $m_indexelement['name'];
					$indexelement_for = $m_indexelement['indexelement_for'];
					$image = $m_indexelement['image'];
					$icon = $m_indexelement['icon'];
					$image_transform = json_decode($m_indexelement['image_transform'], true);
					$url = $m_indexelement['url'];
					$act = $m_indexelement['act'];
					$sequence = $m_indexelement['sequence'];
					$inserttime = $m_indexelement['inserttime'];
					$modifytime = $m_indexelement['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_indexelement['modifyadmin_id'])['name'];
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				//default js
				switch($indexelement_for){
					case 'categoryarea':
						$default_js = '$(\'#e_categoryarea\').val(\''.$image_transform['target'].'\').trigger(\'change\').trigger(\'chosen:updated\');';
						$default_js .= '$(\'input[name="e_image_transform"][value="'.$image_transform['transform'].'"]\').prop(\'checked\', true);';
					break;
				}

				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		parent::$data['indexelement_for'] = $indexelement_for;
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" size="64" maxlength="64" required');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->image('id="image" name="image" value="'.$image.'" required');
		$column[] = array('key'=>_('Image'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->image('id="icon" name="icon" value="'.$icon.'" required');
		$column[] = array('key'=>_('Icon'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->selectKit(['id'=>'indexelement_for', 'name'=>'indexelement_for'], $a_indexelement_for, $indexelement_for, 'category');
		$column[] = ['key'=>'類別', 'value'=>$html];
		parent::$html->set_js($js);

		list($html, $js) = array('<div id="elementlayout"></div>', null);
		$column[] = ['key'=>'關聯資料', 'value'=>$html];
		parent::$html->set_js($js);
		
		$prefix = ($image_transform['target'] == 'url') ? null : URL_ROOT;
		$column[] = array('key'=>_('Url'), 'value'=>'<a id="url" href="'.$prefix.$url.'" data-link="'.$prefix.$url.'">'.$prefix.$url.'</a>', 'key_remark'=>'點擊連結做測試');
		parent::$html->set_js($js);

		
		list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="'.$sequence.'" min="0" max="255" required');
		$column[] = array('key'=>_('Sequence'),'value'=>$html);
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
		
		list($html, $js) = parent::$html->form('id="form"', $formcontent);
		parent::$data['form'] = $html;
		parent::$html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$html->set_css(static_file('js/Image-Select/css/ImageSelect.css'), 'href');
		parent::$html->set_js(static_file('js/Image-Select/js/ImageSelect.jquery.js'), 'src');

		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function get_user_url() {
		if (is_ajax()) {
			$value = (!empty($_POST['id'])) ? $_POST['id'] : null ;
			$id = Core::get_creative_url(substr($value, 0, strpos($value, ' -')));
			$id = str_replace('admin/', '', $id);
			$data = $id;
			json_encode_return(1, null, null, $data);
		}
	}
	
	function delete() {
		die;
	}
	
	function json() {
		$response = array();

		//column
		$column = array(
				M_CLASS.'_id',
				'name',
				'indexelement_for',
				'image',
				'icon',
				'url',
				'sequence',
				'act',
				'modifytime',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();

		foreach ($fetchAll as &$v0) {
			if (!empty($v0['image'])) {
				$v0['image'] = parent::get_gird_img(array('alt'=>$v0['name'], 'src'=>$v0['image']));
			}
			if (!empty($v0['icon'])) {
				$v0['icon'] = parent::get_gird_img(array('alt'=>$v0['name'], 'src'=>$v0['icon']));
			}
			// if (!empty($v0['image_promote'])) {
				// $v0['image_promote'] = parent::get_gird_img(array('alt'=>$v0['name'], 'src'=>$v0['image_promote']));
			// }
			// $v0['eventjoinX'] = Model('eventjoin')->column(array('count(1)'))->where(array(array(array(array('event_id', '=', $v0['event_id'])), 'and')))->fetchColumn();
			// $v0['eventvoteX'] = Model('eventvote')->column(array('count(1)'))->where(array(array(array(array('event_id', '=', $v0['event_id'])), 'and')))->fetchColumn();
			// $v0['viewed'] = Model('eventstatistics')->column(array('viewed'))->where(array(array(array(array('event_id', '=', $v0['event_id'])), 'and')))->fetchColumn();
		}
		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();

		
		die(json_encode($response));
	}
	
	function grid_edit() {
		if (!empty($_REQUEST)) {
			Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', (int)$_REQUEST[M_CLASS.'_id'])), 'and')))->edit(array('sequence'=>$_REQUEST['sequence'], 'modifyadmin_id'=>adminModel::getSession()['admin_id']));
			
			json_encode_return(1, 'Edit success.');
		}
		die;
	}
}