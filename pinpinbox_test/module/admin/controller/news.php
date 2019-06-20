<?php
class newsController extends backstageController {
	function __construct() {}
	
	function analysis($news_id=null) {
		if (!isset($news_id) && !isset($_POST[M_CLASS.'_id'])) {
			throw new Exception("[".__METHOD__."] Parameters error");
		}
		
		$M_CLASS_id = isset($news_id)? $news_id : $_POST[M_CLASS.'_id'];
		$rangestart = isset($_POST['rangestart'])? $_POST['rangestart'] : null;
		$rangeend = isset($_POST['rangeend'])? $_POST['rangeend'] : null;
		
		$data = array();
		
		$where = array();
		$where[] = array(M_CLASS.'_id', '=', $M_CLASS_id);
		if (!empty($rangestart) && !empty($rangeend)) {
			$where[] = array('date', 'between', array($rangestart, $rangeend));
		} elseif (!empty($rangestart)) {
			$where[] = array('date', '>=', $rangestart);
		} elseif (!empty($rangeend)) {
			$where[] = array('date', '<=', $rangeend);
		}
		$m_newslog = Core::model('newslog')->get(sql_select_encode(array('date', 'act', 'sum(count)'), null, $where, array('date', 'act')), 'fetchAll');
		$tmp2 = array();
		$x = array();
		foreach ($m_newslog as $v1) {
			$x[$v1['date']] = $v1['date'];
			$tmp2[$v1['act']][$v1['date']] = $v1['sum(count)'];
		}
		$tmp3 = array();
		$tmp4 = array();
		$tmp5 = array();
		foreach ($tmp2 as $k1 => $v1) {
			$tmp4['name'] = $k1;
			foreach ($x as $v2) {
				$tmp3[$k1][$v2] = isset($v1[$v2])? (int)$v1[$v2] : 0;

				//Highcharts 必須使用 UTC 時區
				$date = new DateTime($v2, new DateTimeZone('UTC'));
				
				$tmp4['data'][] = array($date->getTimestamp() * 1000, $tmp3[$k1][$v2]);
			}
			$tmp5[] = $tmp4;
		}
		$series = $tmp5;
		$categories = str_replace(date('Y-'), '', array_values($x));
		$data['chart_by_act'] = array('categories'=>$categories, 'series'=>$series);
		
		if (is_ajax()) {
			json_encode_return(1, _('Success'), null, $data);
		} else {
			return array_encode_return(1, _('Success'), null, $data);
		}
	}
	
	function index() {
		list($html0, $js0) = parent::$html->grid();
		list($html1, $js1) = parent::$html->imagebox('.grid-img');
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
			$name = $_POST['name'];
			$title = $_POST['title'];
			$text = $_POST['text'];
			$image = $_POST['image'];
			$class = $_POST['class'];
			$starttime = $_POST['starttime'];
			$endtime = $_POST['endtime'];
			$act = $_POST['act'];
			$sequence = $_POST['sequence'];
			
			//magazine
			$a_customize_magazine = isset($_POST['customize_magazine'])? $_POST['customize_magazine'] : array();
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Core::model(M_CLASS)->get(sql_select_encode(null, null, array(array('name', '=', $name))))) {
						json_encode_return(0, _('Data already exists by [Name]'));
					}
					
					//form
					$param = array();
					$param['name'] = $name;
					$param['title'] = $title;
					$param['text'] = $text;
					$param['image'] = $image;
					$param['class'] = $class;
					$param['starttime'] = $starttime;
					$param['endtime'] = $endtime;
					$param['act'] = $act;
					$param['sequence'] = $sequence;
					
					//magazine
					$param['customize_magazine'] = array_emit($a_customize_magazine);
					
					Core::model(M_CLASS)->add($param);
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
		
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					if (Core::model(M_CLASS)->get(sql_select_encode(null, null, array(array(M_CLASS.'_id', '!=', $M_CLASS_id), array('name', '=', $name))))) {
						json_encode_return(0, _('Data already exists by [Name]'));
					}
					
					//form
					$param = array();
					$param['name'] = $name;
					$param['title'] = $title;
					$param['text'] = $text;
					$param['image'] = $image;
					$param['class'] = $class;
					$param['starttime'] = $starttime;
					$param['endtime'] = $endtime;
					$param['act'] = $act;
					$param['sequence'] = $sequence;
					
					//magazine
					$param['customize_magazine'] = array_emit($a_customize_magazine);
					
					Core::model(M_CLASS)->edit($M_CLASS_id, $param);
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$name = null;
		$title = null;
		$text = null;
		$image = null;
		$class = 'blog';
		$starttime = null;
		$endtime = null;
		$act = 'preview';
		$sequence = 65535;
		
		//初始值-magazine
		$a_customize_magazine = array();
		$a_customize_magazine['page'] = array();
		
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
					
					$m_news = Core::model(M_CLASS)->get(sql_select_encode(null, null, array(array(M_CLASS.'_id', '=', $M_CLASS_id))));
					
					//form
					$name = $m_news['name'];
					$title = $m_news['title'];
					$text = $m_news['text'];
					$image = $m_news['image'];
					$class = $m_news['class'];
					$starttime = $m_news['starttime'];
					$endtime = $m_news['endtime'];
					$act = $m_news['act'];
					$sequence = $m_news['sequence'];
					
					//magazine
					$a_customize_magazine = array_parse($m_news['customize_magazine']);
					if (!isset($a_customize_magazine['page'])) $a_customize_magazine['page'] = array();
				}
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				//analysis
				$column2 = array();
				$extra2 = null;
				
				list($html1, $js1) = parent::$html->datetime('id="rangestart"');
				list($html2, $js2) = parent::$html->datetime('id="rangeend"');
				list($html3, $js3) = parent::$html->button('id="reflesh" value="'._('Reflesh').'"');
				$column2[] = array('key'=>_('Date range'), 'value'=>$html1.'&emsp;~&emsp;'.$html2.'&emsp;&emsp;'.$html3);
				$js3 .= '
					$("#reflesh").on("click", function(){
						$.post("'.parent::url(M_CLASS, 'analysis').'", {
							'.M_CLASS.'_id: '.$M_CLASS_id.',
							rangestart: $("#rangestart").val(),
							rangeend: $("#rangeend").val()
						}, function(response){
							response = $.parseJSON(response);
							var result = response.result, message = response.message, data = response.data;
							if (result) {
								$.each(data.chart_by_act.series, function(k1, v1){
									chart_by_act.series[k1].setData(v1.data, false);
								});
								chart_by_act.redraw();
							} else {
								alert(message);
							}
						});
					});
				';
				parent::$html->set_js($js1.$js2.$js3);
				
				list($result, $message, $redirect, $data) = array_decode_return($this->analysis($M_CLASS_id));
				list($html, $js) = parent::$html->highcharts();
				$column2[] = array('key'=>_('By Act'), 'value'=>$html);
				$js .= '
				var chart_by_act = new Highcharts.Chart({
			        chart: {
					    renderTo: "highcharts",
			            type: "line"
			        },
			        title: {
			            text: "'.$name.'"
			        },
			        subtitle: {
			        },
			        xAxis: {
			            type: "datetime",
			            tickInterval: 24 * 3600 * 1000, // one day
		                title: {
		                    text: "'._('Date').'"
		                }
			        },
			        yAxis: {
			        },
			        tooltip: {
			        	crosshairs: true,
			        	shared: true
			        },
			        plotOptions: {
			            line: {
			                dataLabels: {
			                    enabled: true
			                },
			                enableMouseTracking: true
			            }
			        },
			        series: '.json_encode($data['chart_by_act']['series']).'
				});
				';
				parent::$html->set_js($js);
				
				list($html, $js) = parent::$html->table('class="table"', $column2, $extra2);
				$a_tabs[2] = array('href'=>'#tabs-2', 'name'=>_('Analysis'), 'value'=>$html);
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" maxlength="32" size="64"');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="title" name="title" value="'.$title.'" maxlength="64" size="128"');
		$column[] = array('key'=>_('Title'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->ckeditor('id="text" name="text"', $text);
		$column[] = array('key'=>_('Text'), 'value'=>$html, 'key_remark'=>'(婚享線上內容寬度為 700px，上傳圖檔注意一下有沒有超過哦)');
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->image('id="image" name="image" value="'.$image.'"');
		$column[] = array('key'=>_('Image'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->select2('id="class" name="class"', Core::$_config['CONFIG']['NEWS_CLASS'], $class);
		$column[] = array('key'=>_('Class'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->datetime('id="starttime" name="starttime" value="'.$starttime.'"');
		$column[] = array('key'=>_('StartTime'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->datetime('id="endtime" name="endtime" value="'.$endtime.'"');
		$column[] = array('key'=>_('EndTime'), 'value'=>$html);
		parent::$html->set_js($js);
		
		switch (parent::get_admin_admingroup_class()) {
			case 'administrator':
			case 'approver':
				$a_act = array();
				foreach (Core::$_config['CONFIG']['NEWS_ACT'] as $k1 => $v1) {
					$tmp1 = array();
					$tmp1['name'] = 'act';
					$tmp1['value'] = $k1;
					$tmp1['text'] = $v1;
					$a_act[] = $tmp1;
				}
				list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
				$column[] = array('key'=>_('Act'), 'value'=>$html);
				parent::$html->set_js($js);
				break;
		}
		
		list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="'.$sequence.'" min="0" max="65535" required');
		$column[] = array('key'=>_('Sequence'),'value'=>$html);
		parent::$html->set_js($js);
		
		list($html1, $js1) = parent::$html->submit('value="'._('Submit').'"');
		list($html2, $js2) = parent::$html->back('value="'._('Back').'"');
		$column[] = array('key'=>'&nbsp;', 'value'=>$html1.'&emsp;'.$html2);
		parent::$html->set_js($js1.$js2);
		
		list($html, $js) = parent::$html->table('class="table"', $column, $extra);
		$a_tabs[0] = array('href'=>'#tabs-0', 'name'=>_('Form'), 'value'=>$html);
		parent::$html->set_js($js);
		
		//magazine
		$column = array();
		$extra = null;
		
		list($html, $js) = parent::$html->dynamictable('image', 'name="customize_magazine[page][]"', $a_customize_magazine['page']);
		$column[] = array('key'=>_('Page'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->table('class="table"', $column, $extra);
		$a_tabs[1] = array('href'=>'#tabs-1', 'name'=>_('Magazine'), 'value'=>$html);
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
		if (!empty($_POST)) {
			$M_CLASS_id = $_POST[M_CLASS.'_id'];
			Core::model(M_CLASS)->delete($M_CLASS_id);
			json_encode_return(1, _('Success'));
		}
		die;
	}
	
	function json() {
		if (!class_exists('db')) include PATH_ROOT.'lib/db.php';
		$db = new db(Core::$_config['CONFIG']['DB']['site']);
		
		//取得條件
		$page = $_REQUEST['page'];
		$limit = $_REQUEST['rows'];
		$sidx = $_REQUEST['sidx'];
		$sord = $_REQUEST['sord'];
		$totalrows = isset($_REQUEST['totalrows'])? $_REQUEST['totalrows']: false;
		
		//組 where
		$where = null;
		if (!empty($_REQUEST['filters'])) {
			$filters = json_decode($_REQUEST['filters'], true);
			$groupOp = $filters['groupOp'];
			$rules = $filters['rules'];
			if (!empty($rules)) {
				$tmp = array();
				foreach ($rules as $v) {
					$field = $v['field'];
					$data = $v['data'];
					$tmp[] = $field.' like '.$db->quote($data.'%');
				}
				$where = 'where '.implode(' '.$groupOp.' ', $tmp);
			}
		}
		
		//總筆數
		$sql = "SELECT count(".M_CLASS.".".M_CLASS."_id)
		FROM ".M_CLASS."
		".$where;
		$count = $db->fetchColumn($sql);
		
		//條件
		if ($totalrows) $limit = $totalrows;
		if (!$sidx) $sidx = 1;
		$total_pages = ($count > 0)? ceil($count / $limit) : 0;
		if ($page > $total_pages) $page = $total_pages;
		if ($limit < 0) $limit = 0;
		$start = $limit * $page - $limit;
		if ($start < 0) $start = 0;
		
		//data
		$sql = "SELECT ".M_CLASS.".".M_CLASS."_id, ".M_CLASS.".name, ".M_CLASS.".title, ".M_CLASS.".image, ".M_CLASS.".class, ".M_CLASS.".act, ".M_CLASS.".sequence, ".M_CLASS.".modifytime, ".M_CLASS.".modifyadmin_id
		FROM ".M_CLASS."
		$where ORDER BY $sidx $sord LIMIT $start, $limit";
		$fetchAll = $db->fetchAll($sql);
		$response = array();
		$response['page'] = $page;
		$response['total'] = $total_pages;
		$response['records'] = $count;
		foreach ($fetchAll as $k => &$v) {
			$name = null;
			$name = $v['name'];
			$v['name'] = '<a href="'.parent::url(M_CLASS, 'form', array('act'=>'edit', M_CLASS.'_id'=>$v[M_CLASS.'_id'])).'">'.$name.'</a>';
			
			//image
			if (!empty($v['image'])) {
				$v['image'] = '<a class="jqgrid_img" title="'.$name.'" href="'.URL_UPLOAD.$v['image'].'"><img src="'.URL_UPLOAD.getimageresize($v['image']).'" border="0"></a>';
			}
			
			//class
			$v['class'] = Core::$_config['CONFIG']['NEWS_CLASS'][$v['class']];
			
			$v['modifyadmin_id'] = parent::get_admin_name_by_admin_id($v['modifyadmin_id']);
			
			$tmp = array_values($v);
			$v = array();
			$v['cell'] = $tmp;
		}
		$response['rows'] = $fetchAll;
		
		die(json_encode($response));
	}
	
	function jqgrid_edit() {
		if (isset($_REQUEST['oper'])) {
			$oper = $_REQUEST['oper'];
			switch ($oper) {
				case 'edit':
					$M_CLASS_id = $_REQUEST[M_CLASS.'_id'];
					$celname = $_REQUEST['celname'];
					$value = $_REQUEST['value'];
	
					Core::model(M_CLASS)->edit($M_CLASS_id, array($celname=>$value));
					break;
	
				default:
					break;
			}
		}
		die;
	}
}