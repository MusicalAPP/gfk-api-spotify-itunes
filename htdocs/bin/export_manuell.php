<?php
$country_arr = array('at','ch','de','be','nl','fr','it','es','be_flanders','be_wallonia','be_brussels',);
ini_set('memory_limit', '-1');
//////////////////////////////////////////////
//get and check input from php command line //
//////////////////////////////////////////////
if(isset($argv[1])){
	$country = strtolower($argv[1]);
	if(!in_array($country, $country_arr)){
		die("country muss in those countries: \n at, ch, de, be, nl, fr, it, es, be_flanders, be_wallonia, be_brussels\n");
	}
}else{
	die("false parametes from command line, Syntax example: \n php export_manuell.php AT 2013 09 17\n");
}
if(isset($argv[2])){
	$year = $argv[2];
	if(!preg_match('/^[2][0-9]{3}$/',$year)){
		die("false year, muss between [2000 .. 2999]\n");
	}
}else{
	die("false parametes from command line, Syntax example: \n php export_manuell.php AT 2013 09 17\n");
}
if(isset($argv[3])){
	$month = $argv[3];
	if(!preg_match('/^(0?[1-9]|1[012])$/', $month)){
		die("false month, muss between [01 .. 12]\n");
	}
}else{
	die("false parametes from command line, Syntax example: \n php export_manuell.php AT 2013 09 17\n");
}
if(isset($argv[4])){
	$day = $argv[4];
	if(!preg_match('/^(0[1-9]|1[0-9]|2[0-9]|3[01])$/', $day)){
		die("false day, muss between [01 .. 31]\n");
	}
}else{
	die("false parametes from command line, Syntax example: \n php export_manuell.php AT 2013 09 17\n");
}
if(isset($argv[5])){
	die("false parametes from command line, Syntax example: \n php export_manuell.php AT 2013 09 17\n");
}
//////////////////////////////////////////////////
//END get and check input from php command line //
//////////////////////////////////////////////////

//////////////////////////////////////////////////
///////////////Funtions declare //////////////////
//////////////////////////////////////////////////
function fwritecsv($handle, $fields, $transaction_date, $delimiter = "\t") {
    # Check if $fields is an array
    if (!is_array($fields) || empty($fields)) {
        return false;
    }    
    # Combine the data array with $delimiter and write it to the file
    $line = $transaction_date.$delimiter. implode($delimiter, $fields) . "\n";
    fwrite($handle, $line);
    # Return the length of the written data
    return strlen($line);
}
function sort_json_arr($arr){
	$temp = array();
	if(!empty($arr)){		
		$temp['total_streams'] 		= isset($arr['total_streams'])?$arr['total_streams']:'';
		$temp['ad_streams'] 		= isset($arr['ad_streams'])?$arr['ad_streams']:'';
		$temp['premium_streams'] 	= isset($arr['premium_streams'])?$arr['premium_streams']:'';
		$temp['track_name'] 		= isset($arr['track_name'])?$arr['track_name']:'';
		$temp['isrc'] 				= isset($arr['isrc'])?$arr['isrc']:'';
		$temp['album_code'] 		= isset($arr['album_code'])?$arr['album_code']:'';
		$temp['artists'] 			= isset($arr['artists'])?$arr['artists']:'';
		$temp['licensor'] 			= isset($arr['licensor'])?$arr['licensor']:'';
		$temp['label'] 				= isset($arr['label'])?$arr['label']:'';
		$temp['release_date'] 		= isset($arr['release_date'])?$arr['release_date']:'';
		$temp['capped_streams'] 	= isset($arr['capped_streams'])?$arr['capped_streams']:'';
		//dmca_radio_streams gibt nicht mehr auf API V.2
		//$temp['dmca_radio_streams'] = isset($arr['dmca_radio_streams'])?$arr['dmca_radio_streams']:'';
		//The composers, authors or lyricist of the work(API V.2)
		#$temp['composers'] 			= isset($arr['composers'])?$arr['composers']:'';
		//Spotify Track URI of the track(API V.2)
		#$temp['uri'] 				= isset($arr['uri'])?$arr['uri']:'';
		//The number of unique listeners of the track during the period of the chart(API V.2)
		#$temp['unique_listeners'] 	= isset($arr['unique_listeners'])?$arr['unique_listeners']:'';
	}
	return $temp;
}
//////////////////////////////////////////////////
////////////END Funtions declare /////////////////
//////////////////////////////////////////////////

//////////////////////////////////////////////////
////////////Get token from spotify////////////////
//////////////////////////////////////////////////
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
//////////////////////////////////////////////////
//////////END Get token from spotify//////////////
//////////////////////////////////////////////////

//////////////////////////////////////////////////
///////////////get Chart data/////////////////////
//////////////////////////////////////////////////
$save_dir = dirname(__FILE__).'/../data/manuell/'.$country.'/';
$logfile = dirname(__FILE__).'/../logs/logs.txt';
$handler = fopen($logfile,"a");
date_default_timezone_set("Europe/Berlin");
fwrite($handler,'manuell export:--------: '.date('Y-m-d H:i:s')."-----------\n");
//check data exist
if(file_exists($save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.'.txt')){
	fwrite($handler,"Data exist in ".$save_dir." => not prcessing\n");
	fclose($handler);
	die(print_r("Data exist in ".$save_dir." => not prcessing\n"));
}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://ws.spotify.com/analytics/chart/".$country."/".$year."/".$month."/".$day."?oauth_token=".$token);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($ch);
	curl_close($ch);
//////////////////////////////////////////////////
/////////////END get Chart data///////////////////
//////////////////////////////////////////////////

//////////////////////////////////////////////////
/////////////write data to file///////////////////
//////////////////////////////////////////////////
if($data){
	$file = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.'.gz';
	$fh = fopen($file, 'w');
	if(!$fh){
		fwrite($handler,"can not open ".$file."\n");
		die("can't open file: ".$file);
	}
	fwrite($fh, $data);
	fclose($fh);
	echo $file ." created \n";
	fwrite($handler,$file ." created\n");
	#---------------uncompress gzip-----------------
	echo $file ." uncompressing...";	
	//get file size
	$gzip=file_get_contents($file);
	$rest = substr($gzip, -4);
	error_reporting(0); 
	$GZFileSize = end(unpack("V", $rest));
	//---------
	$zd = gzopen($file, "r");
	$contents = gzread($zd, $GZFileSize);
	gzclose($zd);
	// save unzip file
	$unc_file = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day;

	$fh = fopen($unc_file, 'w');
	if(!$fh){
		fwrite($handler,"can not open ".$unc_file."\n");
		die("can't open file: ".$unc_file);
	}
	fwrite($fh, $contents);
	fclose($fh);
	echo "done. \n";
	//write log
	fwrite($handler, 'unzip to '. $unc_file ."\n");
	//read unzipfile and write to csv
	$textfile = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".txt";
	echo ' begin write to '.$textfile.'...';
	$n_file = fopen($unc_file,'r');
	$d_file = fopen($textfile,'w');
	while (!feof($n_file)){
		$string_content = fgets($n_file);
		$js_str = substr($string_content, strpos($string_content, '{'));
		$line_decode = json_decode($js_str,true);
		$transaction_date = $year.'-'.$month.'-'.$day;
		$sorting_data=sort_json_arr($line_decode);
		fwritecsv($d_file, $sorting_data, $transaction_date, "\t");
	}
	fclose($n_file);
	fclose($d_file);
	echo "...done \n";
	fwrite($handler, "get json and write to ".$textfile."\n");
	//delete gzip and uncompress file
	unlink($unc_file);
	echo "deleted uncompress file\n";
	unlink($file);	
	echo "deleted gzip file\n";
	echo "success!!!\n";
//////////////////////////////////////////////////
/////////////write data to file///////////////////
//////////////////////////////////////////////////
}else{
	echo "no data from Spotify-WS!!!\n";
	fwrite($handler, strtoupper($country)."_".$year."_".$month."_".$day.": no data from Spotify-WS!!!\n");
}
fclose($handler);
?>
