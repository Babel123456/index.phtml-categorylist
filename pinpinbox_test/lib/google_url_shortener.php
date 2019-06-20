<?php
class google_url_shortener {
	function short($longUrl) {
		$postData = array('longUrl' => $longUrl, 'key' => Core::settings('GOOGLE_API_KEY'));
		$jsonData = json_encode($postData);
		
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
		
		$response = curl_exec($curl);
		
		// Change the response json string to object
		$json = json_decode($response);
		
		curl_close($curl);
		
		return $json->id;
	}
}