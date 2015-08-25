<?php
require_once('config/config.php');
$curr_dir = dirname(__FILE__);
$arr = scandir($curr_dir);
echo '<pre>';print_r($arr);
/*
foreach ($arr as $value) {
   if(substr($value, 0, 4) == '1400'){
    rmdirr($value);
   }
}
*/
phpinfo();
die();
	error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
	set_time_limit(0);

require_once (dirname(__FILE__).'/lib/pear/Mail.php');
require_once (dirname(__FILE__).'/lib/pear/Mail/mime.php');
require_once (dirname(__FILE__).'/lib/pear/MIME/Type.php');

#require_once (dirname(__FILE__).'/lib/pear/Net/Socket.php');
#require_once (dirname(__FILE__).'/lib/pear/Net/SMTP.php');

$reports_normal_recipients = array(
  "manh-cuong.tran@gfk.com"
);

function sendEMail($par,$file = false) {
    
        
        $recipients     = $par['empfaenger'];       
        $message_array  = $par['message'];
                
        $from           = 'Develop.Entertainment@gfk.com';
        $backend        = 'smtp';
        
        $subject        = $message_array['subject'];
        $body_txt       = $message_array['body_txt'];
            
        $crlf           = "\n";
            
        $params         = array(
                        'host'          => '10.149.43.10',
                        'port'          => 25,
                        'auth'          => false,
                        'username'      => false,
                        'password'      => false,
                        'localhost'     => 'localhost',
                        'timeout'       => null,
                        #'verp'             => false,
                        'debug'         => false
        );      
        
        foreach ($recipients as $recipient) {
            $headers        = array(
                            'From'      => $from,
                            'To'        => $recipient,
                            'Subject'   => $subject
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

$par['empfaenger'] = $reports_normal_recipients;
$par['message'] = array('subject'=>"test",'body_txt'=>"test only");
sendEMail($par);




    die();
    $exasol_dsn = "odbc://FRONTEND:NHzseVLK2yGc53OWCDVz@/EXA_MUSIC";
    require_once('lib/pear/MDB2.php');
    function firstkw($jahr) {
	$erster = mktime(0,0,0,1,1,$jahr);
	$wtag = date('w',$erster);

	if ($wtag <= 4) {
		/**
* Donnerstag oder kleiner: auf den Montag zurï¿½ckrechnen.
*/
		$montag = mktime(0,0,0,1,1-($wtag-1),$jahr);
	} else {
		/**
* auf den Montag nach vorne rechnen.
*/
		$montag = mktime(0,0,0,1,1+(7-$wtag+1),$jahr);
	}
	return $montag;
}
 function thursdaykw($kw,$jahr,$plusminus='-') {
	$firstmonday = firstkw($jahr);
	$mon_monat = date('m',$firstmonday);
	$mon_jahr = date('Y',$firstmonday);
	$mon_tage = date('d',$firstmonday);

	$tage = ($kw-1)*7;

	if($plusminus=='+') {
		$mondaykw = mktime(0,0,0,$mon_monat,$mon_tage+$tage+3,$mon_jahr);
	}
	elseif ($plusminus=='-') {
		$mondaykw = mktime(0,0,0,$mon_monat,$mon_tage+$tage-3,$mon_jahr);
	}
	else {
		$mondaykw = mktime(0,0,0,$mon_monat,$mon_tage+$tage,$mon_jahr);
	}
	return $mondaykw;
}

    $dsn = $exasol_dsn; 
    $warner    = array(18505);
	$options 	= array(
	    'result_buffering' 	=> false, 
	    'field_case' 		=> CASE_LOWER
	);

	$db = MDB2::connect($dsn, $options);
	if (MDB2::isError($db)) {
		die ($db->getMessage());
	}
	#$zeit = 201412;
	$zeit_arr  = array(201001,201002,201003,201004,201005,201006,201007,201008,201009,201010,201011,201012,201013,201014,);
	foreach ($zeit_arr as $zeit) {
		# code...	
	$jahr2 = substr($zeit,2,2);
        $jahr4 = substr($zeit,0,4);
        $woche = substr($zeit,-2,2);

        $sql = "
        SELECT   s1.*,
                stamm_asnr AS seanc,
                stamm_eancid AS seanc_id,
                sprnr,
                stamm_main_prodnr AS main_prodnr,
                stamm_ag_txt AS tart,
                stamm_agid AS tartid,
                stamm_ag_code AS tart_code,
                stamm_wg_txt AS repe,
                stamm_wg_code AS repe_code,
                stamm_firm_code AS firmid,
                stamm_display_code AS display_code,
                stamm_artnr AS satnr,
                stamm_titel AS stitl,
                stamm_artist AS sinte,
                stamm_label_txt AS slabl,
                stamm_herst_txt AS vertrieb_lang,
                stamm_herst_txt2 AS vertrieb,
                stamm_archivnr AS archivnr
            FROM   (SELECT   *
                    FROM   (SELECT   ROWNUM AS index_, daten.*
                                FROM   (  SELECT 
                                                stamm_prodnr AS sprnr,
                                                SUM (bwg_wert) AS sum_wert,
                                                SUM (bwg_menge) AS sum_meng,
                                                SUM (bwg_wert_abs) AS sum_wert_abs,
                                                SUM (bwg_menge_abs) AS sum_meng_abs
                                            FROM   bewegung_w, region, stamm_gesamt
                                        WHERE       bwg_zeitkey      = $zeit
						      AND BWG_CONT_DIST IN ('641','645')
                                                AND stamm_landid     = 1041
                                                AND bwg_landid       = 1041
                                                AND region_landid    = 1041
                                                AND bwg_appid        = 12
                                                AND stamm_appid      = 12
                                                AND region_appid     = 12
                                                AND bwg_haendlerid   = region_haendlerid
                                                AND stamm_eancid     = bwg_eancid
                                                AND stamm_type_flag  = 'D'
                                                AND stamm_lauf       = 'DWN'
                                                AND NVL(stamm_quelle, 'EMPTY') <> 'MA'
                                        GROUP BY   stamm_prodnr
                                        ORDER BY   SUM (bwg_menge) DESC) daten)) s1,
                stamm_gesamt
        WHERE       s1.sprnr = SUBSTR (stamm_gesamt.stamm_eancid, 2)
                AND stamm_type_flag = 'D'
                AND stamm_lauf = 'DWN'
                AND stamm_landid = 1041
                AND stamm_is_header = 1
        GROUP BY   sprnr,
                stamm_display_code,
                stamm_main_prodnr,
                stamm_ag_txt,
                stamm_agid,
                stamm_ag_code,
                stamm_wg_txt,
                stamm_wg_code,
                stamm_firm_code,
                stamm_eancid,
                stamm_titel,
                stamm_asnr,
                stamm_artnr,
                stamm_artist,
                sum_wert,
                sum_meng,
                sum_wert_abs,
                sum_meng_abs,
                stamm_label_txt,
                stamm_herst_txt,
                stamm_herst_txt2,
                stamm_archivnr,
                index_
        ORDER BY   index_ ASC ";
         $arr = array();
         $data = array();
$ct = 0;
echo "begin query:".$zeit." \n";

        $rs=$db->query($sql);

        if (MDB2::isError($rs)) {
            dbug('error', $sql);
            $is_error = true;
        } else {
            while($arr= $rs->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                if ($arr) {
                    if(isset($udata[$arr['vertrieb']])) {
                            $firma = $udata[$arr['vertrieb']];
                    } else {
                            $firma = '';
                    }
                    #ISRC could sometimes be null. in this case, simply fill it with 13 times 9
                    $data[$ct]['ISRC']          = (isset($arr['satnr']) and strlen($arr['satnr']) > 0) ? $arr['satnr'] : '9999999999999';
                    $data[$ct]['Titel']         = (isset($arr['stitl']) and strlen($arr['stitl']) > 0) ? $arr['stitl'] : '          ';
                    $data[$ct]['Interpret']     = (isset($arr['sinte']) and strlen($arr['sinte']) > 0) ? $arr['sinte'] : '          ';
                    $data[$ct]['Menge']         = $arr['sum_meng'];
                    $data[$ct]['Firma']         = $firma;
                    $data[$ct]['Label']         = $arr['slabl'];
                    $data[$ct]['Vertrieb']      = $arr['vertrieb'];
                    $data[$ct]['VertriebLong']  = $arr['vertrieb_lang'];
                    $data[$ct]['FirmID']        = $arr['firmid'];
                    $data[$ct]['DisplayCode']   = $arr['display_code'];
                    $data[$ct]['Archivnr']      = trim($arr['archivnr']);
                    $data[$ct]['ProdNr']        = trim($arr['sprnr']);
                    $data[$ct]['MainProdNr']    = trim($arr['main_prodnr']);
                    $data[$ct]['Umsatz']        = $arr['sum_wert'];
                    $data[$ct]['Tart']          = trim($arr['tart']);
                    $data[$ct]['TartID']        = $arr['tartid'];
                    $data[$ct]['TartCode']      = $arr['tart_code'];
                    $data[$ct]['RepeID']        = $arr['repe_code'];
                    $data[$ct]['Repe']          = $arr['repe'];
                }
                $ct++;
            }
            $rs->free();
        }
echo "begin write to file\n";
        $dlwarner=fopen('CHDLWA'.$jahr2.'KW'.$woche.'.TXT', 'w');

        foreach ($data as $c=>$dt) {
               # if($dt['FirmID'] == 4391){
                    fwrite($dlwarner, $zeit.';');
                    fwrite($dlwarner, date('Ymd',thursdaykw($woche,$jahr4)).';');
                    fwrite($dlwarner, date('Ymd',thursdaykw($woche,$jahr4,'+')).';');  #date end
                    fwrite($dlwarner, '"'.$dt['ISRC'].'";');                                #ISRC
                    fwrite($dlwarner, '"'.$dt['Interpret'].'";');                           #Artist
                    fwrite($dlwarner, '"'.$dt['Titel'].'";');                               #Title
                    fwrite($dlwarner, '"'.$dt['MainProdNr'].'";');                          #mainprodID
                    fwrite($dlwarner, '"'.$dt['TartCode'].'";');                            #Tart Code
                    fwrite($dlwarner, '"'.$dt['Tart'].'";');                                #Tart Text 
                    fwrite($dlwarner, '"'.$dt['RepeID'].'";');                              #Genre Code
                    fwrite($dlwarner, '"'.$dt['Repe'].'";');                                #Genre Text
                    fwrite($dlwarner, number_format($dt['Menge'], 0, ",", "") . ';');       #Menge 
                    fwrite($dlwarner, '"'.$dt['DisplayCode'].'";');                         #DispCode (Distributor Code)
                    fwrite($dlwarner, '"'.$dt['VertriebLong'].'";');                        #Distributor Text
                    fwrite($dlwarner, '"'.$dt['Label'].'";');                               #Label
                    if(in_array($dt['DisplayCode'], $warner)) {
                        // Warner wants avg. price instead of sales
                        $m = $dt['Menge'];
                        $u = $dt['Umsatz'];
                        $ap = ($m == 0 ? 0 : $u/$m);
                        fwrite($dlwarner, number_format($ap, 2, ",", ""));
                    }else{
                        fwrite($dlwarner, number_format(0, 2, ",", ""));
                    }
                    fwrite($dlwarner,"\n");
                #}
            }
            fclose($dlwarner);
}

?>
