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
if(!isset($_SESSION['login']) || $_SESSION['login'] != true){
	header('Location: index.php');
	exit();
}
ini_set('memory_limit', '-1');
ini_set('max_input_time', 1200);
set_time_limit(0);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.min.js"></script>    
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script src="js/app.js"></script>
    <link rel="stylesheet" type="text/css" href="core.css">
    <script type="text/javascript">
	$(document).ready(function(){
	    $('[data-toggle="tooltip"]').tooltip();
	});
	</script>
</head>
<body >		
	<?php require_once('config/config.php'); ?>	
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
						<li ng-class="spotifyTab" ng-click="spotifyTab = 'active'; itunesTab = ''; sevenDigitalTab = '';">
						<a class="active " data-toggle="tab" href="#">
						<span class="glyphicon icon-Spotify"></span>
						<span>Spotify</span>
						</a>
						</li>
						<li ng-class="itunesTab" ng-click="spotifyTab = ''; itunesTab = 'active'; sevenDigitalTab = '';">
						<a data-toggle="tab" href="#">
						<span class="glyphicon icon-Itunes"></span>
						<span>iTunes</span>
						</a>
						</li>
						<li ng-class="sevenDigitalTab" ng-click="spotifyTab = ''; itunesTab = ''; sevenDigitalTab = 'active';">
						<a data-toggle="tab" href="#">
						<span class="glyphicon icon-7Digtal"></span>
						<span>7 Digital</span>
						</a>
						</li>						
					</ul>

					<div class="tab-content ">
						<!----------------------SPOTIFY-------------------------->
						<div class="tab-pane " ng-class="spotifyTab" 
						<?php 
						//init Tabs
						if($_SERVER['REQUEST_METHOD']=='POST'){
							switch ($_POST['tabname']) {
								case 'itunes':
									echo 'ng-init="spotifyTab = \'\'; itunesTab = \'active\'; sevenDigitalTab = \'\';"';
									break;

								case '7digital':
									echo 'ng-init="spotifyTab = \'\'; itunesTab = \'\'; sevenDigitalTab = \'active\';"';
									break;
								
								default:
									echo 'ng-init="spotifyTab = \'active\'; itunesTab = \'\'; sevenDigitalTab = \'\';"';
									break;
							}							
						}else{
							echo 'ng-init="spotifyTab = \'active\'; itunesTab = \'\'; sevenDigitalTab = \'\';"';
						}   
						?> 
							>
							<div class="row">
								<div class="col-md-5">
									<h4>
										<span class="glyphicon icon-setting"></span>
										Spotify Streaming Data: API Parameters setting
									</h4>
									<form class="form-horizontal" role="form" id="spotifyFrom" action="main.php" method="post">
										<div class="form-group">
										    <label for="spotfrmCountry" class="col-md-1 control-label">Country:</label>
										</div> 
										<div class="form-group">
											<div class="col-md-12">									   
											    <select id="spotfrmCountry" name="spotfrmCountry" class="form-control" ng-model="varCountry" 
											    <?php 
												//init Spotify country
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'spotify'){													
													echo 'ng-init="varCountry=\''.$_POST['spotfrmCountry'].'\'"';
												}else{
													echo 'ng-init="varCountry = \'de\'"';
												}   
												?> 
											    >
											    	<option value="at">AT ( &Ouml;sterreich )</option>
													<option value="ch">CH ( Schweiz )</option>
													<option value="de">DE ( Deutschland )</option>
													<option value="be">BE ( Belgien )</option>
													<option value="nl">NL ( Niederlande )</option>
													<option value="fr">FR ( Frankreich )</option>
													<option value="it">IT ( Italien )</option>
													<option value="es">ES ( Spanien )</option> 
													<option value="be_flanders">BE ( FLANDERS )</option>
													<option value="be_wallonia">BE ( WALLONIA )</option>
													<option value="be_brussels">BE ( BRUSSELS )</option>
													<option value="be_noregion">BE ( NOREGION )</option>
													<option value="pt">PT ( PORTUGAL )</option>
											    </select>
											</div>
									  </div>
									  <div class="form-group">
									  		<label for="spotfrmYear" class="col-md-1 control-label">Year:</label>
									  		<div class="col-md-3">
									  			<select id="spotfrmYear" name="spotfrmYear" class="form-control" ng-model="varYear" 
									  			<?php 
												//init Spotify Year
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'spotify'){													
													echo 'ng-init="varYear=\''.$_POST['spotfrmYear'].'\'"';
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
									  		<label for="spotfrmMonth" class="col-md-1 control-label">Month:</label>
									  		<div class="col-md-3">
									  			<select id="spotfrmMonth" name="spotfrmMonth" class="form-control" ng-model="varMonth"
									  			<?php 
												//init Spotify Month
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'spotify'){													
													echo 'ng-init="varMonth=\''.$_POST['spotfrmMonth'].'\'"';
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
									  		<label for="spotfrmDay" class="col-md-1 control-label">Day:</label>
									  		<div class="col-md-3">
									  			<select id="spotfrmDay" name="spotfrmDay" class="form-control" ng-model="varDay"
									  			<?php 
												//init Spotify Day
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'spotify'){													
													echo 'ng-init="varDay=\''.$_POST['spotfrmDay'].'\'"';
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
									  	<!--<label for="spotfrmFTPdirectory" class="col-md-5  control-label">Download to FTP Directory:</label>-->
									  	<div class="btn-group col-md-12">
									  		<button class="btn btn-default {{ftpbutton}}" type="button" ng-click="ftpbutton='active';localbutton='';ftpCheck='FTP'"
									  		<?php 
												//init ftp
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'spotify' && isset($_POST['ftpCheck']) && $_POST['ftpCheck']=='LOCAL'){
													echo 'ng-init="ftpbutton=\'\'"';
												}else{
													echo 'ng-init="ftpbutton=\'active\'"';
												}   
												?>									  			
									  		data-toggle="tooltip" title="Upload direct to FTP">FTP<span class="glyphicon icon-ftp"></span></button>

									  		<button class="btn btn-default {{localbutton}}" type="button" ng-click="ftpbutton='';localbutton='active';ftpCheck='LOCAL'"
									  		<?php 
												//init non ftp
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'spotify' && isset($_POST['ftpCheck']) && $_POST['ftpCheck']=='LOCAL'){
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
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'spotify' && isset($_POST['ftpCheck']) &&  $_POST['ftpCheck']=='LOCAL'){
													echo 'ng-init="ftpCheck=\'LOCAL\'"';
												}else{
													echo 'ng-init="ftpCheck=\'FTP\'"';
												}   
												?>
										>
									  </div>
									  <div class="form-group {{ftpPfad?'':error_status}}">
									  	<div class="col-md-12">
									  	 <input type="text" class="form-control" id="spotfrmFTPdirectory" name="spotfrmFTPdirectory" value={{ftpPfad}} ng-model="ftpPfad" style="display:{{(ftpbutton=='active')?display_block:display_none}}"
									  	 <?php 
											//init Spotify FTP URL
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'spotify'){													
												echo 'ng-init="ftpPfad=\''.$_POST['spotfrmFTPdirectory'].'\'"';
											}else{
												echo 'ng-init="ftpPfad=\'/NFS/ASBAD04/FTP/MediaControl/GER/DigRetailer/Spotify_API/FROMGUI\'"';
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
									  		<input type="checkbox" id="multiModeCheckBox" name="multiModeCheckBox"
									  		<?php 
											//init Spotify Multimode
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'spotify' && isset($_POST['multiModeCheckBox']) && $_POST['multiModeCheckBox']=='on'){
												echo 'checked';
											}  
											?>
											> Multi download</input>
									  		<input id="dwnitems" name="dwnitems" class="form-control" type="text" ng-model="dwnString">
									  		<input id="tabname" name="tabname" class="form-control" type="text" value="spotify">
									  </div>
									  <hr>
									  <div class="form-group">
									  	<div class="col-md-3">
									  		<button type="submit" class="btn btn-primary" ng-disabled="(!ftpPfad) || (multiMode && !downloadLists.length)">Execute<span class="glyphicon icon-submit"></span></button>
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
											spotify_toplist_for_{{varCountry | uppercase}}_{{varYear}}_{{varMonth}}_{{varDay}}
										</label>										
									</div>
									<div class="row">
										<div class="col-md-6">
											<input type="checkbox" ng-model="multiMode" ng-click="multiModeClick()" 
											<?php 
											//init Spotify Multimode
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'spotify' && isset($_POST['multiModeCheckBox']) && $_POST['multiModeCheckBox']=='on'){													
												echo 'ng-init="multiMode=true"';
											}else{
												echo 'ng-init="multiMode = false"';
											}   
											?>
												> Multi download</input>
										</div>
										<div class="col-md-6">											
											<button type="button" class="btn btn-default btn-xs pull-right" style="display:{{multiMode?display_block:display_none}}" ng-click="add_dwnitem()"><span class="glyphicon icon-add"></span>&nbsp;&nbsp;&nbsp;&nbsp;Add</button>
										</div>
									</div>
									<div class="divider tiny"></div>
									<div class="row {{(multiMode && !downloadLists.length)? error_status: ''}}" style="display:{{multiMode?display_block:display_none}}">
										<div class="col-md-12">
											<select multiple style="width:100%;height:156px;" class="form-control has-error" id="downloadList" 
											<?php 
											//init Spotify Multimode
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'spotify' && isset($_POST['multiModeCheckBox']) && $_POST['multiModeCheckBox']=='on'){
												$tmp = implode("','",explode(',', $_POST['dwnitems']));		
												echo 'ng-init="dwnString = \''.$_POST['dwnitems'].'\'; downloadLists = [\''.$tmp.'\'];"';
											}else{
												echo 'ng-init="dwnString = \'\'; downloadLists = [];"';
											}   
											?>
											>
												<option ng-repeat="dwnitem in downloadLists" value={{dwnitem}}>{{dwnitem}}</option>
											</select>
										</div>
										<div class="col-md-12">
									  		<div class="alert alert-danger" style="display:{{downloadLists.length?display_none:display_block}}">
									  			Please add files to download
									  		</div>
									  	</div>
									</div>
									<div class="divider tiny"></div>
									<div class="row">
										<div class="col-md-12">
											<button type="button" class="btn btn-default btn-xs pull-right" style="display:{{multiMode?display_block:display_none}}" ng-click="remove_dwnitem()"><span class="glyphicon icon-remove"></span>&nbsp;&nbsp;Remove</button>
										</div>
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
											<textarea class="form-control" rows="13" id="spotifyLogs"></textarea>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php 						
						if($_SERVER['REQUEST_METHOD']=='POST'){
						/**
						SPOTIFY DATEN
						*/						
						if(isset($_POST['tabname']) && $_POST['tabname'] == 'spotify'){
							$logs ='';
							if(!isset($_POST['multiModeCheckBox'])){
								//flush_buffers();
								//Single mode
								$country 	= $_POST['spotfrmCountry'];
								$year 		= $_POST['spotfrmYear'];
								$month 		= $_POST['spotfrmMonth'];
								$day 		= $_POST['spotfrmDay'];
								$rel_dir 	= time();
								$tmp_dir 	= dirname(__FILE__).'/'.$rel_dir;
								$old = umask(0);
								mkdir($tmp_dir, 0777);
								umask($old);
								$save_dir	= $tmp_dir . '/';
								$ftp_target  = $_POST['spotfrmFTPdirectory'];
								$files_tmp = array();
								$logs = '+ spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day." :  " ;																
								//////////////////////////////////////////////////
								///////////////get Chart data/////////////////////
								//////////////////////////////////////////////////
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, "https://ws.spotify.com/analytics/chart/".$country."/".$year."/".$month."/".$day."?oauth_token=".getTokenSpotify());
								curl_setopt($ch, CURLOPT_PROXY, $proxy);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
								$data = curl_exec($ch);
								$info = curl_getinfo($ch);
								curl_close($ch);
								//////////////////////////////////////////////////
								/////////////END get Chart data///////////////////
								//////////////////////////////////////////////////

								//////////////////////////////////////////////////
								/////////////write data to file///////////////////
								//////////////////////////////////////////////////
								if($data){
									//downloading...
									$file = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.'.gz';
									$fh = fopen($file, 'w');
									fwrite($fh, $data);
									fclose($fh);
									//uncompressing...
									$gzip=file_get_contents($file);
									$rest = substr($gzip, -4);
									$tmptmp = unpack("V", $rest);
									$GZFileSize = end($tmptmp);
									$zd = gzopen($file, "r");
									$contents = gzread($zd, $GZFileSize);
									gzclose($zd);
									$unc_file = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day;
									$fh = fopen($unc_file, 'w');
									fwrite($fh, $contents);
									fclose($fh);
									if($_POST['ftpCheck']=='FTP'){
										//write to text file
										$textfile = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".part";
									}else{
										$textfile = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".txt";
										$files_tmp[]= array(
											"abs_path" => $save_dir. 'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".txt", 
											"rel_path" => 'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".txt");
									}
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
									unlink($unc_file);
									unlink($file);								
								//////////////////////////////////////////////////
								/////////////end write data to file///////////////
								//////////////////////////////////////////////////
									if(filesize($textfile)>0){
										if($_POST['ftpCheck']=='FTP'){
											//////////////////////////////////////////////////
											///////ftp transfer to asbad04.mcbad.net//////////
											//////////////////////////////////////////////////
											//open ftp
											$conn_id=ftp_connect($ftp_host);
											ftp_login($conn_id, $ftp_username, $ftp_password);
											ftp_pasv($conn_id, true);
											if( !ftp_put($conn_id, $ftp_target . '/spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".part", $textfile, FTP_BINARY)) {
												$logs .= "FTP failed to upload ".$textfile." file.";
											}else{
												sleep(5);
												ftp_rename($conn_id, $ftp_target . '/spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".part",$ftp_target . '/spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".txt");
												$logs .= "upload to FTP SERVER success!";
												#unlink($textfile);
											}
											ftp_close($conn_id);
											//////////////////////////////////////////////////
											///////end ftp transfer to asbad04.mcbad.net//////
											//////////////////////////////////////////////////
											rmdirr($rel_dir);
										}else{
											//download to local Computer
											create_zip($files_tmp,$tmp_dir.'/spotifyStreamingDaten.zip',true);
											/*
											$re_url = substr('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'], 0, strlen('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'])-9) . 'spotifydwn.php?file='.urlencode($rel_dir.'/spotifyStreamingDaten.zip').'&tmp_dir='.urlencode($tmp_dir);
											*/
											$re_url = 'http://'.$_SERVER['HTTP_HOST'].'/spotifydwn.php?file='.urlencode($rel_dir.'/spotifyStreamingDaten.zip').'&tmp_dir='.urlencode($rel_dir);
											echo '<script>';
											echo 'window.open("'.$re_url.'","_blank");';
											echo '</script>';
											$logs .="success!!";
										}
									}else{
										$logs .="Spotify transfer empty file!";
										rmdirr($rel_dir);
									}									
								}else{
									$logs .="no data from Spotify-API";
									rmdirr($rel_dir);
								}
								writelogs($logs);
							}else{
								//Multi mode
								$items_arr = explode(',', $_POST['dwnitems']);
								$rel_dir 	= time();
								$tmp_dir 	= dirname(__FILE__).'/'.$rel_dir;
								$old = umask(0);
								mkdir($tmp_dir, 0777);
								umask($old);
								$save_dir	= $tmp_dir . '/';
								$files_tmp  = array();
								$file_flg = false;

								foreach ($items_arr as $value) {
									//flush_buffers();
									$country 	= substr($value, 0,strlen($value)-8);
									$year 		= substr($value, -8,4);
									$month 		= substr($value, -4,2);
									$day 		= substr($value, -2);									
									
									$ftp_target = $_POST['spotfrmFTPdirectory'];
									$logs .= '+ spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day." :  " ;
									//////////////////////////////////////////////////
									///////////////get Chart data/////////////////////
									//////////////////////////////////////////////////
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, "https://ws.spotify.com/analytics/chart/".$country."/".$year."/".$month."/".$day."?oauth_token=".getTokenSpotify());
									curl_setopt($ch, CURLOPT_PROXY, $proxy);
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
										//downloading...
										$file = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.'.gz';
										$fh = fopen($file, 'w');
										fwrite($fh, $data);
										fclose($fh);
										//uncompressing...
										$gzip=file_get_contents($file);
										$rest = substr($gzip, -4);
										$tmptmp = unpack("V", $rest);
										$GZFileSize = end($tmptmp);
										$zd = gzopen($file, "r");
										$contents = gzread($zd, $GZFileSize);
										gzclose($zd);
										$unc_file = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day;
										$fh = fopen($unc_file, 'w');
										fwrite($fh, $contents);
										fclose($fh);
										if($_POST['ftpCheck']=='FTP'){
											//write to text file
											$textfile = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".part";
										}else{
											$textfile = $save_dir.'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".txt";
											$files_tmp[]= array(
												"abs_path" => $save_dir. 'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".txt", 
												"rel_path" => 'spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".txt");
										}
										
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
										unlink($unc_file);
										unlink($file);								
									//////////////////////////////////////////////////
									/////////////end write data to file///////////////
									//////////////////////////////////////////////////
										if(filesize($textfile)>0){
											$file_flg = true;
											if($_POST['ftpCheck']=='FTP'){
												//////////////////////////////////////////////////
												///////ftp transfer to asbad04.mcbad.net//////////
												//////////////////////////////////////////////////
												//open ftp
												$conn_id=ftp_connect($ftp_host);
												ftp_login($conn_id, $ftp_username, $ftp_password);
												ftp_pasv($conn_id, true);
												if( !ftp_put($conn_id, $ftp_target . '/spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".part", $textfile, FTP_BINARY)) {
													$logs .= "FTP failed to upload ".$textfile." file. \\n";
												}else{
													sleep(5);
													ftp_rename($conn_id, $ftp_target . '/spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".part",$ftp_target . '/spotify_toplist_for_'.strtoupper($country)."_".$year."_".$month."_".$day.".txt");
													$logs .= "upload to FTP SERVER success! \\n";
													unlink($textfile);
												}
												ftp_close($conn_id);
												//////////////////////////////////////////////////
												///////end ftp transfer to asbad04.mcbad.net//////
												//////////////////////////////////////////////////
											}else{
												$logs .= "success created! \\n";
											}
										}else{
											$logs .="Spotify transfer empty file! \\n";
										}
									}else{
										$logs .="no data from Spotify-API \\n";
									}
									writelogs($logs);

								}
								if($_POST['ftpCheck']=='FTP'){
									rmdirr($rel_dir);
								}else{
									if($file_flg){
										//download to local Computer
										create_zip($files_tmp,$tmp_dir.'/spotifyStreamingDaten.zip',true);
										/*
										$re_url = substr('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'], 0, strlen('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'])-9) . 'spotifydwn.php?file='.urlencode($rel_dir.'/spotifyStreamingDaten.zip').'&tmp_dir='.urlencode($tmp_dir);
										*/
										$re_url = 'http://'.$_SERVER['HTTP_HOST'] . '/spotifydwn.php?file='.urlencode($rel_dir.'/spotifyStreamingDaten.zip').'&tmp_dir='.urlencode($rel_dir);
										echo '<script>';
										echo 'window.open("'.$re_url.'","_blank");';
										echo '</script>';
									}else{
										rmdirr($rel_dir);
									}
								}
							}
						}						
						/**
						END SPOTIFY DATEN
						*/
						}
						?>
						<!----------------------END SPOTIFY-------------------------->
						<!----------------------ITUNES-------------------------->
						<div class="tab-pane " ng-class="itunesTab">
							<div class="row">
								<div class="col-md-5">
									<h4>
										<span class="glyphicon icon-setting"></span>
										Itunes Data: API Parameters setting
									</h4>
									<form class="form-horizontal" role="form" id="itunesFrom" action="main.php" method="post">
										<div class="form-group">
										    <label for="itunesfrmGeo" class="col-md-1 control-label">Geometry:</label>
										</div>
										<div class="form-group">
											<div class="col-md-12">									   
											    <select id="itunesfrmGeo" name="itunesfrmGeo" class="form-control" ng-model="varitunesGeo" 
											    <?php 
												//init Spotify country
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'itunes'){													
													echo 'ng-init="varitunesGeo=\''.$_POST['itunesfrmGeo'].'\'"';
												}else{
													echo 'ng-init="varitunesGeo = \'EUR\'"';
												}   
												?> 
											    >
											    	<option value="EUR">EUR ( Europa )</option>
													<option value="APAC">APAC ( Asia & Pacific )</option>
													<option value="eur-applemusic">EUR-STREAM ( Europa stream data )</option>
													<option value="apac-applemusic">APAC-STREAM ( Asia & Pacific stream data )</option>
											    </select>
											</div>
									  	</div>
									  	<div class="form-group">
									  		<label for="itunesfrmYear" class="col-md-1 control-label">Year:</label>
									  		<div class="col-md-3">
									  			<select id="itunesfrmYear" name="itunesfrmYear" class="form-control" ng-model="varitunesYear" 
									  			<?php 
												//init Spotify Year
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'itunes'){													
													echo 'ng-init="varitunesYear=\''.$_POST['itunesfrmYear'].'\'"';
												}else{
													echo 'ng-init="varitunesYear=curYear"';
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
									  		<label for="itunesfrmMonth" class="col-md-1 control-label">Month:</label>
									  		<div class="col-md-3">
									  			<select id="itunesfrmMonth" name="itunesfrmMonth" class="form-control" ng-model="varitunesMonth"
									  			<?php 
												//init Spotify Month
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'itunes'){													
													echo 'ng-init="varitunesMonth=\''.$_POST['itunesfrmMonth'].'\'"';
												}else{
													echo 'ng-init="varitunesMonth=curMonth"';
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
									  		<label for="itunesfrmDay" class="col-md-1 control-label">Day:</label>
									  		<div class="col-md-3">
									  			<select id="itunesfrmDay" name="itunesfrmDay" class="form-control" ng-model="varitunesDay"
									  			<?php 
												//init Spotify Day
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'itunes'){													
													echo 'ng-init="varitunesDay=\''.$_POST['itunesfrmDay'].'\'"';
												}else{
													echo 'ng-init="varitunesDay=curDay"';
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
									  	<div class="btn-group col-md-12">
									  		<button class="btn btn-default {{itunesftpbutton}}" type="button" ng-click="itunesftpbutton='active';ituneslocalbutton='';itunesftpCheck='FTP'"
									  		<?php 
												//init ftp
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'itunes' && isset($_POST['itunesftpCheck']) && $_POST['itunesftpCheck']=='LOCAL'){
													echo 'ng-init="itunesftpbutton=\'\'"';
												}else{
													echo 'ng-init="itunesftpbutton=\'active\'"';
												}   
												?>									  			
									  		data-toggle="tooltip" title="Upload direct to FTP">FTP<span class="glyphicon icon-ftp"></span></button>

									  		<button class="btn btn-default {{ituneslocalbutton}}" type="button" ng-click="itunesftpbutton='';ituneslocalbutton='active';itunesftpCheck='LOCAL'"
									  		<?php 
												//init non ftp
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'itunes' && isset($_POST['itunesftpCheck']) && $_POST['itunesftpCheck']=='LOCAL'){
													echo 'ng-init="ituneslocalbutton=\'active\'"';
												}else{
													echo 'ng-init="ituneslocalbutton=\'\'"';
												}   
												?>
									  		data-toggle="tooltip" title="Download to local" >local<span class="glyphicon icon-local"></span></button>
									  	</div>
									  	<input type="text" name="itunesftpCheck" ng-model="itunesftpCheck" style="display:none" 
									  	<?php 
												//init FTP
												if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'itunes' && isset($_POST['itunesftpCheck']) && $_POST['itunesftpCheck']=='LOCAL'){
													echo 'ng-init="itunesftpCheck=\'LOCAL\'"';
												}else{
													echo 'ng-init="itunesftpCheck=\'FTP\'"';
												}   
												?>
										>
									  </div>
									  <div class="form-group {{itunesftpPfad?'':error_status}}">
									  	<div class="col-md-12">
									  	 <input type="text" class="form-control" id="itunesfrmFTPdirectory" name="itunesfrmFTPdirectory" value={{itunesftpPfad}} ng-model="itunesftpPfad" style="display:{{(itunesftpbutton=='active')?display_block:display_none}}"
									  	 <?php 
											//init Spotify FTP URL
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'itunes'){													
												echo 'ng-init="itunesftpPfad=\''.$_POST['itunesfrmFTPdirectory'].'\'"';
											}else{
												echo 'ng-init="itunesftpPfad=\'/NFS/ASBAD04/FTP/MediaControl/GBR/Apple/doppelt/test\'"';
											}   
											?>
									  	 >
									  	</div>
									  	<div class="col-md-12">
									  		<div class="alert alert-danger" style="display:{{((itunesftpPfad && itunesftpbutton=='active') || ituneslocalbutton=='active')?display_none:display_block}}">
									  			Please insert FTP Directory
									  		</div>
									  	</div>
									  </div>
									  	<!--hidden pamrameters-->
									  	<div class="form-group" style="display:none">
									  		
									  		<input type="checkbox" id="itunesmultiModeCheckBox" name="itunesmultiModeCheckBox"
									  		<?php 
											//init Spotify Multimode
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'itunes' && isset($_POST['itunesmultiModeCheckBox']) && $_POST['itunesmultiModeCheckBox']=='on'){
												echo 'checked';
											}  
											?>
											> Multi download</input>
									  		<input id="itunesdwnitems" name="itunesdwnitems" class="form-control" type="text" ng-model="itunesdwnString">
									  		<input id="tabname" name="tabname" class="form-control" type="text" value="itunes">
									  	</div>
									  	<hr>
										  <div class="form-group">
										  	<div class="col-md-3">
										  		<button type="submit" class="btn btn-primary" ng-disabled="(!itunesftpPfad) || (itunesmultiMode && !itunesdownloadLists.length)">Execute<span class="glyphicon icon-submit"></span></button>
										  	</div>
										  </div>
									</form>
									<!--///////////////////FORM//////////////////////////-->
								</div>
								<div class="col-md-3">
									<h4>
										<span class="glyphicon icon-filedwn"></span>
										Files Download info:
									</h4>
									<div class="row">
										<label class="col-md-5">
											mediacontrol_{{getDwlFilename(varitunesGeo) | lowercase}}{{varitunesYear}}{{varitunesMonth}}{{varitunesDay}}_xxx.txt.zip
										</label>										
									</div>
									<div class="row">
										<div class="col-md-6">
											<input type="checkbox" ng-model="itunesmultiMode" ng-click="itunesmultiModeClick()" 
											<?php 
											//init Spotify Multimode
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'itunes' && isset($_POST['itunesmultiModeCheckBox']) && $_POST['itunesmultiModeCheckBox']=='on'){													
												echo 'ng-init="itunesmultiMode=true"';
											}else{
												echo 'ng-init="itunesmultiMode = false"';
											}   
											?>
												> Multi download</input>
										</div>
										<div class="col-md-6">											
											<button type="button" class="btn btn-default btn-xs pull-right" style="display:{{itunesmultiMode?display_block:display_none}}" ng-click="itunes_add_dwnitem()"><span class="glyphicon icon-add"></span>&nbsp;&nbsp;&nbsp;&nbsp;Add</button>
										</div>
									</div>
									<div class="divider tiny"></div>
									<div class="row {{(itunesmultiMode && !itunesdownloadLists.length)? error_status: ''}}" style="display:{{itunesmultiMode?display_block:display_none}}">
										<div class="col-md-12">
											<select multiple style="width:100%;height:156px;" class="form-control has-error" id="itunesdownloadList" 
											<?php 
											//init Spotify Multimode
											if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'itunes' && isset($_POST['itunesmultiModeCheckBox']) && $_POST['itunesmultiModeCheckBox']=='on'){
												$tmp = implode("','",explode(',', $_POST['itunesdwnitems']));		
												echo 'ng-init="itunesdwnString = \''.$_POST['itunesdwnitems'].'\'; itunesdownloadLists = [\''.$tmp.'\'];"';
											}else{
												echo 'ng-init="itunesdwnString = \'\'; itunesdownloadLists = [];"';
											}   
											?>
											>
												<option ng-repeat="itunesdwnitem in itunesdownloadLists" value={{itunesdwnitem}}>{{itunesdwnitem}}</option>
											</select>
										</div>
										<div class="col-md-12">
									  		<div class="alert alert-danger" style="display:{{itunesdownloadLists.length?display_none:display_block}}">
									  			Please add files to download
									  		</div>
									  	</div>
									</div>
									<div class="divider tiny"></div>
									<div class="row">
										<div class="col-md-12">
											<button type="button" class="btn btn-default btn-xs pull-right" style="display:{{itunesmultiMode?display_block:display_none}}" ng-click="itunesremove_dwnitem()"><span class="glyphicon icon-remove"></span>&nbsp;&nbsp;Remove</button>
										</div>
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
											<textarea class="form-control" rows="13" id="itunesLogs"></textarea>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php 						
						if($_SERVER['REQUEST_METHOD']=='POST'){
						/**
						ITUNES DATEN
						*/
						if(isset($_POST['tabname']) && $_POST['tabname'] == 'itunes'){
							$logs ='';
							if(!isset($_POST['itunesmultiModeCheckBox'])){
								//flush_buffers();
								//Single mode
								$geo 		= $_POST['itunesfrmGeo'];
								$year 		= $_POST['itunesfrmYear'];
								$month 		= $_POST['itunesfrmMonth'];
								$day 		= $_POST['itunesfrmDay'];
								$rel_dir 	= time();
								$tmp_dir 	= dirname(__FILE__).'/itunes_'.$rel_dir;
								$old = umask(0);
								mkdir($tmp_dir, 0777);
								umask($old);
								$save_dir	= $tmp_dir . '/';
								$ftp_target  = $_POST['itunesfrmFTPdirectory'];								
								$logs = '+ mediacontrol_'.itunesGetFilenameFromString($geo).$year.$month.$day.'_'.date('YmdHis',$rel_dir).'.txt.zip'." :  " ;																
								//////////////////////////////////////////////////
								///////////////get Chart data/////////////////////
								//////////////////////////////////////////////////
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, "https://reportingitc-charts.apple.com/ITunesChartAutoIngestServices/autoIngest.itc?userId=mike.timm@gfk.com&password=7ab0028AB&req=download&geo=".$geo."&requestTime=".$month."-".$day."-".$year);
								curl_setopt($ch, CURLOPT_PROXY, $proxy);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
								$data = curl_exec($ch);
								$info = curl_getinfo($ch);
								curl_close($ch);
								//////////////////////////////////////////////////
								/////////////END get Chart data///////////////////
								//////////////////////////////////////////////////

								//////////////////////////////////////////////////
								/////////////write data to file///////////////////
								//////////////////////////////////////////////////
								if($info['http_code']==200){
									//success => write to file
									$file = $save_dir.'mediacontrol_'.itunesGetFilenameFromString($geo).$year.$month.$day.'_'.date('YmdHis',$rel_dir).'.txt.zip';
									$fh = fopen($file, 'w');
									fwrite($fh, $data);
									fclose($fh);																
								//////////////////////////////////////////////////
								/////////////end write data to file///////////////
								//////////////////////////////////////////////////
										if($_POST['itunesftpCheck']=='FTP'){
											//////////////////////////////////////////////////
											///////ftp transfer to asbad04.mcbad.net//////////
											//////////////////////////////////////////////////
											//open ftp
											$conn_id=ftp_connect($ftp_host);
											ftp_login($conn_id, $ftp_username, $ftp_password);
											ftp_pasv($conn_id, true);
											if( !ftp_put($conn_id, $ftp_target . '/mediacontrol_'.itunesGetFilenameFromString($geo).$year.$month.$day."_".date('YmdHis',$rel_dir).'.txt.zip', $file, FTP_BINARY)) {
												$logs .= "FTP failed to upload ".$file." file.";
											}else{												
												$logs .= "upload to FTP SERVER success! \\n";
												//////////////////////////////////////////////////
												///////////////get unzip key /////////////////////
												//////////////////////////////////////////////////
												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, "https://reportingitc-charts.apple.com/ITunesChartAutoIngestServices/autoIngest.itc?userId=mike.timm@gfk.com&password=7ab0028AB&req=decryption_key");
												curl_setopt($ch, CURLOPT_PROXY, $proxy);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
												curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
												$data = curl_exec($ch);
												curl_close($ch);
												//////////////////////////////////////////////////
												/////////////END get unzip key ///////////////////
												//////////////////////////////////////////////////
												$logs .= "------------------------------------------------------\\n";
												$logs .= "key to unzip : ".$data;
											}
											ftp_close($conn_id);
											//////////////////////////////////////////////////
											///////end ftp transfer to asbad04.mcbad.net//////
											//////////////////////////////////////////////////
											rmdirr('itunes_'.$rel_dir);
										}else{
											//download to local Computer
											/*
											$re_url = substr('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'], 0, strlen('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'])-9) . 'spotifydwn.php?file='.urlencode('itunes_'.$rel_dir.'/mediacontrol_'.strtolower($geo)."_master_trans_".$year.$month.$day.'_'.date('YmdHis',$rel_dir).'.txt.zip').'&tmp_dir='.urlencode($tmp_dir);
											*/
											$re_url = 'http://'.$_SERVER['HTTP_HOST'] . '/spotifydwn.php?file='.urlencode('itunes_'.$rel_dir.'/mediacontrol_'.itunesGetFilenameFromString($geo).$year.$month.$day.'_'.date('YmdHis',$rel_dir).'.txt.zip').'&tmp_dir='.urlencode('itunes_'.$rel_dir);
											echo '<script>';
											echo 'window.open("'.$re_url.'","_blank");';
											echo '</script>';
											$logs .="success!! \\n";
											//////////////////////////////////////////////////
											///////////////get unzip key /////////////////////
											//////////////////////////////////////////////////
											$ch = curl_init();
											curl_setopt($ch, CURLOPT_URL, "https://reportingitc-charts.apple.com/ITunesChartAutoIngestServices/autoIngest.itc?userId=mike.timm@gfk.com&password=7ab0028AB&req=decryption_key");
											curl_setopt($ch, CURLOPT_PROXY, $proxy);
											curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
											curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
											$data = curl_exec($ch);
											curl_close($ch);
											//////////////////////////////////////////////////
											/////////////END get unzip key ///////////////////
											//////////////////////////////////////////////////
											$logs .= "------------------------------------------------------\\n";
											$logs .= "key to unzip : ".$data;
										}
																	
								}else{
									$logs .="no data from ITUNES-API";
									rmdirr('itunes_'.$rel_dir);
								}
								writelogsitunes($logs);
							}else{
								//Multi mode
								$items_arr = explode(',', $_POST['itunesdwnitems']);
								$rel_dir 	= time();
								$tmp_dir 	= dirname(__FILE__).'/itunes_'.$rel_dir;
								$old = umask(0);
								mkdir($tmp_dir, 0777);
								umask($old);
								$save_dir	= $tmp_dir . '/';
								$files_tmp	= array();
								$file_flg = false;

								foreach ($items_arr as $value) {
									//flush_buffers();
									$geo 		= substr($value, 0,strlen($value)-8);
									$year 		= substr($value, -8,4);
									$month 		= substr($value, -4,2);
									$day 		= substr($value, -2);									
									
									$ftp_target = $_POST['itunesfrmFTPdirectory'];
									$logs .= '+ mediacontrol_'.itunesGetFilenameFromString($geo).$year.$month.$day.'_'.date('YmdHis',$rel_dir).'.txt.zip'." :  " ;
									//////////////////////////////////////////////////
									///////////////get itunes data////////////////////
									//////////////////////////////////////////////////
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, "https://reportingitc-charts.apple.com/ITunesChartAutoIngestServices/autoIngest.itc?userId=mike.timm@gfk.com&password=7ab0028AB&req=download&geo=".$geo."&requestTime=".$month."-".$day."-".$year);
									curl_setopt($ch, CURLOPT_PROXY, $proxy);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
									$data = curl_exec($ch);
									$info = curl_getinfo($ch);
									curl_close($ch);
									//////////////////////////////////////////////////
									/////////////END get itunes data//////////////////
									//////////////////////////////////////////////////

									//////////////////////////////////////////////////
									/////////////write data to file///////////////////
									//////////////////////////////////////////////////
									if($info['http_code']==200){
										//downloading...
										$file = $save_dir.'mediacontrol_'.itunesGetFilenameFromString($geo).$year.$month.$day.'_'.date('YmdHis',$rel_dir).'.txt.zip';
										$fh = fopen($file, 'w');
										fwrite($fh, $data);
										fclose($fh);															
									//////////////////////////////////////////////////
									/////////////end write data to file///////////////
									//////////////////////////////////////////////////
											if($_POST['itunesftpCheck']=='FTP'){
												//////////////////////////////////////////////////
												///////ftp transfer to asbad04.mcbad.net//////////
												//////////////////////////////////////////////////
												//open ftp
												$conn_id=ftp_connect($ftp_host);
												ftp_login($conn_id, $ftp_username, $ftp_password);
												ftp_pasv($conn_id, true);
												if( !ftp_put($conn_id, $ftp_target . '/mediacontrol_'.itunesGetFilenameFromString($geo).$year.$month.$day.'_'.date('YmdHis',$rel_dir).".txt.zip", $file, FTP_BINARY)) {
													$logs .= "FTP failed to upload ".$file." file. \\n";
												}else{													
													$logs .= "upload to FTP SERVER success! \\n";	
												}
												ftp_close($conn_id);	
												//////////////////////////////////////////////////
												///////end ftp transfer to asbad04.mcbad.net//////
												//////////////////////////////////////////////////
											}else{
												$files_tmp[]= array(
												"abs_path" => $file, 
												"rel_path" => 'mediacontrol_'.itunesGetFilenameFromString($geo).$year.$month.$day.'_'.date('YmdHis',$rel_dir).'.txt.zip');
												$logs .= "success! \\n";
											}
											$file_flg = true;										
									}else{
										$logs .="no data from ITUNES-API \\n";
									}
								}
								if($file_flg){
								//////////////////////////////////////////////////
								///////////////get unzip key /////////////////////
								//////////////////////////////////////////////////
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, "https://reportingitc-charts.apple.com/ITunesChartAutoIngestServices/autoIngest.itc?userId=mike.timm@gfk.com&password=7ab0028AB&req=decryption_key");
								curl_setopt($ch, CURLOPT_PROXY, $proxy);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
								$data = curl_exec($ch);
								curl_close($ch);
								//////////////////////////////////////////////////
								/////////////END get unzip key ///////////////////
								//////////////////////////////////////////////////
								$logs .= "------------------------------------------------------\\n";
								$logs .= "key to unzip : ".$data;
								}
								writelogsitunes($logs);

								if($_POST['itunesftpCheck']=='FTP'){
									rmdirr('itunes_'.$rel_dir);
								}else{
									if($file_flg){
										//download to local Computer										
										create_zip($files_tmp,$tmp_dir.'/itunesDaten.zip',true);
										/*
										$re_url = substr('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'], 0, strlen('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'])-9) . 'spotifydwn.php?file='.urlencode('itunes_'.$rel_dir.'/itunesDaten.zip').'&tmp_dir='.urlencode($tmp_dir);
										*/										
										$re_url = 'http://'.$_SERVER['HTTP_HOST'] . '/spotifydwn.php?file='.urlencode('itunes_'.$rel_dir.'/itunesDaten.zip').'&tmp_dir='.urlencode('itunes_'.$rel_dir);
										echo '<script>';
										echo 'window.open("'.$re_url.'","_blank");';
										echo '</script>';
									}else{
										rmdirr('itunes_'.$rel_dir);
									}
								}
							}
						}						
						/**
						END ITUNES DATEN
						*/
						}
						?>
						<!----------------------END ITUNES-------------------------->
						<div class="tab-pane " ng-class="sevenDigitalTab">
							nicht verf&uuml;gbar
						</div>
					</div>

				</div>
		</div>
	</div>
</body>
</html>