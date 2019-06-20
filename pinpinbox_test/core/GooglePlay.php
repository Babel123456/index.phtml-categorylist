<?php
namespace Core;
class GooglePlay {
	static function getVersion($app_id) {
		$response = @file_get_contents('https://androidquery.appspot.com/api/market?app=' . $app_id);
		
		if ($response) {
			$version = json_decode($response, true)['version'];
		} else {
			$response = @file_get_contents('https://play.google.com/store/apps/details?id=' . $app_id);
			
			if ($response) {
				$doc = new \DOMDocument();
					
				$internalErrors = libxml_use_internal_errors(true);//參考 http://stackoverflow.com/questions/1685277/warning-domdocumentloadhtml-htmlparseentityref-expecting-in-entity
					
				$doc->loadHTML($response);
					
				libxml_use_internal_errors($internalErrors);
					
				$xpath = new \DOMXPath($doc);
					
				$version = $xpath->query('//div[@itemprop="softwareVersion"]')->item(0)->textContent;
			}
		}
		
		$version = trim($version) === ''? null : trim($version);
		
		return $version;
	}
}