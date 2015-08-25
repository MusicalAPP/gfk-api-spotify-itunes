<?php 
#error_reporting(E_ALL);
//ftp
$ftp_host = 'asbad04.mcbad.net';
$ftp_username = 'MC_FTP';
$ftp_password = 'linux_3018';
$proxy = 'proxy.media-control.int:8080';
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
function getTokenSpotify(){
	//////////////////////////////////////////////////
	////////////Get token from spotify////////////////
	//////////////////////////////////////////////////
	$proxy = 'proxy.media-control.int:8080';
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
	curl_setopt($ch, CURLOPT_PROXY, $proxy);
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
	return $token;
	//////////////////////////////////////////////////
	//////////END Get token from spotify//////////////
	//////////////////////////////////////////////////
}

function writelogs($log_txt){
	echo '<script>';								
	echo "$('#spotifyLogs').val('".$log_txt."');";
	echo '</script>';
}

function writelogsitunes($log_txt){
	echo '<script>';								
	echo "$('#itunesLogs').val('".$log_txt."');";
	echo '</script>';
}

function writelogsbokbasen($log_txt){
    echo '<script>';
    echo "$('#bokbasenLogs').val('".$log_txt."');";
    echo '</script>';
}

function rmdirr($dirname) {
    // Sanity check
    if (!file_exists($dirname)) {
        return false;
    }

    // Simple delete for a file
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }

    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Recurse
        rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
    }

    // Clean up
    $dir->close();
    return rmdir($dirname);
}

function create_zip($files = array(), $destination = '',$overwrite = false) {
	//if the zip file already exists and overwrite is false, return false
	if(file_exists($destination) && !$overwrite) { return false; }
	//vars
	$valid_files = array();
	//if files were passed in...
	if(is_array($files)) {
		//cycle through each file
		foreach($files as $file) {
			//make sure the file exists
			if(file_exists($file['abs_path'])) {
				$valid_files[] = $file;
			}
		}
	}
	//if we have good files...
	if(count($valid_files)) {
		//create the archive
		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			return false;
		}
		//add the files
		foreach($valid_files as $file) {
			$zip->addFile($file['abs_path'],$file['rel_path']);
		}
		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

		//close the zip -- done!
		$zip->close();

		//check to make sure the file exists
		return file_exists($destination);
	}
	else
	{
		return false;
	}
}

function flush_buffers(){
    ob_end_flush();
    ob_flush();
    flush();
    ob_start();
} 

function get_bokbasen_next_token($file){
	$r = '';
	if(is_readable($file) && filesize($file)!=0){
		$r = file_get_contents($file);
	}
	return $r;
}
function update_bokbasen_next_token($file,$token){
	$h = fopen($file,"w");
	$r = fwrite($h, $token);
	fclose($h);
	return $r;
}
function itunesGetFilenameFromString($name){
	switch($name){
          case 'EUR':
            return 'eur_master_trans_';
            break;
          case 'APAC':
            return 'apac_master_trans_';
            break;
          case 'eur-applemusic':
            return 'streaming_eur_master_';
            break;
          case 'apac-applemusic':
            return 'streaming_apac_master_';
            break;
          default:
            return strtolower($name);
          break;
        }
}
//////////////////////////////////////////////////
////////////END Funtions declare /////////////////
//////////////////////////////////////////////////



?>