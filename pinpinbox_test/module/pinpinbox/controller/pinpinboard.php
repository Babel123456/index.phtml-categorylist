<?php
class pinpinboardController extends frontstageController {
	const key = 'user';
	function __construct() {}

	function addcomment()
	{
		if (is_ajax()) {
			$user = parent::user_get();

			$text = (!empty($_POST['text'])) ? nl2br($_POST['text']) : null;
			$type = (!empty($_POST['type'])) ? $_POST['type'] : null;
			$type_id = (!empty($_POST['type_id'])) ? $_POST['type_id'] : null;
			$push_notice_ids = (!empty($_POST['push_notice_ids'])) ? $_POST['push_notice_ids'] : null;

			if ($text == null || $type == null || $type_id == null || empty($user)) json_encode_return(0, _('Param error.'));
			list($result, $pinpinboard) = array_decode_return((new pinpinboardModel)->addComment($user['user_id'], $text, $type, $type_id, $push_notice_ids));
			$user_picture = (!file_exists(PATH_STORAGE . Core::get_userpicture($user['user_id']))) ? static_file('images/face_sample.svg') : URL_STORAGE . Core::get_userpicture($user['user_id']);

			if ($result) {
				$data = [
					'user' => $user,
					'pinpinboard_id' => $pinpinboard['pinpinboard_id'],
					'user_name' => $user['name'],
					'user_picture' => $user_picture,
                    'user_url' => Core::get_creative_url($user['user_id']),
					'inserttime' => $pinpinboard['inserttime'],
					'textToMention' => (new pinpinboardModel())->textToMention($text),
				];
			}

			json_encode_return($result, null, null, $data);
		}
	}

	function deleteComment()
	{
		if (is_ajax()) {
			$pinpinboard_id = (!empty($_POST['pinpinboard_id'])) ? $_POST['pinpinboard_id'] : null;
			$type = (!empty($_POST['type'])) ? $_POST['type'] : null;
			$type_id = (!empty($_POST['type_id'])) ? $_POST['type_id'] : null;
			$user = parent::user_get();

			if (is_null($pinpinboard_id) || is_null($type_id) || empty($user)) json_encode_return(0, 'Error');
			//get comment
			$where = [[[['pinpinboard_id', '=', $pinpinboard_id], ['user_id', '=', $user['user_id']], ['type', '=', $type], ['type_id', '=', $type_id], ['act', '=', 'open']], 'and']];
			$m_pinpinboard = (new pinpinboardModel)->where($where)->fetch();

			if (empty($m_pinpinboard)) json_encode_return(0, 'Error');
			$result = (new pinpinboardModel)->deleteComment($pinpinboard_id, $type_id);

			if (!$result) {
				json_encode_return(0, 'Error');
			}

			json_encode_return(1);
		}
	}

	function mention()
	{
		if (is_ajax()) {
			$searchkey = isset($_POST['searchkey']) ? $_POST['searchkey'] : null;
			$text = isset($_POST['text']) ? $_POST['text'] : null;
			$user = parent::user_get();

			if (trim($searchkey)) {
				$Solr_user = Solr('user')
					->column(['user_id'])
					->where([[[['_text_', 'like', $searchkey]], 'and']])
					->fetchAll();

				$object_user = [];
				if ($Solr_user) {
					$user_ids = array_column($Solr_user, 'user_id');

					//已被tag的id
					preg_match_all("/\[(.+?)\]/", $text, $match);
					$id = array_map(function($v){
						return substr($v, 0, strpos($v, ':'));
					}, $match[1]);

					//登入的使用者id
					$id[] = $user['user_id'];

					$listIds = array_diff($user_ids, $id);

					$array_user = (new \userModel)
						->column([
							'user_id',
							'name',
						])
						->where([[[['user_id', 'in', $listIds]], 'and']])
						->fetchAll();

					foreach ($array_user as $v_0) {

						if (!file_exists(PATH_STORAGE . userModel::getPicture($v_0['user_id']))) {
							$picture = static_file('images/face_sample.svg');
						} else {
							$picture = URL_STORAGE . userModel::getPicture($v_0['user_id']);
						}

						$object_user[] = [
							'user_id' => $v_0['user_id'],
							'name' => $v_0['name'],
							'picture' => $picture,
						];
					}
				}

				echo json_encode($object_user);
				die;
			}

			echo json_encode([]);
			die;
		}
	}
}