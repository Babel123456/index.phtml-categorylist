<?php
class disqusController extends frontstageController {
	public static $PUBLIC_KEY ,$client_id, $SECRET_KEY;

	function __construct() {
		self::$PUBLIC_KEY = self::$client_id = Core::settings('DISQUS_PUBLIC_KEY');
		self::$SECRET_KEY = Core::settings('DISQUS_SECRET_KEY');
	}
	
	function API_posts_details ($id) {
		$return = null;
		if($id) {
			$return = 'https://disqus.com/api/3.0/posts/details.json?post='.$id.'&'; 
		}
		return $return;
	}
		
	function getData($url, $SECRET_KEY, $access_token) {
		//Setting OAuth parameters
		$oauth_params = (object) array(
			'access_token' => $access_token, 
			'api_secret' => self::$SECRET_KEY
		);
		$param_string = '';

		//Build the endpiont from the fields selected and put add it to the string.
		//foreach($params as $key=>$value) { $param_string .= $key.'='.$value.'&'; }
		foreach($oauth_params as $key=>$value) { $param_string .= $key.'='.$value.'&'; }
		$param_string = rtrim($param_string, "&");
		// setup curl to make a call to the endpoint
		$url .= $param_string;
		$session = curl_init($url);
		// indicates that we want the response back rather than just returning a "TRUE" string
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session,CURLOPT_FOLLOWLOCATION,true);
		// execute GET and get the session backs
		$results = curl_exec($session);
		// close connection
		curl_close($session);
		// show the response in the browser
		return  json_decode($results);
	}

	
	function index () {
		if (is_ajax()) {
			$comment_id = !empty($_POST['comment_id'])? $_POST['comment_id'] : null;
			$id = !empty($_POST['id'])? $_POST['id'] : null;
			$type = !empty($_POST['type'])? $_POST['type'] : null;

			$redirect = url('index', 'disqus');
			$endpoint = 'https://disqus.com/api/oauth/2.0/authorize?';
			
			$scope = 'read,write';
			$response_type = 'code';
			$auth_url = $endpoint.'&client_id='.self::$client_id.'&scope='.$scope.'&response_type='.$response_type.'&redirect_uri='.$redirect;

			$authorize = "authorization_code";
			$url = 'https://disqus.com/api/oauth/2.0/access_token/?';
			$fields = array(
				'grant_type'=>urlencode($authorize),
				'client_id'=>urlencode(self::$PUBLIC_KEY),
				'client_secret'=>urlencode(self::$SECRET_KEY),
				'redirect_uri'=>urlencode($redirect),
				'code'=>urlencode(null)
			);
			//url-ify the data for the POST
			$fields_string = '';
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string, "&");
			//open connection
			$ch = curl_init();
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_POST,count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			//execute post
			$data = curl_exec($ch);
			//close connection
			curl_close($ch);
			//turn the string into a object
			$auth_results = json_encode($data);
			$access_token = $auth_results->access_token;

			/**
			 *  get API Query
			 *  1. 取得post 資料 @require : id
			 */
			$api_endpoint = self::API_posts_details($comment_id);
			
			/**
			 *  get Data via API endpoint
			 *  1.Calling the function to getData
			 */
			$Data = self::getData($api_endpoint, self::$SECRET_KEY, $access_token);
			
			/**
			 *  處理取得的資料
			 */
			$result = self::Notification_data(json_encode($Data), $type, $id);
			
			/**
			 *  $result = [
					'case' => [   //不同case取得不同資料
						if@ album		=> 'album', user_id, user_name, album_id, album_name, creative_code
						if@ creative	=> 'creative', user_id, user_name, creative_name, creative_code
						if@ event		=> 'event', event_id, event_name
					],
					'comment' => [  //此筆評論的資料 
						'comment_id', 'comment', 'author_id', 'author_name'
					],
					'parent' => [   //此筆評論的上層資料
						'comment_id', 'comment', 'author_id', 'author_name'
					],
				]
			 */
			
			/**
			 *	Push Notification Here
			 */
			
			
			json_encode_return(1, $result);
		}
	}

	function Notification_data($data, $type, $id) {
		$return = array();
		
		//取得該頁面作者資訊
		switch($type) {
			case 'album':
				$m_album = Model('album')->column(['album.name album_name', 'album.album_id', 'album.user_id', 'user.name user_name', 'user.creative_code'])->where([[[['album_id', '=', $id]], 'and']])->join([['left join', 'user', 'using(user_id)']])->fetch();
				$case = [
					'case' => $type,
					'user_id' => $m_album['user_id'],
					'user_name' => $m_album['user_name'],
					'album_id' => $m_album['album_id'],
					'album_name' =>$m_album['album_name'],
					'creative_code' =>$m_album['creative_code'],
				];
			break;
			
			case 'creative':
				$m_user = Model('user')->where([[[['user_id', '=', $id]], 'and']])->fetch();
				$case = [
					'case' => $type,
					'user_id' => $m_user['user_id'],
					'user_name' => $m_user['name'],
					'creative_name' => $m_user['creative_name'],
					'creative_code' => $m_user['creative_code'],
				];
			break;
			
			case 'event':
				$m_event = Model('event')->where([[[['event_id', '=', $id]], 'and']])->fetch();
				$case = [
					'case' => $type,
					'event_id' => $m_event['event_id'],
					'event_name' => $m_event['name'],
				];
			break;
			
		}
		
		//取得此筆comment 
		$tmp0 = json_decode($data, true) ;
		$comment_data0 = $tmp0['response'];
		
		$return = [
			'case' => $case,		
			'comment' => [
				'comment_id' => $comment_data0['id'],
				'comment' => $comment_data0['raw_message'],
				'author_id' => $comment_data0['author']['id'],
				'author_name' => $comment_data0['author']['name'],
			],
		];
		
		//若此id回傳資料帶有上一層(parent)id, 取得parent_id 並再次送往disqus取得資料
		if( !empty($comment_data0['parent'])) {
			$api_endpoint = self::API_posts_details($comment_data0['parent']);
			$Data = self::getData($api_endpoint, self::$SECRET_KEY, $access_token);
			$tmp1 = json_decode( json_encode($Data), true) ;
			$comment_data1 = $tmp1['response'];
			
			$return['parent'] = [
				'comment_id' => $comment_data1['id'],
				'comment' => $comment_data1['raw_message'],
				'author_id' => $comment_data1['author']['id'],
				'author_name' => $comment_data1['author']['name'],
			];
		}
		
		return $return;
	}
	
}