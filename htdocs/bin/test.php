<?php 
$url = 'https://ws.spotify.com/oauth/token';
$fields = array(
	'grant_type'=>'client_credentials',
	'client_id'=>'gfk',
	'client_secret'=>'k1MvRKCA887Jf3hs'
	);
$fields_string = '';
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
$fields_string=rtrim($fields_string, '&');

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);
$j_arr = json_decode($result,true);
$token = $j_arr['access_token'];


///////////////////
$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://ws.spotify.com/analytics/chart/be_brussels/2014/07/06?oauth_token=".$token);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($ch);
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

echo $http_status ."\n";
?>