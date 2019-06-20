<?php
namespace Core;
class AppStore {
	static function getVersion($app_id) {
		$response = json_decode(@file_get_contents('https://itunes.apple.com/lookup?id=' . $app_id), true);
		
		if ($response['resultCount']) {
			$version = $response['results'][0]['version'];
		} else {
			$response = json_decode(@file_get_contents('https://itunes.apple.com/lookup?id=' . $app_id . '&country=tw'), true);
			
			if ($response['resultCount']) {
				$version = $response['results'][0]['version'];
			}
		}
		
		$version = trim($version) === ''? null : trim($version);
		
		return $version;
	}
}