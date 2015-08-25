<?php 
error_reporting(0);
set_include_path(dirname(__FILE__) . '/lib/pear/');

require_once(dirname(__FILE__).'/config/html_dom_inc.php');
require_once(dirname(__FILE__).'/config/dbconfig.php');
require_once('lib/pear/MDB2.php');
$exasol_dsn = "odbc://FRONTEND:NHzseVLK2yGc53OWCDVz@/EXA_MUSIC";

$db = MDB2::connect($exasol_dsn);
if (MDB2::isError($db)) {
	die ($db->getMessage());
}
$mysql_db_dsn = "$user_type_mysql://$user_user_mysql:$user_pass_mysql@$user_hostname_mysql/$user_db_name_mysql";

function convert_zeitekey($zeitkey,$zeit_unit){
	$zeitkey = trim($zeitkey);
	switch (strtoupper($zeit_unit)) {
		case 'W':
			$week = substr($zeitkey, -2);
			$year = substr($zeitkey, 0, strlen($zeitkey) -2);
			if(substr($week, 0,1)=='0'){
				$week = substr($week, 1);
			}
			return $week .'.'. $year;
			break;
		case 'M':
			$month = substr($zeitkey, -2);
			$year = substr($zeitkey, 0, strlen($zeitkey) -2);
			if(substr($month, 0,1)=='0'){
				$month = substr($month, 1);
			}
			return $month .'.'. $year;
			break;
		case 'D':
			$day = substr($zeitkey, -2);
			$month = substr($zeitkey, 4,2);
			$year = substr($zeitkey, 0, 4);
			if(substr($day, 0,1)=='0'){
				$day = substr($day, 1);
			}
			return $day .'.'.$month.'.'.$year;
			break;
		default:
			return $zeitkey;
			break;
	}
}

function get_de_stamm_titel($code,$modul_id){
	global $db;
	switch ($modul_id) {
		case 101:
			$sql = "SELECT stamm_titel 
				FROM stamm_gesamt 
				WHERE stamm_landid = 1054 
				AND stamm_archivnr = '".substr($code,0,strlen($code)-1)."' 
				AND stamm_ag_main_format = 'S' 
				AND stamm_mainprod_header = 1";
			
			$res = $db->query($sql);	
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			if(!empty($row)){
				return $row['stamm_titel'];
			}
			return '';   
			
			break;
		case 102:
		case 103:			
		default:
			$sql = "SELECT stamm_titel 
				FROM stamm_gesamt 
				WHERE stamm_landid = 1054 
				AND stamm_archivnr = '".$code."' 
				AND stamm_ag_main_format = 'L' 
				AND stamm_mainprod_header = 1";			
			$res = $db->query($sql);
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			if(!empty($row)){
				return $row['stamm_titel'];
			}
			return ''; 
			break;
	}	
}

function get_de_stamm_artist($code,$modul_id){
	global $db;
	switch ($modul_id) {
		case 101:
			$sql = "SELECT stamm_artist 
				FROM stamm_gesamt 
				WHERE stamm_landid = 1054 
				AND stamm_archivnr = '".substr($code,0,strlen($code)-1)."' 
				AND stamm_ag_main_format = 'S' 
				AND stamm_mainprod_header = 1";
			
			$res = $db->query($sql);	
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			if(!empty($row)){
				return $row['stamm_artist'];
			}
			return '';   
			
			break;
		case 102:
		case 103:			
		default:
			$sql = "SELECT stamm_artist 
				FROM stamm_gesamt 
				WHERE stamm_landid = 1054 
				AND stamm_archivnr = '".$code."' 
				AND stamm_ag_main_format = 'L' 
				AND stamm_mainprod_header = 1";			
			$res = $db->query($sql);
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			if(!empty($row)){
				return $row['stamm_artist'];
			}
			return ''; 
			break;
	}	
}

function write_chart_to_mysql($ar,$landid){
	global $mysql_db_dsn;	
	$mysql_db = MDB2::connect($mysql_db_dsn);
	if (MDB2::isError($mysql_db)) {
	    die ($mysql_db->getMessage());
	}

	if(is_array($ar)){
		if(empty($ar)){
			$mysql_db->disconnect();
			return false;
		}else{
			echo "begin insert chart to mysql...";
			foreach ($ar as $value) {
				$query = "INSERT INTO `GE_CHART`(`LAND`, `MODUL_ID`, `RANK`, `TITEL`, `ARTIST`, `TIME_KEY`, `TIME_UNIT`, `RANK_W_1`, `RANK_W_2`, `ITUNES_LINK`, `AMAZON_PHYS_LINK`, `AMAZON_DWN_LINK`, `VIDEO_LINK`) VALUES (".$landid.",".$value['MODUL_ID'].",".$value['RANK'].",'".str_replace("'", "\'", $value['TITEL'])."','".str_replace("'", "\'", $value['ARTIST'])."',".$value['TIME_KEY'].",'".$value['TIME_UNIT']."',".$value['RANK_W_1'].",".$value['RANK_W_2'].",'".$value['ITUNES_LINK']."','".$value['AMAZON_PHYS_LINK']."','".$value['AMAZON_DWN_LINK']."','".$value['VIDEO_LINK']."')";
				$quid = $mysql_db->query($query);
				if(MDB2::isError($quid)) {
			        die("Error in query: ".$query);
				}
			}
			echo "done \n";
		}
		$mysql_db->disconnect();
		return true;
	}else{
		$mysql_db->disconnect();
		return false;
	}
}

function update_chart_to_mysql($ar,$landid){
	global $mysql_db_dsn;	
	$mysql_db = MDB2::connect($mysql_db_dsn);
	if (MDB2::isError($mysql_db)) {
	    die ($mysql_db->getMessage());
	}

	if(is_array($ar)){
		if(empty($ar)){
			$mysql_db->disconnect();
			return false;
		}else{
			echo "begin update chart to mysql...";
			foreach ($ar as $value) {
				$query = "UPDATE `GE_CHART` SET `TITEL`='".str_replace("'", "\'", $value['TITEL'])."',`ARTIST`='".str_replace("'", "\'", $value['ARTIST'])."',`RANK_W_1`=".$value['RANK_W_1'].",`RANK_W_2`=".$value['RANK_W_2'].",`ITUNES_LINK`='".$value['ITUNES_LINK']."',`AMAZON_PHYS_LINK`='".$value['AMAZON_PHYS_LINK']."',`AMAZON_DWN_LINK`='".$value['AMAZON_DWN_LINK']."',`VIDEO_LINK`='".$value['VIDEO_LINK']."' 
						  WHERE LAND = $landid and MODUL_ID = ".$value['MODUL_ID']." and RANK = ".$value['RANK'] ." and TIME_KEY = ".$value['TIME_KEY'] ." and TIME_UNIT = '".$value['TIME_UNIT']."'";
				$quid = $mysql_db->query($query);
				if(MDB2::isError($quid)) {
			        die("Error in query: ".$query);
				}
			}
			echo "done \n";	
		}
		$mysql_db->disconnect();
		return true;
	}else{
		$mysql_db->disconnect();
		return false;
	}
}
// get Chart from exasol: return multiple array 
function get_chart_from_exasol($top,$modul_id,$zeikey,$zeit_unit){
	//modul_id = 101 : Single
	//modul_id = 102 : Longplay
	//modul_id = 103 : Compilation
	echo "get chart from Exasol: top $top , modul $modul_id , $zeit_unit $zeikey ...";
	$format = ($modul_id == 101)? 'SONG' : 'ALBUM';
	global $db;
	$ar = array();

	$sql = "SELECT
		NVL(Platzierung, 0) AS pnposak,
		NVL(Platz_Zeitraum_1, ' ') AS pnposvv,
		NVL(Platz_Zeitraum_2, ' ') AS pnposww,
		NVL(Autor, ' ') AS pninterp,
		NVL(Titel, ' ') AS pntitel,
		NVL(code, ' ') AS Code
	FROM
		(
			SELECT
				TO_NUMBER(Platzierung) AS Platzierung,
				m2.Platz_Zeitraum_1,
				m2.Platz_Zeitraum_2,
				m2.Autor,
				m2.Titel,
				m2.code
			FROM
				mcchart m2,
				mcchrth h2
			WHERE
				h2.Modul_Id = $modul_id AND
				h2.Ausw_Zeitraum = '".convert_zeitekey($zeikey,$zeit_unit)."' AND
				h2.Ausw_Zeitraum_Einheit = '".$zeit_unit."' AND
				m2.referenz = h2.referenz
			ORDER BY
				1
		)
	WHERE
		Platzierung <= $top";

	$res = $db->query($sql);
	echo "done \n";
	if($modul_id == 101){
		$itunes_top100 			= get_itunes_top100_song();
		$amazon_top100_phys 	= get_amazon_top100_phys_song();
		$amazon_top100_dwn 		= get_amazon_top100_dwn_song();
	}else{
		$itunes_top100 			= get_itunes_top100_album();	
		$amazon_top100_phys 	= get_amazon_top100_phys_album();		
		$amazon_top100_dwn 		= get_amazon_top100_dwn_album();
	}
	
	while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){	
	 	$stamm_titel = 	get_de_stamm_titel(trim($row['code']),$modul_id);
	 	$stamm_artist = get_de_stamm_artist(trim($row['code']),$modul_id);
		$ar[]=array(
			'RANK' 		=> $row['pnposak'],
			'RANK_W_1' 	=> $row['pnposvv'],
			'RANK_W_2' 	=> $row['pnposww'],
			'TITEL' 	=> $stamm_titel,
			'ARTIST' 	=> $stamm_artist,
			'ITUNES_LINK' 			=> get_itunes_links($stamm_titel,$stamm_artist,$itunes_top100),
			'AMAZON_PHYS_LINK' 		=> get_amazon_links($stamm_titel,$stamm_artist,$amazon_top100_phys),
			'AMAZON_DWN_LINK' 		=> get_amazon_links($stamm_titel,$stamm_artist,$amazon_top100_dwn),
			'VIDEO_LINK'			=> '',
			'MODUL_ID'  => $modul_id,
			'TIME_KEY'	=> $zeikey,
			'TIME_UNIT' => $zeit_unit
			);
	}	
	return $ar;
}


function convert_to_full_text($string_input){
	$new_string = strtoupper($string_input);
	
	$new_string = str_replace('È', 'E', $new_string);
	$new_string = str_replace('É', 'E', $new_string);
	$new_string = str_replace('Ä', 'AE', $new_string);
	$new_string = str_replace('Ö', 'OE', $new_string);
	$new_string = str_replace('Ü', 'UE', $new_string);
	$new_string = str_replace('ß', 'SS', $new_string);
	$new_string = preg_replace("/[''.,#!$^&*;:{}=_`~()-]/", ' ', $new_string);
	$new_string = preg_replace("{///}", ' ', $new_string);
	$new_string = preg_replace("{\\\\}", ' ', $new_string);
	$new_string = preg_replace('/\s+/', ' ',$new_string);	
	return trim($new_string);
}

function compare_two_string($string1,$string2,$genauigkeit_prozent){
	
	//split string into array 	
	$arr1 = explode(" ", convert_to_full_text($string1));
	$arr2 = explode(" ", convert_to_full_text($string2));
	$prozent = 0;
	$length1 = count($arr1);
	$length2 = count($arr2);
	if($length1 == 0 || $length2 == 0 ){
		return false;
	}
	foreach ($arr1 as $value) {
		if(in_array($value, $arr2)){			
			$prozent += 100/$length1;//increase genauigkeit prozent
		}
		unset($arr2[array_search($value,$arr2)]);//remove items from array2
	}
	if($prozent >= $genauigkeit_prozent){
		return true;
	}

	//opposite direction
	$prozent = 0;
	$arr1 = explode(" ", convert_to_full_text($string1));
	$arr2 = explode(" ", convert_to_full_text($string2));

	foreach ($arr2 as $value) {
		if(in_array($value, $arr1)){
			$prozent += 100/$length2;
		}
		unset($arr1[array_search($value,$arr1)]);//remove items from array1
	}
	if($prozent >= $genauigkeit_prozent){
		return true;
	}
	return false;

}

function get_itunes_top100_album(){
	echo "get itunes top 100 album ...";
	$ar = array();
	$html = get_data("https://itunes.apple.com/de/rss/topalbums/limit=100/explicit=true/xml","ITUNES");
	try{
        $arx = new SimpleXMLElement($html);
    }catch(Exception $e){
    	return "";
    }
    $namespaces = $arx->getNameSpaces( true );
    foreach ( $arx->entry as $a) {
        $im = $a->children( $namespaces['im'] );
        $ar[]=array('title' => (string)$im->name,'artist' => (string)$im->artist,'id'=> (string)$a->id ) ;
    }
    echo "done \n";
    return $ar;
}

function get_itunes_top100_song(){
	echo "get itunes top 100 song ...";
	$ar = array();
	$html = get_data("https://itunes.apple.com/de/rss/topsongs/limit=100/explicit=true/xml","ITUNES");
	try{
        $arx = new SimpleXMLElement($html);
    }catch(Exception $e){
    	return "";
    }
    $namespaces = $arx->getNameSpaces( true );
    foreach ( $arx->entry as $a) {
        $im = $a->children( $namespaces['im'] );
        $ar[]=array('title' => (string)$im->name,'artist' => (string)$im->artist,'id'=> (string)$a->id ) ;
    }
    echo "done\n";
    return $ar;
}

function get_itunes_links($title,$artist,$ar){	
    // search titel and artist and give id back else return ""
    foreach ($ar as $value) {
    	if(compare_two_string($title,$value['title'],100) && compare_two_string($artist,$value['artist'],80)){
    		return $value['id'];
    	}
    }

    return "";
    
}

function get_amazon_top100_phys_song(){
	echo "get amazon top 100 physical song ...";
	$ar = array();
	$url = "http://www.amazon.de/gp/bestsellers/music/379141011";
	$provider = "AMAZON";
	for($j = 1; $j<=5; $j++){
                //loop through pages (20 items / page => top 100 : 5 pages)
                //get fisrt 3 items of page
                $html = get_data($url,$provider,$j,1);
                if(empty($html)){                            
                   continue;
                }
                $html_object = str_get_html($html);
                $play=$html_object->find("div.zg_itemImmersion");
                         
                foreach ( $play as $pl ) {
                    $cnt=intval(str_replace('.', '', $pl->find('div.zg_rankDiv span.zg_rankNumber',0)->plaintext));
                    $ar[$cnt]['titel'] =  $pl->find('div.zg_itemWrapper div.zg_title',0)->plaintext;
                    $ar[$cnt]['link'] = trim((string)$pl->find('div.zg_itemWrapper div.zg_title a',0)->href);
                    $ar[$cnt]['artist'] = substr(trim($pl->find('div.zg_itemWrapper div.zg_byline',0)->plaintext),3);
                }
                //get the rest(17 items) of page
                $html = get_data($url,$provider,$j,0);
                if(empty($html)){
                	continue ;
                }
                $html_object = str_get_html($html);
                $play=$html_object->find("div.zg_itemImmersion");
                
                foreach ( $play as $pl ) {
                    $cnt=intval(str_replace('.', '', $pl->find('div.zg_rankDiv span.zg_rankNumber',0)->plaintext));
                    $ar[$cnt]['titel'] =  $pl->find('div.zg_itemWrapper div.zg_title',0)->plaintext;
                    $ar[$cnt]['link'] = trim((string)$pl->find('div.zg_itemWrapper div.zg_title a',0)->href);
                    $ar[$cnt]['artist'] =  substr(trim($pl->find('div.zg_itemWrapper div.zg_byline',0)->plaintext),3);
                } 
                             
            }
            echo "done\n";
            return $ar;
}
function get_amazon_top100_phys_album(){
	echo "get amazon top 100 physical album ...";
	$ar = array();
	$url = "http://www.amazon.de/gp/bestsellers/music";
	$provider = "AMAZON";
			for($j = 1; $j<=5; $j++){
                //loop through pages (20 items / page => top 100 : 5 pages)
                //get fisrt 3 items of page
                $html = get_data($url,$provider,$j,1);
                if(empty($html)){                            
                   continue;
                }
                $html_object = str_get_html($html);
                $play=$html_object->find("div.zg_itemImmersion");
                         
                foreach ( $play as $pl ) {
                    $cnt=intval(str_replace('.', '', $pl->find('div.zg_rankDiv span.zg_rankNumber',0)->plaintext));
                    $ar[$cnt]['titel'] =  $pl->find('div.zg_itemWrapper div.zg_title',0)->plaintext;
                    $ar[$cnt]['link'] = trim((string)$pl->find('div.zg_itemWrapper div.zg_title a',0)->href);
                    $ar[$cnt]['artist'] = substr(trim($pl->find('div.zg_itemWrapper div.zg_byline',0)->plaintext),3);
                }
                //get the rest(17 items) of page
                $html = get_data($url,$provider,$j,0);
                if(empty($html)){
                	continue ;
                }
                $html_object = str_get_html($html);
                $play=$html_object->find("div.zg_itemImmersion");
                
                foreach ( $play as $pl ) {
                    $cnt=intval(str_replace('.', '', $pl->find('div.zg_rankDiv span.zg_rankNumber',0)->plaintext));
                    $ar[$cnt]['titel'] =  $pl->find('div.zg_itemWrapper div.zg_title',0)->plaintext;
                    $ar[$cnt]['link'] = trim((string)$pl->find('div.zg_itemWrapper div.zg_title a',0)->href);
                    $ar[$cnt]['artist'] =  substr(trim($pl->find('div.zg_itemWrapper div.zg_byline',0)->plaintext),3);
                } 
                             
            }
            echo "done\n";
            return $ar;
}

function get_amazon_top100_dwn_song(){
	echo "get amazon top 100 download song ...";
	$ar=array();
	$url = "http://www.amazon.de/gp/bestsellers/dmusic/digital-music-track";
	$provider = "AMAZON";
			for($j = 1; $j<=5; $j++){
                //loop through pages (20 items / page => top 100 : 5 pages)
                //get fisrt 3 items of page
                $html = get_data($url,$provider,$j,1);
                if(empty($html)){                            
                   continue;
                }
                $html_object = str_get_html($html);
                $play=$html_object->find("div.zg_itemImmersion");
                         
                foreach ( $play as $pl ) {
                    $cnt=intval(str_replace('.', '', $pl->find('div.zg_rankDiv span.zg_rankNumber',0)->plaintext));
                    $ar[$cnt]['titel'] =  $pl->find('div.zg_itemWrapper div.zg_title',0)->plaintext;
                    $ar[$cnt]['link'] = trim((string)$pl->find('div.zg_itemWrapper div.zg_title a',0)->href);
                    $str_tmp = explode('|', $pl->find('div.zg_itemWrapper div.zg_byline',0)->plaintext);
                    $ar[$cnt]['artist'] =  trim($str_tmp[0]);
                }
                //get the rest(17 items) of page
                $html = get_data($url,$provider,$j,0);
                if(empty($html)){
                	continue ;
                }
                $html_object = str_get_html($html);
                $play=$html_object->find("div.zg_itemImmersion");
                
                foreach ( $play as $pl ) {
                    $cnt=intval(str_replace('.', '', $pl->find('div.zg_rankDiv span.zg_rankNumber',0)->plaintext));
                    $ar[$cnt]['titel'] =  $pl->find('div.zg_itemWrapper div.zg_title',0)->plaintext;
                    $ar[$cnt]['link'] = trim((string)$pl->find('div.zg_itemWrapper div.zg_title a',0)->href);
                    $str_tmp = explode('|', $pl->find('div.zg_itemWrapper div.zg_byline',0)->plaintext);
                    $ar[$cnt]['artist'] =  trim($str_tmp[0]);
                } 
                             
            }
            echo "done\n";
            return $ar;
}

function get_amazon_top100_dwn_album(){
	echo "get amazon top 100 download album ...";
	$ar = array();
	$url = "http://www.amazon.de/gp/bestsellers/dmusic/digital-music-album";
	$provider = "AMAZON";
			for($j = 1; $j<=5; $j++){
                //loop through pages (20 items / page => top 100 : 5 pages)
                //get fisrt 3 items of page
                $html = get_data($url,$provider,$j,1);
                if(empty($html)){                            
                   continue;
                }
                $html_object = str_get_html($html);
                $play=$html_object->find("div.zg_itemImmersion");
                         
                foreach ( $play as $pl ) {
                    $cnt=intval(str_replace('.', '', $pl->find('div.zg_rankDiv span.zg_rankNumber',0)->plaintext));
                    $ar[$cnt]['titel'] =  $pl->find('div.zg_itemWrapper div.zg_title',0)->plaintext;
                    $ar[$cnt]['link'] = trim((string)$pl->find('div.zg_itemWrapper div.zg_title a',0)->href);
                    $str_tmp = explode('|', $pl->find('div.zg_itemWrapper div.zg_byline',0)->plaintext);
                    $ar[$cnt]['artist'] =  trim($str_tmp[0]);
                }
                //get the rest(17 items) of page
                $html = get_data($url,$provider,$j,0);
                if(empty($html)){
                	continue ;
                }
                $html_object = str_get_html($html);
                $play=$html_object->find("div.zg_itemImmersion");
                
                foreach ( $play as $pl ) {
                    $cnt=intval(str_replace('.', '', $pl->find('div.zg_rankDiv span.zg_rankNumber',0)->plaintext));
                    $ar[$cnt]['titel'] =  $pl->find('div.zg_itemWrapper div.zg_title',0)->plaintext;
                    $ar[$cnt]['link'] = trim((string)$pl->find('div.zg_itemWrapper div.zg_title a',0)->href);
                    $str_tmp = explode('|', $pl->find('div.zg_itemWrapper div.zg_byline',0)->plaintext);
                    $ar[$cnt]['artist'] =  trim($str_tmp[0]);
                } 
                             
            }
            echo "done\n";
        return $ar;
}

function get_amazon_links($title,$artist,$ar){
	foreach ($ar as $value) {
		if(compare_two_string($title,$value['titel'],100) && compare_two_string($artist,$value['artist'],80)){
		    return $value['link'];
		}
	}
	return "";
}

/////////////////////////////////Main action//////////////////////////////////////

if(!isset($_REQUEST['action']) || !isset($_REQUEST['modulID']) || !isset($_REQUEST['timeunit']) || !isset($_REQUEST['timekey']) || !isset($_REQUEST['limit']) ) {
	echo 'invalid parameters';
}else{
	if($_REQUEST['action']=='insert'){
		write_chart_to_mysql(get_chart_from_exasol($_REQUEST['limit'],$_REQUEST['modulID'],$_REQUEST['timekey'],$_REQUEST['timeunit']),1054);
	}elseif($_REQUEST['action']=='update'){
		update_chart_to_mysql(get_chart_from_exasol($_REQUEST['limit'],$_REQUEST['modulID'],$_REQUEST['timekey'],$_REQUEST['timeunit']),1054);
	}	
}

$db->disconnect();
?>