<?php
require_once ('config/dbconfig.php');
require_once ('MySQLSession.class.php');
	$sess = new MySQLSession(
	$session_user,
	$session_pass,
    $session_hostname,
	$session_dbname,
	7200,
    'apisess',
    $session_tbl
);
if(!isset($_SESSION['login_bokbasenapi']) || $_SESSION['login_bokbasenapi'] != true){
	header('Location: index.php');
	exit();
}
ini_set('memory_limit', '-1');
ini_set('max_input_time', 1200);
set_time_limit(0);
error_reporting(E_ALL);

require_once('config/config.php');

/**
BOKBASEN DATEN
 */
if($_SERVER['REQUEST_METHOD']=='POST'){
    if(isset($_POST['tabname']) && $_POST['tabname'] == 'bokbasen'){

        $logs ='';
        //flush_buffers();
        //Single mode
        $reports 	= $_POST['bokfrmreport'];
        if(isset($_POST['bokbasenfrmNext']) && $_POST['bokbasenfrmNext']!=''){
        	//do nothing
        }else{
	        $year 		= $_POST['bokbasenfrmYear'];
	        $month 		= $_POST['bokbasenfrmMonth'];
	        $day 		= $_POST['bokbasenfrmDay'];
	        $hour       = $_POST['bokbasenfrmHour'];
	        $minute     = $_POST['bokbasenfrmMinute'];
	        $second     = $_POST['bokbasenfrmSecond'];
	    }
        $rel_dir 	= time();
        $tmp_dir 	= dirname(__FILE__).'/'.$rel_dir;
        $old = umask(0);
        mkdir($tmp_dir, 0777);
        umask($old);
        $save_dir	= $tmp_dir . '/';
        $ftp_target  = $_POST['bokbasenfrmFTPdirectory'];
        //$files_tmp = array();
        $filename = $_POST['bokbasenfrmFilename'];

        //////////////////////////////////////////////////
        ////////////Get token from bokbasen///////////////
        //////////////////////////////////////////////////
        $url = 'https://login.boknett.no/v1/tickets';
        $fields = array(
            'username'=>'65006metadata',
            'password'=>'hK48Rt36'
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
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        //execute post
        $response =curl_exec($ch);
        $response = substr($response, strpos($response, "\r\n\r\n"));
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        //close connection
        curl_close($ch);
        $arr = explode("\r\n",$header);
        $tgt_key = '';
        $tgt_value = '';
        foreach ($arr as $line) {
            if(empty($line ))
                continue;
            if(strpos( $line,'Boknett-TGT:')===0){
                list($tgt_key,$tgt_value) = explode(': ', $line);
                break;
            }
        }
        /////////////////////////////////////////////////////////////////////////////
        //////////END Get token from bokbasen: return token = $tgt_value/////////////
        /////////////////////////////////////////////////////////////////////////////
        $authorization = "Boknett ".$tgt_value;
        $date = date(DATE_RFC1123, time()-3600);
        ///////////////////////////////////////////////////////////////////
        ////////////Get Onix Report from bokbasen with Token///////////////
        ///////////////////////////////////////////////////////////////////
        $headers = array(
            'Authorization: '.$authorization,
            'Date: '.substr($date,0,strlen($date)-5).'UTC'
        );
        $url = 'https://api.boknett.no/metadata/export/';
        $url .= $_POST['bokfrmreport'].'?';
        $url .= 'subscription='.$_POST['bokbasenfrmSubscription'];
        $url .= '&pagesize='.$_POST['bokbasenfrmPagesize'];
        if(isset($_POST['bokbasenfrmNext']) && $_POST['bokbasenfrmNext']!=''){
        	$url .= '&next='.$_POST['bokbasenfrmNext'];
        }else{
        	$url .= '&after='.$year.$month.$day.$hour.$minute.$second;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $data = curl_exec($ch);
        $data = substr($data, strpos($data, "\r\n\r\n"));
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($data, 0, $header_size);
        $data = trim(substr($data,$header_size));
        $info = curl_getinfo($ch);
        curl_close($ch);
        //change next token and link if exist
        $next_token = '';
        $link_param = '';
        $arr = explode("\r\n",$header);
        $tgt_key = '';
        $tgt_value = '';
        foreach ($arr as $line) {
            if(empty($line ))
                continue;
            if(strpos( $line,'Next:')===0){
                list($tgt_key,$tgt_value) = explode(': ', $line);
                break;
            }
        }
        $next_token = trim($tgt_value);
        update_bokbasen_next_token('bokbasennext.txt',$next_token);
        $tgt_key = '';
        $tgt_value = '';
        foreach ($arr as $line) {
            if(empty($line ))
                continue;
            if(strpos( $line,'Link:')===0){
                list($tgt_key,$tgt_value) = explode(': ', $line);
                break;
            }
        }
        $link_param = substr($tgt_value, 0, strpos($tgt_value, ';'));
        update_bokbasen_next_token('bokbasenlink.txt',$link_param);            
        ///////////////////////////////////////////////////////////////////
        ////////////End Get Onix Report from bokbasen with Token///////////
        ///////////////////////////////////////////////////////////////////
        $logs = '+ Boknett-TGT: '.$tgt_value.'\n';
        $logs .= '+ Query: \n';
        $logs .= $url.'\n';
        $logs .= '+ '.$filename." :  " ;

        //////////////////////////////////////////////////
        /////////////write data to file///////////////////
        //////////////////////////////////////////////////
        if($info['http_code']==200){
            //downloading...
            $file = $save_dir.$filename;
            $fh = fopen($file, 'w');
            fwrite($fh, $data);
            fclose($fh);
            //////////////////////////////////////////////////
            /////////////end write data to file///////////////
            //////////////////////////////////////////////////
            if(filesize($save_dir.$filename)>0){
                if($_POST['ftpCheck']=='FTP'){
                    //////////////////////////////////////////////////
                    ///////ftp transfer to asbad04.mcbad.net//////////
                    //////////////////////////////////////////////////
                    //open ftp
                    $conn_id=ftp_connect($ftp_host);
                    ftp_login($conn_id, $ftp_username, $ftp_password);
                    ftp_pasv($conn_id, true);
                    if( !ftp_put($conn_id, $ftp_target . '/'.$filename, $file, FTP_BINARY)) {
                        $logs .= "FTP failed to upload ".$file." file.";
                    }else{
                        $logs .= "upload to FTP SERVER success!";
                    }
                    ftp_close($conn_id);
                    //////////////////////////////////////////////////
                    ///////end ftp transfer to asbad04.mcbad.net//////
                    //////////////////////////////////////////////////
                    rmdirr($rel_dir);
                }else{
                    //download to local Computer
                    $re_url = 'http://'.$_SERVER['HTTP_HOST'].'/spotifydwn.php?file='.urlencode($rel_dir.'/'.$filename).'&tmp_dir='.urlencode($rel_dir);
                    echo '<script>';
                    echo 'window.open("'.$re_url.'","_blank");';
                    echo '</script>';
                    $logs .="success!!";
                }
            }else{
                $logs .="Bokbasen transfer empty file!";
                rmdirr($rel_dir);
            }
        }else{
            switch ($info['http_code']){
                case 400:
                    $logs .= 'Bad Request';
                    break;
                case 401:
                    $logs .= 'Unauthorized';
                    break;
                case 403:
                    $logs .= 'Forbidden';
                    break;
                case 406:
                    $logs .= 'Not Acceptable';
                    break;
                case 500:
                    $logs .= 'Internal Server Error';
                    break;
                default:
                    $logs .= 'Unknown Error';
                    break;
            }
            rmdirr($rel_dir);
        }        
    }
}
/**
END BOKBASEN DATEN
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.min.js"></script>    
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>    
    <link rel="stylesheet" type="text/css" href="core.css">    
    <script src="js/bokbasen.js"></script>
	<script type="text/javascript">
	$(document).ready(function(){
	    $('[data-toggle="tooltip"]').tooltip();
	});
	</script>
</head>
<body>    
    <div class="container">
		<div class="divider"></div>
		<div class="pull-right" data-toggle="tooltip" data-placement="left" title="logout">
			<a href="logout.php">
			<span class="glyphicon icon-logout"></span>
			</a>
		</div>					
		<div class="row">
			<div class="tabbable custom-tabs" ng-app="tabbedApp" ng-controller="tabController">
				<ul class="nav nav-tabs">
					<li ng-class="bokbasenTab" ng-click="bokbasenTab = 'active'">
					<a class="active " data-toggle="tab" href="#">
					<span class="glyphicon icon-bokbasen"></span>
					<span>bokbasen as</span>
					</a>
					</li>											
				</ul>
				<div class="tab-content ">
					<div class="tab-pane active" ng-class="bokbasenTab" ng-init="bokbasenTab = 'active'">
						<div class="row">
							<div class="col-md-5">
								<h4>
									<span class="glyphicon icon-setting"></span>
									Bokbasen XML Data: API Parameters setting
								</h4>
								<form class="form-horizontal" role="form" id="bokbasenFrom" action="main_bokbasen.php" method="post">
								  <div class="form-group">
									    <label for="bokfrmreport" class="col-md-2 control-label">Report-Type:</label>

										<div class="col-md-10">
										    <select id="bokfrmreport" name="bokfrmreport" class="form-control" ng-model="varReport" 
										    <?php 
											//init Bokbasen Report type
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen'){
												echo 'ng-init="varReport=\''.$_POST['bokfrmreport'].'\'"';
											}else{
												echo 'ng-init="varReport = \'onix\'"';
											}   
											?> 
										    >
										    	<option value="bokbasenxml">Bokbasen XML Report</option>
												<option value="onix">Onix Report</option>
										    </select>
										</div>
								  	</div>
								  <div class="form-group">
								  	<label for="bokbasenfrmNext" class="col-md-1 control-label">
								  		next:
								  	</label>
								  	<div class="col-md-11">
                                        <input type="text" class="form-control" id="bokbasenfrmNext" name="bokbasenfrmNext" ng-model="varNext"
                                        	<?php 
											//init Bokbasen Next parameter
											echo 'ng-init="varNext = \''.get_bokbasen_next_token('bokbasennext.txt').'\'"';											  
											?> 
                                        />
                                    </div>
								  </div>
								  <div class="form-group" ng-class="(varLink)?error_status:succes_status">
								  	<label for="bokbasenfrmLink" class="col-md-1 control-label">
								  		link:
								  	</label>
								  	<div class="col-md-11">
                                        <input type="text" class="form-control" id="bokbasenfrmLink" name="bokbasenfrmLink" ng-model="varLink" disabled
                                        	<?php 
											//init Bokbasen Next parameter
											echo 'ng-init="varLink = \''.get_bokbasen_next_token('bokbasenlink.txt').'\'"';											  
											?> 
                                        />
                                    </div>                                    
								  </div>
								  <div class="form-group">
								  	<label for="bokbasenfrmAfter" class="col-md-1 control-label">
								  		after:
								  	</label>
								  	<div class="col-md-6">
                                        <input type="text" class="form-control" id="bokbasenfrmAfter" name="bokbasenfrmAfter" value={{varYear}}{{varMonth}}{{varDay}}{{varHour}}{{varMinute}}{{varSecond}} disabled/>
                                    </div>
								  </div>
								  <div class="form-group">
								  		<label for="bokbasenfrmYear" class="col-md-1 control-label">Year:</label>
								  		<div class="col-md-3">
								  			<select id="bokbasenfrmYear" name="bokbasenfrmYear" class="form-control" ng-model="varYear" ng-disabled="varNext"
								  			<?php
											//init Bokbasen Year
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen' && isset($_POST['bokbasenfrmYear'])){
												echo 'ng-init="varYear=\''.$_POST['bokbasenfrmYear'].'\'"';
											}else{
												echo 'ng-init="varYear=curYear"';
											}
											?>
								  			>
										    	<?php
										    	$currentYear = date('Y');
												 foreach (range($currentYear,2000) as $value) {
												 		echo '<option value='. $value .'>' . $value . '</option >' ;
												 	}
										    	?>
										    </select>
								  		</div>
								  		<label for="bokbasenfrmMonth" class="col-md-1 control-label">Month:</label>
								  		<div class="col-md-3">
								  			<select id="bokbasenfrmMonth" name="bokbasenfrmMonth" class="form-control" ng-model="varMonth" ng-disabled="varNext"
								  			<?php
											//init Bokbasen Month
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen' && isset($_POST['bokbasenfrmMonth'])){
												echo 'ng-init="varMonth=\''.$_POST['bokbasenfrmMonth'].'\'"';
											}else{
												echo 'ng-init="varMonth=curMonth"';
											}
											?>
								  			>
										    	<?php
												 for($i=1;$i<=12;$i++) {
												 	if($i<10){
												 		if(date('n')==$i){
												 			echo '<option value=0'. $i .' selected>0' . $i . '</option >' ;
												 		}else{
													 		echo '<option value=0'. $i .'>0' . $i . '</option >' ;
													 	}
												 	}else{
												 		if(date('n')==$i){
												 			echo '<option value='. $i .' selected>' . $i . '</option >' ;
												 		}else{
													 		echo '<option value='. $i .'>' . $i . '</option >' ;
													 	}
												 	}
												 }
										    	?>
										    </select>
								  		</div>
								  		<label for="bokbasenfrmDay" class="col-md-1 control-label">Day:</label>
								  		<div class="col-md-3">
								  			<select id="bokbasenfrmDay" name="bokbasenfrmDay" class="form-control" ng-model="varDay" ng-disabled="varNext"
								  			<?php
											//init Bokbasen Day
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen' && isset($_POST['bokbasenfrmDay'])){
												echo 'ng-init="varDay=\''.$_POST['bokbasenfrmDay'].'\'"';
											}else{
												echo 'ng-init="varDay=curDay"';
											}
											?>
								  			>
										    	<?php
												 for($i=1;$i<=31;$i++) {
												 	if($i<10){
													 		echo '<option value=0'. $i .'>0' . $i . '</option >' ;
												 	}else{
													 		echo '<option value='. $i .'>' . $i . '</option >' ;
												 	}
												 }
										    	?>
										    </select>
								  		</div>
								  </div>
								  <div class="form-group">
								  	<label for="bokbasenfrmHour" class="col-md-1 control-label">Hour:
								  	</label>
							  		<div class="col-md-3">
							  			<select id="bokbasenfrmHour" name="bokbasenfrmHour" class="form-control" ng-model="varHour" ng-disabled="varNext"
							  			<?php
										//init Bokbasen Hour
										if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen' && isset($_POST['bokbasenfrmHour'])){
											echo 'ng-init="varHour=\''.$_POST['bokbasenfrmHour'].'\'"';
										}else{
											echo 'ng-init="varHour=curHour"';
										}
										?>
							  			>
									    	<?php
											 for($i=0;$i<=23;$i++) {
											 	if($i<10){
												 		echo '<option value=0'. $i .'>0' . $i . '</option >' ;
											 	}else{
												 		echo '<option value='. $i .'>' . $i . '</option >' ;
											 	}
											 }
									    	?>
									    </select>
							  		</div>
							  		<label for="bokbasenfrmMinute" class="col-md-1 control-label">Minute:
								  	</label>
							  		<div class="col-md-3">
							  			<select id="bokbasenfrmMinute" name="bokbasenfrmMinute" class="form-control" ng-model="varMinute" ng-disabled="varNext"
							  			<?php
										//init Bokbasen Hour
										if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen' && isset($_POST['bokbasenfrmMinute'])){
											echo 'ng-init="varMinute=\''.$_POST['bokbasenfrmMinute'].'\'"';
										}else{
											echo 'ng-init="varMinute=curMinute"';
										}
										?>
							  			>
									    	<?php
											 for($i=0;$i<=59;$i++) {
											 	if($i<10){
												 		echo '<option value=0'. $i .'>0' . $i . '</option >' ;
											 	}else{
												 		echo '<option value='. $i .'>' . $i . '</option >' ;
											 	}
											 }
									    	?>
									    </select>
							  		</div>
							  		<label for="bokbasenfrmSecond" class="col-md-1 control-label">Second:
								  	</label>
							  		<div class="col-md-3">
							  			<select id="bokbasenfrmSecond" name="bokbasenfrmSecond" class="form-control" ng-model="varSecond" ng-disabled="varNext"
							  			<?php
										//init Bokbasen Hour
										if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen' && isset($_POST['bokbasenfrmSecond'])){
											echo 'ng-init="varSecond=\''.$_POST['bokbasenfrmSecond'].'\'"';
										}else{
											echo 'ng-init="varSecond=curSecond"';
										}
										?>
							  			>
									    	<?php
											 for($i=0;$i<=59;$i++) {
											 	if($i<10){
												 		echo '<option value=0'. $i .'>0' . $i . '</option >' ;
											 	}else{
												 		echo '<option value='. $i .'>' . $i . '</option >' ;
											 	}
											 }
									    	?>
									    </select>
							  		</div>
								  </div>
                                  <div class="form-group">
                                      <label for="bokbasenfrmSubscription" class="col-md-2 control-label">Subscription:
                                      </label>
                                      <div class="col-md-4">
                                          <select id="bokbasenfrmSubscription" name="bokbasenfrmSubscription" class="form-control" ng-model="varSubcription"
                                              <?php
                                              //init Bokbasen Subcription
                                              if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen'){
                                                  echo 'ng-init="varSubcription=\''.$_POST['bokbasenfrmSubscription'].'\'"';
                                              }else{
                                                  echo 'ng-init="varSubcription=\'Extended\'"';
                                              }
                                              ?>
                                              >
                                              <option value="Basic">Basic</option>
                                              <option value="Extended">Extended</option>
                                              <option value="School">School</option>
                                          </select>
                                      </div>
                                  </div>
                                  <div class="form-group">
                                    <label for="bokbasenfrmPagesize" class="col-md-2 control-label">Pagesize:
                                    </label>
                                    <div class="col-md-4">
                                        <select id="bokbasenfrmPagesize" name="bokbasenfrmPagesize" class="form-control" ng-model="varPagesize"
                                            <?php
                                            //init Bokbasen Pagesize
                                            if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen'){
                                                echo 'ng-init="varPagesize=\''.$_POST['bokbasenfrmPagesize'].'\'"';
                                            }else{
                                                echo 'ng-init="varPagesize=\'1000\'"';
                                            }
                                            ?>
                                            >
                                            <option value="10">10</option>
                                            <option value="100">100</option>
                                            <option value="500">500</option>
                                            <option value="1000">1000 (max)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
								  	<label for="bokbasenfrmFilename" class="col-md-2 control-label">
								  		filename:
								  	</label>
								  	<div class="col-md-6">
                                        <input type="text" class="form-control" id="bokbasenfrmFilename" name="bokbasenfrmFilename" ng-model="varFilename"
                                        	ng-init="varFilename=varReport+'_'+varYear+'_'+varMonth+'_'+varDay+'_'+varHour+'_'+varMinute+'_'+varSecond+'.xml'"
                                        />
                                    </div>
								  </div>
								  <div class="form-group">
								  	<div class="btn-group col-md-12">
								  		<button class="btn btn-default {{ftpbutton}}" type="button" ng-click="ftpbutton='active';localbutton='';ftpCheck='FTP'"
								  		<?php 
											//init ftp
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen' && isset($_POST['ftpCheck']) && $_POST['ftpCheck']=='LOCAL'){
												echo 'ng-init="ftpbutton=\'\'"';
											}else{
												echo 'ng-init="ftpbutton=\'active\'"';
											}   
											?>									  			
								  		data-toggle="tooltip" title="Upload direct to FTP">FTP<span class="glyphicon icon-ftp"></span></button>

								  		<button class="btn btn-default {{localbutton}}" type="button" ng-click="ftpbutton='';localbutton='active';ftpCheck='LOCAL'"
								  		<?php 
											//init non ftp
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen' && isset($_POST['ftpCheck']) && $_POST['ftpCheck']=='LOCAL'){
												echo 'ng-init="localbutton=\'active\'"';
											}else{
												echo 'ng-init="localbutton=\'\'"';
											}   
											?>
								  		data-toggle="tooltip" title="Download to local" >local<span class="glyphicon icon-local"></span></button>
								  	</div>
								  	<input type="text" name="ftpCheck" ng-model="ftpCheck" style="display:none"
								  	<?php 
											//init FTP
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen' && isset($_POST['ftpCheck']) &&  $_POST['ftpCheck']=='LOCAL'){
												echo 'ng-init="ftpCheck=\'LOCAL\'"';
											}else{
												echo 'ng-init="ftpCheck=\'FTP\'"';
											}   
											?>
									>
								  </div>
								  <div class="form-group {{ftpPfad?'':error_status}}">
								  	<div class="col-md-12">
								  	 <input type="text" class="form-control" id="bokbasenfrmFTPdirectory" name="bokbasenfrmFTPdirectory" value={{ftpPfad}} ng-model="ftpPfad" style="display:{{(ftpbutton=='active')?display_block:display_none}}"
								  	 <?php 
										//init Bokbasen FTP URL
										if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'bokbasen'){
											echo 'ng-init="ftpPfad=\''.$_POST['bokbasenfrmFTPdirectory'].'\'"';
										}else{
											echo 'ng-init="ftpPfad=\'/nfs/ASBAD04/FTP/MediaControl/NOR/GfK-Exchange/Article_Database/Book/Onix\'"';
										}   
										?>
								  	 >
								  	</div>
								  	<div class="col-md-12">
								  		<div class="alert alert-danger" style="display:{{((ftpPfad && ftpbutton=='active') || localbutton=='active')?display_none:display_block}}">
								  			Please insert FTP Directory
								  		</div>
								  	</div>
								  </div>
								  <!--hidden pamrameters-->
								  <div class="form-group" style="display:none">
								  		<input id="tabname" name="tabname" class="form-control" type="text" value="bokbasen">
								  </div>
								  <hr>
								  <div class="form-group">
								  	<div class="col-md-3">
								  		<button type="submit" class="btn btn-primary" ng-disabled="!(((ftpPfad && ftpbutton=='active') || localbutton=='active') && varFilename)">Execute<span class="glyphicon icon-submit"></span></button>
								  	</div>
								  </div>

								</form>								
							</div>
							<!--///////////////////FORM//////////////////////////-->
							<div class="col-md-3">
								<h4>
									<span class="glyphicon icon-filedwn"></span>
									Files Download info:
								</h4>

								<div class="row">
									<label class="col-md-5">
										{{varFilename}}
									</label>										
								</div>
                                <hr/>
                                <div class="row">
									<label class="col-md-12">
										https://api.boknett.no/metadata/export/{{varReport}}?{{(varNext)?'next='+varNext:'after='+varYear+varMonth+varDay+varHour+varMinute+varSecond}}&subscription={{varSubcription}}&pagesize={{varPagesize}}
									</label>										
								</div>

                            </div>
							<div class="col-md-4">
								<h4>
									<span class="glyphicon icon-document"></span>
									Process logs:
								</h4>
								<div class="divider tiny"></div>
								<div class="row">
									<div class="col-md-12">
										<textarea class="form-control notes" rows="11" id="bokbasenLogs"></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>                    
				</div>	
			</div>
		</div>
	</div>	
</body>
</html>
<?php 
if($_SERVER['REQUEST_METHOD']=='POST'){
    if(isset($_POST['tabname']) && $_POST['tabname'] == 'bokbasen'){
		writelogsbokbasen($logs);
	}
}