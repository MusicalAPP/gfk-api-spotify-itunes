<?php
$exasol_dsn = "odbc://FRONTEND:NHzseVLK2yGc53OWCDVz@/EXA_MUSIC";
$dsn = $exasol_dsn; 

$mysql_db_dsn = "$user_type_mysql://$user_user_mysql:$user_pass_mysql@$user_hostname_mysql/$user_db_name_mysql";
$land_ref_arr = array(
	1054 => 'DE ( Deutschland )',
	1072 => 'FR ( Frankreich )',
	1065 => 'ES ( Spanien )',
	1013 => 'AT ( Österreich )',
	1041 => 'CH ( Schweiz )',
	1105 => 'IT ( Italien )',
	1176 => 'PT ( PORTUGAL )'
	);
$charts_modul_ref = array(
	101 => 'SINGLE',
	102 => 'LONGPLAY',
	103 => 'COMPILATION',
	104 => 'DVD/VIDEO',
	105 => 'KLASSIK',
	106 => 'SCHLAGER SINGLE',
	107 => 'SCHLAGER LONGPLAY',
	109 => 'DOWNLOAD',
	170 => 'SINGLE',
	171 => 'SINGLE',
	172 => 'SINGLE',
	173 => 'SINGLE',
	174 => 'SINGLE',
	175 => 'SINGLE',
	176 => 'SINGLE',
	177 => 'SINGLE',
	178 => 'SINGLE',
	180 => 'LONGPLAY',
	181 => 'LONGPLAY',
	182 => 'LONGPLAY',
	183 => 'LONGPLAY',
	184 => 'LONGPLAY',
	185 => 'LONGPLAY',
	186 => 'LONGPLAY',
	187 => 'LONGPLAY',
	188 => 'LONGPLAY',
	190 => 'COMPILATION',
	191 => 'COMPILATION',
	192 => 'COMPILATION',
	193 => 'COMPILATION',
	194 => 'COMPILATION',
	195 => 'COMPILATION',
	196 => 'COMPILATION',
	197 => 'COMPILATION',
	198 => 'COMPILATION'
	);
$charts_modul_ref_land = array(
	101 => 1054,
	102 => 1054,
	103 => 1054,
	104 => 1054,
	105 => 1054,
	106 => 1054,
	107 => 1054,
	109 => 1054,
	170 => 1054,
	171 => 1054,
	172 => 1054,
	173 => 1054,
	174 => 1054,
	175 => 1054,
	176 => 1054,
	177 => 1054,
	178 => 1054,
	180 => 1054,
	181 => 1054,
	182 => 1054,
	183 => 1054,
	184 => 1054,
	185 => 1054,
	186 => 1054,
	187 => 1054,
	188 => 1054,
	190 => 1054,
	191 => 1054,
	192 => 1054,
	193 => 1054,
	194 => 1054,
	195 => 1054,
	196 => 1054,
	197 => 1054,
	198 => 1054
	);
$ival_ar_ref = array(
	'W' => 'WEEK',
	'T' => 'DAY',
	'M' => 'MONTH'
	);
//Exasol function

function get_exa_chart_modul_id(){
	//nur DE 101,102,103
	$ar = array(101,102,103);
	return $ar;
}
function get_exa_chart_ival($modul_id){
	// nur Wochen chart
	global $ival_ar_ref;
	return array(
		'W'=>$ival_ar_ref['W']
		);
}

function get_exa_chart_time_key($modul_id,$time_unit){
	global $dsn;
	$db = MDB2::connect($dsn);
	if (MDB2::isError($db)) {
		die ($db->getMessage());
	}
	$ar = array();
	$query = "SELECT DISTINCT to_number(th.ausw_zr_jahr || LPAD(th.ausw_zr_wmq, 2, 00)) AS ID 
			  FROM
				mcchrth th
			  WHERE
				th.land = 'DE' AND
				th.ausw_typ = 'CHART' AND
				th.frei_datumzeit_erst <= TO_CHAR(SYSTIMESTAMP, 'YYYY-MM-DD HH24:MI:SS') and
				th.ausw_zr_jahr <= to_char(sysdate, 'YYYY') and
				th.ausw_zr_jahr != 0 and
				th.modul_id = $modul_id and 
				th.ausw_zeitraum_einheit = '$time_unit'
			ORDER BY 
				to_number(th.ausw_zr_jahr || LPAD(th.ausw_zr_wmq, 2, 00)) DESC
			";

	$quid = $db->query($query);
	if(MDB2::isError($quid)) {
		die("Error in query: ".$query);
	}else{
		while($row = $quid->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$ar[]= $row['id'];
		}
		return $ar;
	}
	$db->disconnect();	
}


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

///get distinct all charts-modul-ID from mysql GE_CHART
function get_chart_modulID(){
	global $mysql_db_dsn;
	global $charts_modul_ref;
	$mysql_db = MDB2::connect($mysql_db_dsn);
	if (MDB2::isError($mysql_db)) {
		die ($mysql_db->getMessage());
	}
	$ar = array();
	$query = "SELECT distinct modul_id as id FROM `GE_CHART` WHERE 1 ";
	$quid = $mysql_db->query($query);
	if(MDB2::isError($quid)) {
		die("Error in query: ".$query);
	}else{
		while($row = $quid->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$ar[]= $row['id'];
		}
		return $ar;
	}
	$mysql_db->disconnect();
}
//get chart ival from mysql GE_CHART
function get_chart_ival($modul_id = 0){
	global $ival_ar_ref;
	global $mysql_db_dsn;
	$mysql_db = MDB2::connect($mysql_db_dsn);
	if (MDB2::isError($mysql_db)) {
		die ($mysql_db->getMessage());
	}
	$ar = array();
	$cond = ($modul_id == 0)? 1 : "modul_id = $modul_id";
	$query = "SELECT distinct time_unit FROM `GE_CHART` WHERE $cond ORDER BY time_unit desc";
	$quid = $mysql_db->query($query);
	if(MDB2::isError($quid)) {
		die("Error in query: ".$query);
	}else{
		while($row = $quid->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$ar[$row['time_unit']]= $ival_ar_ref[$row['time_unit']];
		}
		return $ar;
	}
	$mysql_db->disconnect();
}
//get chart time_key from mysql GE_CHART
function get_chart_time_key($modul_id,$time_unit){
	global $mysql_db_dsn;
	$mysql_db = MDB2::connect($mysql_db_dsn);
	if (MDB2::isError($mysql_db)) {
		die ($mysql_db->getMessage());
	}
	$ar = array();
	$query = "SELECT distinct time_key FROM `GE_CHART` WHERE modul_id = $modul_id and time_unit = '".$time_unit."' ORDER BY time_key desc";
	$quid = $mysql_db->query($query);
	if(MDB2::isError($quid)) {
		die("Error in query: ".$query);
	}else{
		while($row = $quid->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$ar[]= $row['time_key'];
		}
		return $ar;
	}
	$mysql_db->disconnect();
}
function get_max_default_key(){
	global $mysql_db_dsn;
	$mysql_db = MDB2::connect($mysql_db_dsn);
	if (MDB2::isError($mysql_db)) {
		die ($mysql_db->getMessage());
	}	
	$query = "SELECT max(TIME_KEY) as TIME_KEY FROM `GE_CHART` WHERE modul_id = 101 and time_unit = 'W'";
	$quid = $mysql_db->query($query);
	if(MDB2::isError($quid)) {
		die("Error in query: ".$query);
	}else{
		$row = $quid->fetchRow(MDB2_FETCHMODE_ASSOC);
		return $row['time_key'];
	}
	$mysql_db->disconnect();
}
function get_chart($modul_id=101,$time_unit='W',$time_key= 0){
	global $mysql_db_dsn;
	$mysql_db = MDB2::connect($mysql_db_dsn);
	if (MDB2::isError($mysql_db)) {
		die ($mysql_db->getMessage());
	}
	$time_key=($time_key==0)?get_max_default_key():$time_key;
	$ar = array();
	$query = "SELECT * FROM `GE_CHART` WHERE modul_id = $modul_id and time_unit = '".$time_unit."' and time_key = ".$time_key." ORDER BY rank asc";
	$quid = $mysql_db->query($query);
	if(MDB2::isError($quid)) {
		die("Error in query: ".$query);
	}else{
		while($row = $quid->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$ar[]= array(
				'rank'=>$row['rank'],
				'titel'=>$row['titel'],
				'artist'=>$row['artist'],
				'itunes_link'=>$row['itunes_link'],
				'amazon_phys_link'=>$row['amazon_phys_link'],
				'amazon_dwn_link'=>$row['amazon_dwn_link'],
				'video_link'=>$row['video_link']
				);
		}
		return $ar;
	}
	$mysql_db->disconnect();
}

function update_link($modul_id,$time_unit,$time_key,$rank,$feld,$value){
	global $mysql_db_dsn;
	$mysql_db = MDB2::connect($mysql_db_dsn);
	if (MDB2::isError($mysql_db)) {
		die ($mysql_db->getMessage());
	}
	$query = "UPDATE `GE_CHART` SET ".$feld."='".$value."' WHERE modul_id = ".$modul_id." and time_unit='".$time_unit."' and time_key=".$time_key." and rank = ".$rank;
	$quid = $mysql_db->query($query);
	if(MDB2::isError($quid)) {
		die("Error in query: ".$query);
	}
	return ;
	$mysql_db->disconnect();
}
?>