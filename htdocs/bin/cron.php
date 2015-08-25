<?php
error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE & ~E_WARNING);
#error_reporting(E_ALL);
/////////////////////////////////////////////////
//////////////////config/////////////////////////
/////////////////////////////////////////////////
$country_arr = array('at','ch','de','be','nl','fr','it','es','be_flanders','be_wallonia','be_brussels');
ini_set('memory_limit', '-1');
set_time_limit(0);
//ftp
$ftp_host = 'asbad04.mcbad.net';
$ftp_username = 'MC_FTP';
$ftp_password = 'linux_3018';
$ftp_target = '/NFS/ASBAD04/FTP/MediaControl/GER/DigRetailer/Spotify_API';
//email
require_once (dirname(__FILE__).'/../lib/pear/Mail.php');
require_once (dirname(__FILE__).'/../lib/pear/Mail/mime.php');
require_once (dirname(__FILE__).'/../lib/pear/MIME/Type.php');

#require_once (dirname(__FILE__).'/../lib/pear/Net/Socket.php');
#require_once (dirname(__FILE__).'/../lib/pear/Net/SMTP.php');

$reports_error_recipients = array(
    "vitus.nagel@gfk.com, manh-cuong.tran@gfk.com, Ke.Tan@gfk.com"
);
$reports_normal_recipients = array(
  "manh-cuong.tran@gfk.com, vitus.nagel@gfk.com, Ke.Tan@gfk.com, Mike.Timm@gfk.com, Thorsten.Haas@gfk.com, Michael.Hacker@gfk.com"
);

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

function get_last_update_zeitkey($lastupdatefile){
    if(is_readable($lastupdatefile)){
        $fhandle = fopen($lastupdatefile, 'r');
        if($fhandle){
            $zeitkey = fread($fhandle, 8);
        }
        fclose($fhandle);
        if(preg_match('/\d{8}/', $zeitkey)){
            return $zeitkey;
        }
    }
    return false;
}

function update_last_update_zeitkey($lastupdatefile, $zeitkey){
    $fhandle = fopen($lastupdatefile, 'w');
    $r = fwrite($fhandle, $zeitkey);
    fclose($fhandle);
    return $r;
}

function get_recursiv_zeitkey($recursiv_file){
	$r = array();
	if(is_readable($recursiv_file) && filesize($recursiv_file)!=0){
		$r = unserialize(file_get_contents($recursiv_file));
	}
	return $r;
}

function update_recursiv_zeitkey($recursiv_file,$arr){
	$recursiv_zk = serialize($arr);
	$recursiv_hl = fopen($recursiv_file,"w");
	$r = fwrite($recursiv_hl, $recursiv_zk);
	fclose($recursiv_hl);
	return $r;
}

function sendEMail($par,$file = false) {
	
		
		$recipients  	= $par['empfaenger'];		
		$message_array	= $par['message'];
				
		$from			= 'Develop.Entertainment@gfk.com';
		$backend 		= 'smtp';
		
		$subject		= $message_array['subject'];
		$body_txt		= $message_array['body_txt'];
			
		$crlf 			= "\n";
			
		$params 		= array(
						'host' 			=> '10.149.43.10',
						'port' 			=> 25,
						'auth' 			=> false,
						'username' 		=> false,
						'password' 		=> false,
						'localhost'		=> 'localhost',
						'timeout' 		=> null,
						#'verp' 			=> false,
						'debug' 		=> false
		);		
	    
	    foreach ($recipients as $recipient) {
	    	$headers 		= array(
			              	'From'    	=> $from,
			              	'To'    	=> $recipient,
			              	'Subject' 	=> $subject
		    );
		    
	    	$mime = new Mail_mime($crlf);
		
			$mime->setTXTBody($body_txt);
			if (is_file($file)) {
				$ctype = MIME_Type::autoDetect($file);
				$mime->addAttachment($file, $ctype);
			}
		
			$body = $mime->get();
			$hdrs = $mime->headers($headers);
			
			$mail =& Mail::factory($backend, $params);
			$mail->send($recipient, $hdrs, $body);
	    }		    		
}
//////////////////////////////////////////////////
////////////END Funtions declare /////////////////
//////////////////////////////////////////////////

//////////////////////////////////////////////////
////////Begin Cron for all country ///////////////
//////////////////////////////////////////////////
//open logfile
$logfile = dirname(__FILE__).'/../data/logs/logs.txt';
$handler = fopen($logfile,"a");
//open ftp
$conn_id=ftp_connect($ftp_host);
ftp_login($conn_id, $ftp_username, $ftp_password);
ftp_pasv($conn_id, true);
//fehler status
$is_error = false;

foreach ($country_arr as $land) {
	///////////////////////////////////////////////////////////
	//config zeit_key to get data from API check:            //
	//today -1, -2, -3, -4 days compare with update_zeitkey  //
	///////////////////////////////////////////////////////////
	date_default_timezone_set("Europe/Berlin");
	$heute = date("Ymd");
	$oneday = date('Ymd',strtotime($heute."-1 day"));
	$twodays = date('Ymd',strtotime($heute."-2 days"));
	$threedays = date('Ymd',strtotime($heute."-3 days"));
	$fourdays = date('Ymd',strtotime($heute."-4 days"));
	$access_date = array($fourdays, $threedays, $twodays, $oneday);
	$update_key = get_last_update_zeitkey(dirname(__FILE__).'/../data/zeitkey/'.$land.'_last_key.txt');	

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

	
	////////////get recursiv zeitkey from file////////	
	$recursiv_zeitkey = get_recursiv_zeitkey(dirname(__FILE__).'/../data/zeitkey/'.$land.'_recursiv_key.txt');

	//loop for 4 days before today
	foreach ($access_date as $getapizk) {
		//check update key
		if(($getapizk <= $update_key) && (!in_array($getapizk, $recursiv_zeitkey))){
			continue;
		}
		$country = $land;
		$year = substr($getapizk, 0,4);
		$month = substr($getapizk, 4,2);
		$day = substr($getapizk, 6);
		//////////////////////////////////////////////////
		///////////////get Chart data/////////////////////
		//////////////////////////////////////////////////
		$save_dir = dirname(__FILE__).'/../data/'.$country.'/';		
		date_default_timezone_set("Europe/Berlin");
		fwrite($handler,'cron export:--------: '.date('Y-m-d H:i:s')."-----------\n");
		//check update
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
				$par['empfaenger'] = $reports_error_recipients;
				$par['message'] = array('subject'=>"Spotify streaming Daten Error",'body_txt'=>"Bei Generierung Spotify streaming Daten (TAGESDATEN) Fehler aufgetreten:\n can't open file: ".$file ."\n");
				sendEMail($par);

				die("can't open file: ".$file ."\n");
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
				$par['empfaenger'] = $reports_error_recipients;
				$par['message'] = array('subject'=>"Spotify streaming Daten error",'body_txt'=>"Bei Generierung spotify streaming Daten (TAGESDATEN) Fehler aufgetreten:\n can't open file: ".$unc_file ."\n");
				sendEMail($par);
				die("can't open file: ".$unc_file."\n");
			}
			fwrite($fh, $contents);
			fclose($fh);
			echo "...done. \n";
			//write log
			fwrite($handler, 'unzip to '. $unc_file ."\n");
			//read unzipfile and write to csv
			$textfile = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".part";
			echo ' begin write to '.$textfile."...";
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
		/////////////end write data to file///////////////
		//////////////////////////////////////////////////
		if(filesize($textfile)>0){
			//update zeitkey
			if($getapizk > $update_key)
                        update_last_update_zeitkey(dirname(__FILE__).'/../data/zeitkey/'.$land.'_last_key.txt',$getapizk);
		//////////////////////////////////////////////////
		///////ftp transfer to asbad04.mcbad.net//////////
		//////////////////////////////////////////////////
			echo "begin ftp upload...\n";
			echo $ftp_target . '/spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".part\n";
			if( !ftp_put($conn_id, $ftp_target . '/spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".part", $textfile, FTP_BINARY)) {
	        	$is_error = true;
	        	echo "failed to upload ".$textfile." file.\n";
	        	$par['empfaenger'] = $reports_error_recipients;
				$par['message'] = array('subject'=>"Spotify streaming Daten error",'body_txt'=>"Bei Generierung Spotify streaming Daten (TAGESDATEN) Fehler aufgetreten:\n failed to upload ".$textfile ."\n");
				sendEMail($par);
	    		}else{
			sleep(5);
	    		ftp_rename($conn_id, $ftp_target . '/spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".part",$ftp_target . '/spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".txt");
	    		echo "...done\n\n\n";
	    		$par['empfaenger'] = $reports_normal_recipients;
			$par['message'] = array('subject'=>"Spotify streaming Daten Erfolg",'body_txt'=>"Spotify streaming Daten (TAGESDATEN) ".strtoupper($country).$year.$month.$day."  generiert und auf FTP hochgeladen.\n");
			sendEMail($par);
	    		//delete local files
	    		unlink($textfile);
	    		}
		//////////////////////////////////////////////////
		///////end ftp transfer to asbad04.mcbad.net//////
		//////////////////////////////////////////////////
	    		//remove recursiv_key
	    		if(in_array($getapizk, $recursiv_zeitkey)){
	    			$key = array_search($getapizk, $recursiv_zeitkey);
	    			unset($recursiv_zeitkey[$key]);
	    		}
		}else{
                    echo "filesize = 0; empty file from Spotify???\n";
                	fwrite($handler, "Spotify transfer empty file!\n");
                	//send warning to me
                	$par['empfaenger'] = array('manh-cuong.tran@gfk.com');
					$par['message'] = array('subject'=>"Spotify streaming Daten Waring",'body_txt'=>"Spotify transfer empty file!:\n country: ".$land ."\n Zeitkey: ". $getapizk ."\n check logs file for details ");
					sendEMail($par);
                	//add recursiv key
                	if(!in_array($getapizk, $recursiv_zeitkey)){
                		$recursiv_zeitkey[] = $getapizk;
                	}
		}
		}else{
			echo "no data from Spotify-WS!!!\n";
			fwrite($handler, strtoupper($country)."_".$year."_".$month."_".$day.": no data from Spotify-WS!!!\n");
			//add recursiv key
            if(!in_array($getapizk, $recursiv_zeitkey)){
                $recursiv_zeitkey[] = $getapizk;
            }
		}		
	}
	update_recursiv_zeitkey(dirname(__FILE__).'/../data/zeitkey/'.$land.'_recursiv_key.txt', $recursiv_zeitkey);
}
fclose($handler);
ftp_close($conn_id);

?>
