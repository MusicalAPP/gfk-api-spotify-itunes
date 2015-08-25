<?php
error_reporting(0);
set_include_path(dirname(__FILE__) . '/lib/pear/');

require_once ('config/dbconfig.php');
require_once ('MySQLSession.class.php');
require_once('lib/pear/MDB2.php');
	$sess = new MySQLSession(
	$session_user,
	$session_pass,
    $session_hostname,
	$session_dbname,
	7200,
    'apisess',
    $session_tbl
);
if(!isset($_SESSION['login_widgetapi']) || $_SESSION['login_widgetapi'] != true){
	header('Location: index.php');
	exit();
}
require_once('config/function_widgetapi.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.min.js"></script>    
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="core.css">
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
	<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
	<script src="js/underscore-min.js"></script>
	<script src="js/bootbox.min.js"></script>
    <script type="text/javascript">
    var tabApp = angular.module('tabbedApp', []);
	tabApp.controller('tabController', ['$scope', function($scope) {
		//MYSQL tab
		<?php 
		$ar_tmp = get_chart_modulID();
		echo '$scope.module =[';
		$index_md = 0;
		foreach ($ar_tmp as $value) {
			echo '{index_md: '.$index_md.', id: '.$value.',landname:\''.$land_ref_arr[$charts_modul_ref_land[$value]].'\',modulname: \''.$charts_modul_ref[$value].'\',';
			echo 'timeunit: [';
			$ival_tmp = get_chart_ival($value);
			$index_tu = 0;
			foreach ($ival_tmp as $key => $row) {
				echo "{index_tu: ".$index_tu.",id_tu: '".$key."', name_tu: '".$row."'},";
				$index_tu++;
			}
			echo '],';
			echo 'timekey: [';
			foreach ($ival_tmp as $key => $row) {
				$timekey_tmp = get_chart_time_key($value,$key);
				$index = 0;
				foreach ($timekey_tmp as  $keys) {
					echo "{index_tk: ".$index.", id_tk: '".$key."', key_tk: ".$keys.", name_tk: '".convert_zeitekey($keys,$key)."'},";
					$index++;
				}
			}
			echo ']';
			echo '},';
			$index_md++;
		}
		echo '];';
		if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['tabname'] == 'manageTab'){
			echo '$scope.current_modul = _.filter($scope.module, function(obj){ return obj.id=='.$_POST['manageTabModulId'].'});';
			echo '$scope.current_modul_index = $scope.current_modul[0].index_md;';
			echo '$scope.current_modul_time_unit = _.filter($scope.current_modul[0].timeunit, function(obj){ return obj.id_tu==\''.$_POST['manageTabTimeUnit'].'\'});';
			echo '$scope.current_modul_time_unit_index = $scope.current_modul_time_unit[0].index_tu;';
			echo '$scope.current_modul_time_key = _.where($scope.current_modul[0].timekey, {id_tk: \''.$_POST['manageTabTimeUnit'].'\' ,key_tk:'.$_POST['manageTabTimeKey'].'});';
			echo '$scope.current_modul_time_key_index = $scope.current_modul_time_key[0].index_tk;';
		}else{			
			echo '$scope.current_modul_index = 0;';
			echo '$scope.current_modul_time_unit_index = 0;';
			echo '$scope.current_modul_time_key_index = 0;';
		}
		?>	
		$scope.changemodul = function changemodul(){
			$scope.manageTabTimeUnit_val = $scope.manageTabModulId_val.timeunit[0];
			$scope.manageTabTimeKey_val  = _.filter($scope.manageTabModulId_val.timekey, function(obj){return obj.id_tk=$scope.manageTabTimeUnit_val.id_tu})[0];
		}
		$scope.changeunit = function changeunit(){
			$scope.manageTabTimeKey_val = _.filter($scope.manageTabModulId_val.timekey, function(obj){return obj.id_tk=$scope.manageTabTimeUnit_val.id_tu})[0];
		}
		//EXASOL tab
		<?php 
		$ar_tmp = get_exa_chart_modul_id();
		echo '$scope.exa_module =[';
		$index_md = 0;
		foreach ($ar_tmp as $value) {
			echo '{index_md: '.$index_md.', id: '.$value.',landname:\''.$land_ref_arr[$charts_modul_ref_land[$value]].'\',modulname: \''.$charts_modul_ref[$value].'\',';
			echo 'timeunit: [';
			$ival_tmp = get_exa_chart_ival($value);
			foreach ($ival_tmp as $key => $row) {
				echo "{index_tu: ".$index_tu.",id_tu: '".$key."', name_tu: '".$row."'},";
				$index_tu++;
			}
			echo '],';
			echo 'timekey: [';
			foreach ($ival_tmp as $key => $row) {
				$timekey_tmp = get_exa_chart_time_key($value,$key);
				$index = 0;
				foreach ($timekey_tmp as  $keys) {
					echo "{index_tk: ".$index.", id_tk: '".$key."', key_tk: ".$keys.", name_tk: '".convert_zeitekey($keys,$key)."'},";
					$index++;
				}
			}
			echo ']';
			echo '},';
			$index_md++;
		}
		echo '];';
		echo '$scope.current_exa_modul_index = 0;';
		echo '$scope.current_exa_modul_time_unit_index = 0;';
		echo '$scope.current_exa_modul_time_key_index = 0;';
		?>
		$scope.top_chk_box = [{'top':10}];
		$scope.change_exa_modul = function change_exa_modul(){
			$scope.exasolTabTimeUnit_val = $scope.exasolTabModulId_val.timeunit[0];
			$scope.exasolTabTimeKey_val  = _.filter($scope.exasolTabModulId_val.timekey, function(obj){return obj.id_tk=$scope.exasolTabTimeUnit_val.id_tu})[0];
		}
		$scope.change_exa_unit = function change_exa_unit(){
			$scope.exasolTabTimeKey_val = _.filter($scope.exasolTabModulId_val.timekey, function(obj){return obj.id_tk=$scope.exasolTabTimeUnit_val.id_tu})[0];
		}

	}]);
	function importchart(){
		var scope = angular.element('[ng-controller=tabController]').scope();
		//check timekey in sql
		var modul_tmp = _.findWhere(scope.module, {id: scope.exasolTabModulId_val.id });
		if(modul_tmp == undefined){
			//AJAX insert
			var xmlhttp=new XMLHttpRequest();
			xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState==4 && xmlhttp.status==200) {
					$('#loading-indicator').hide();
					document.getElementById("exasollogs").innerHTML=xmlhttp.responseText;
				}
			}
			xmlhttp.open("POST","loader.php?action=insert&modulID="+scope.exasolTabModulId_val.id+
									"&timeunit="+scope.exasolTabTimeUnit_val.id_tu+
									"&timekey="+scope.exasolTabTimeKey_val.key_tk+
									"&limit="+scope.exasolTabTop_val.top,true);
			$('#loading-indicator').show();
			xmlhttp.send();
		}else{
			if(_.findWhere(modul_tmp.timekey, {id_tk: scope.exasolTabTimeUnit_val.id_tu, key_tk: scope.exasolTabTimeKey_val.key_tk }) != undefined){
				bootbox.confirm(scope.exasolTabModulId_val.landname+" "+
								scope.exasolTabModulId_val.modulname+" "+
								scope.exasolTabTimeUnit_val.name_tu+" "+ 
								scope.exasolTabTimeKey_val.name_tk+" "+ 
								"<br/>This chart has been loaded in MySQL, are you sure to update it?", function(result) {
					if (result) {
						//AJAX insert
						var xmlhttp=new XMLHttpRequest();
						xmlhttp.onreadystatechange=function() {
							if (xmlhttp.readyState==4 && xmlhttp.status==200) {
								$('#loading-indicator').hide();
								document.getElementById("exasollogs").innerHTML=xmlhttp.responseText;
							}
						}
						xmlhttp.open("POST","loader.php?action=update&modulID="+scope.exasolTabModulId_val.id+
												"&timeunit="+scope.exasolTabTimeUnit_val.id_tu+
												"&timekey="+scope.exasolTabTimeKey_val.key_tk+
												"&limit="+scope.exasolTabTop_val.top,true);
						$('#loading-indicator').show();
						xmlhttp.send();
					} else {
						return true;
					}
				});
			}else{
				//AJAX import
				var xmlhttp=new XMLHttpRequest();
				xmlhttp.onreadystatechange=function() {
					if (xmlhttp.readyState==4 && xmlhttp.status==200) {
						$('#loading-indicator').hide();
					    document.getElementById("exasollogs").innerHTML=xmlhttp.responseText;
					}
				}
				xmlhttp.open("POST","loader.php?action=insert&modulID="+scope.exasolTabModulId_val.id+
									"&timeunit="+scope.exasolTabTimeUnit_val.id_tu+
									"&timekey="+scope.exasolTabTimeKey_val.key_tk+
									"&limit="+scope.exasolTabTop_val.top,true);
				$('#loading-indicator').show();
				xmlhttp.send();
			}
		}
		}
    </script>
    <script type="text/javascript">
	$(document).ready(function(){
	    $('[data-toggle="tooltip"]').tooltip();
	});
	</script>
</head>
<body >	
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
					<li ng-class="manageTab" ng-click="manageTab = 'active'; exasolTab = '';" ng-init="manageTab = 'active';  exasolTab = '';">
					<a class="active " data-toggle="tab" href="#">
					<span class="glyphicon icon-mysql-widgetapi"></span>
					<span>Manage Charts</span>
					</a>
					</li>
					<li ng-class="exasolTab" ng-click="manageTab = ''; exasolTab = 'active';">
					<a data-toggle="tab" href="#">
					<span class="glyphicon icon-exasol-widgetapi"></span>
					<span>import new Charts</span>
					</a>
					</li>						
				</ul>
				<!-- Tabs contents begin here -->
				<div class="tab-content ">
					<!--Charts manage Tab -->
					<div class="tab-pane "  ng-class="manageTab">
						<div class="row">
						<div class="col-md-12">
							<h4>	
								<span class="glyphicon icon-setting"></span>							
								Charts Parameter setting:
							</h4>
							<div class="divider"></div>
							<form role="form" id="manageTabFrom" action="main_widgetapi.php" method="post">
								<div class="form_group col-md-2">
							  		<label class="control-label" for="manageTabModulId">Charts Modul:</label>	
								    <select id="manageTabModulId" 
								    		name="manageTabModulId" 
								    		class="form-control" 
								    		ng-model="manageTabModulId_val" 
								     		ng-init="manageTabModulId_val = module[current_modul_index]"
								     		ng-options="md.id for md in module track by md.id"
								     		ng-change="changemodul()"
								     		>
									</select>
							  	</div>
								<div class="form_group col-md-2">
								    <label class="control-label">Land:</label>								
								    <input type="text" class="form-control" value="{{manageTabModulId_val.landname}}" disabled>
							  	</div>
							  	<div class="form_group col-md-2">
								    <label class="control-label">Chart format:</label>								
								    <input type="text" class="form-control"value="{{manageTabModulId_val.modulname}}" disabled>
							  	</div>
							  	
							  	<div class="form_group col-md-2">
							  		<label class="control-label" for="manageTabTimeUnit">Time Unit:</label>							  	
								    <select id="manageTabTimeUnit" 
								    		name="manageTabTimeUnit" 
								    		class="form-control" 
								    		ng-model="manageTabTimeUnit_val" 
								    		ng-options="tu.name_tu for tu in manageTabModulId_val.timeunit track by tu.id_tu"
								    		ng-init="manageTabTimeUnit_val=manageTabModulId_val.timeunit[current_modul_time_unit_index]"
								    		ng-change="changeunit()">
									</select>
							  	</div>
							  	<div class="form_group col-md-2">
							  		<label class="control-label" for="manageTabTimeKey">Time Key:</label>
							  		<select id="manageTabTimeKey" 
											name="manageTabTimeKey"
											class="form-control" 
											ng-model="manageTabTimeKey_val" 
											ng-options="tk.name_tk for tk in manageTabModulId_val.timekey | filter:{id_tk:manageTabTimeUnit_val.id_tu} track by tk.key_tk" 
											ng-init="manageTabTimeKey_val = manageTabModulId_val.timekey[current_modul_time_key_index]">
									</select>		
							  	</div>
							  	<div class="form_group" style="display:none">
							  		<input id="tabname" name="tabname" class="form-control" type="text" value="manageTab">
							  	</div>
							  	<div class="form_group col-md-2 exebtn_widget">							  		
							  		<button type="submit" class="btn btn-primary ">Execute<span class="glyphicon icon-submit"></span></button>
								</div>
							</form>
						</div>
						</div>
					<div class="divider"></div>
					<div class="row">
						<div class="col-md-12">
					<?php 						
						if($_SERVER['REQUEST_METHOD']=='POST'){
						/**
						Manage Charts Tab
						*/
						if(isset($_POST['tabname']) && $_POST['tabname'] == 'manageTab'){
							echo '<table class="table table-striped table-hover table-responsive">';
							echo '<thead>';
							echo '<tr class="success">';
							echo '<th class="col-md-1">Rank</th>';
							echo '<th class="col-md-1">Title</th>';
							echo '<th class="col-md-1">Artist</th>';
							echo '<th class="col-md-3">Itunes link</th>';
							echo '<th class="col-md-3">Amazon phys. link</th>';
							echo '<th class="col-md-3">Amazon dwn. link</th>';
							echo '<th class="col-md-3">Video link</th>';
							echo '</tr>';
							echo '</thead>';
							
							echo '<tbody>';
							$ar = get_chart($_POST['manageTabModulId'],$_POST['manageTabTimeUnit'],$_POST['manageTabTimeKey']);
							$rank = 1;
							foreach ($ar as $value) {
								echo '<tr>';
								echo '<td>';
								echo $value['rank'];
								echo '</td>';
								echo '<td>';
								echo $value['titel'];
								echo '</td>';
								echo '<td>';
								echo $value['artist'];
								echo '</td>';
								echo '<td>';
								echo '<a class="itunes_link" href="#" id="itunes_link" data-type="textarea" data-pk="'.$rank.'" data-url="mysql_chart_update.php" data-title="Edit Itunes link">'.$value['itunes_link'].'</a>';
								echo '<a href="'.$value['itunes_link'].'" target="_blank"><span class="glyphicon icon-link-widgetapi"></span></a>';
								echo '</td>';
								echo '<td>';
								echo '<a class="amazon_phys_link" href="#" id="amazon_phys_link" data-type="textarea" data-pk="'.$rank.'" data-url="mysql_chart_update.php" data-title="Edit Amazon physical link">'.$value['amazon_phys_link'].'</a>';
								echo '<a href="'.$value['amazon_phys_link'].'" target="_blank"><span class="glyphicon icon-link-widgetapi"></span></a>';								
								echo '</td>';
								echo '<td>';
								echo '<a class="amazon_dwn_link" href="#" id="amazon_dwn_link" data-type="textarea" data-pk="'.$rank.'" data-url="mysql_chart_update.php" data-title="Edit Amazon digital link">'.$value['amazon_dwn_link'].'</a>';
								echo '<a href="'.$value['amazon_dwn_link'].'" target="_blank"><span class="glyphicon icon-link-widgetapi"></span></a>';								
								echo '</td>';
								echo '<td>';
								echo '<a class="video_link" href="#" id="video_link" data-type="textarea" data-pk="'.$rank.'" data-url="mysql_chart_update.php" data-title="Edit official Video link">'.$value['video_link'].'</a>';
								echo '<a href="'.$value['video_link'].'" target="_blank"><span class="glyphicon icon-link-widgetapi"></span></a>';								
								echo '</td>';
								echo '</tr>';
								$rank++;
							}
							echo '</tbody>';
							echo '</table>';
						}

						echo '<script type="text/javascript">
								$(document).ready(function(){	';	
						echo '$(\'.itunes_link\').editable({params: {\'modid\': \''.$_POST['manageTabModulId'].'\',\'tunit\': \''.$_POST['manageTabTimeUnit'].'\',\'tkey\': \''.$_POST['manageTabTimeKey'].'\'}});';
						echo '$(\'.amazon_phys_link\').editable({params: {\'modid\': \''.$_POST['manageTabModulId'].'\',\'tunit\': \''.$_POST['manageTabTimeUnit'].'\',\'tkey\': \''.$_POST['manageTabTimeKey'].'\'}});';	
						echo '$(\'.amazon_dwn_link\').editable({params: {\'modid\': \''.$_POST['manageTabModulId'].'\',\'tunit\': \''.$_POST['manageTabTimeUnit'].'\',\'tkey\': \''.$_POST['manageTabTimeKey'].'\'}});';
						echo '$(\'.video_link\').editable({params: {\'modid\': \''.$_POST['manageTabModulId'].'\',\'tunit\': \''.$_POST['manageTabTimeUnit'].'\',\'tkey\': \''.$_POST['manageTabTimeKey'].'\'}});';	
						echo '});
							</script>';						
						}else{
							echo '<table class="table table-striped table-hover table-responsive">';
							echo '<thead>';
							echo '<tr class="success">';
							echo '<th class="col-md-1">Rank</th>';
							echo '<th class="col-md-1">Title</th>';
							echo '<th class="col-md-1">Artist</th>';
							echo '<th class="col-md-3">Itunes link</th>';
							echo '<th class="col-md-3">Amazon phys. link</th>';
							echo '<th class="col-md-3">Amazon dwn. link</th>';
							echo '<th class="col-md-3">Video link</th>';
							echo '</tr>';
							echo '</thead>';
							
							echo '<tbody>';
							$ar = get_chart();
							$rank = 1;
							foreach ($ar as $value) {
								echo '<tr>';
								echo '<td>';
								echo $value['rank'];
								echo '</td>';
								echo '<td>';
								echo $value['titel'];
								echo '</td>';
								echo '<td>';
								echo $value['artist'];
								echo '</td>';
								echo '<td>';
								echo '<a class="itunes_link" href="#" id="itunes_link" data-type="textarea" data-pk="'.$rank.'" data-url="mysql_chart_update.php" data-title="Edit Itunes link">'.$value['itunes_link'].'</a>';
								echo '<a href="'.$value['itunes_link'].'" target="_blank"><span class="glyphicon icon-link-widgetapi"></span></a>';
								echo '</td>';
								echo '<td>';
								echo '<a class="amazon_phys_link" href="#" id="amazon_phys_link" data-type="textarea" data-pk="'.$rank.'" data-url="mysql_chart_update.php" data-title="Edit Amazon physical link">'.$value['amazon_phys_link'].'</a>';
								echo '<a href="'.$value['amazon_phys_link'].'" target="_blank"><span class="glyphicon icon-link-widgetapi"></span></a>';								
								echo '</td>';
								echo '<td>';
								echo '<a class="amazon_dwn_link" href="#" id="amazon_dwn_link" data-type="textarea" data-pk="'.$rank.'" data-url="mysql_chart_update.php" data-title="Edit Amazon digital link">'.$value['amazon_dwn_link'].'</a>';
								echo '<a href="'.$value['amazon_dwn_link'].'" target="_blank"><span class="glyphicon icon-link-widgetapi"></span></a>';								
								echo '</td>';
								echo '<td>';
								echo '<a class="video_link" href="#" id="video_link" data-type="textarea" data-pk="'.$rank.'" data-url="mysql_chart_update.php" data-title="Edit official Video link">'.$value['video_link'].'</a>';
								echo '<a href="'.$value['video_link'].'" target="_blank"><span class="glyphicon icon-link-widgetapi"></span></a>';								
								echo '</td>';
								echo '</tr>';
								$rank++;
							}
							echo '</tbody>';
							echo '</table>';
							echo '<script type="text/javascript">
								$(document).ready(function(){	';	
							echo '$(\'.itunes_link\').editable({params: {\'modid\': \'101\',\'tunit\': \'W\',\'tkey\': \''.get_max_default_key().'\'}});';
							echo '$(\'.amazon_phys_link\').editable({params: {\'modid\': \'101\',\'tunit\': \'W\',\'tkey\': \''.get_max_default_key().'\'}});';	
							echo '$(\'.amazon_dwn_link\').editable({params: {\'modid\': \'101\',\'tunit\': \'W\',\'tkey\': \''.get_max_default_key().'\'}});';
							echo '$(\'.video_link\').editable({params: {\'modid\': \'101\',\'tunit\': \'W\',\'tkey\': \''.get_max_default_key().'\'}});';	
							echo '});
								</script>';
						/**
						END Manage Charts Tab
						*/
						}
					?>
							</div>
						</div>
					</div>
					<!--Exa get new Charts Tab -->
					<div class="tab-pane " ng-class="exasolTab">
						<div class="row">
							<div class="col-md-12">
								<h4>	
										<span class="glyphicon icon-setting"></span>							
										Charts Parameter setting:
									</h4>																
								<div class="divider"></div>

								<form role="form" id="exasolTabFrom">
								<div class="form_group col-md-2">
							  		<label class="control-label" for="exasolTabModulId">Charts Modul:</label>	
								    <select id="exasolTabModulId" 
								    		name="exasolTabModulId" 
								    		class="form-control" 
								    		ng-model="exasolTabModulId_val" 
								     		ng-init="exasolTabModulId_val = exa_module[current_exa_modul_index]"
								     		ng-options="md.id for md in exa_module track by md.id"
								     		ng-change="change_exa_modul()"
								     		>
									</select>
							  	</div>
								<div class="form_group col-md-2">
								    <label class="control-label">Land:</label>								
								    <input type="text" class="form-control" value="{{exasolTabModulId_val.landname}}" disabled>
							  	</div>
							  	<div class="form_group col-md-2">
								    <label class="control-label">Chart format:</label>								
								    <input type="text" class="form-control"value="{{exasolTabModulId_val.modulname}}" disabled>
							  	</div>
							  	
							  	<div class="form_group col-md-2">
							  		<label class="control-label" for="exasolTabTimeUnit">Time Unit:</label>							  	
								    <select id="exasolTabTimeUnit" 
								    		name="exasolTabTimeUnit" 
								    		class="form-control" 
								    		ng-model="exasolTabTimeUnit_val" 
								    		ng-options="tu.name_tu for tu in exasolTabModulId_val.timeunit track by tu.id_tu"
								    		ng-init="exasolTabTimeUnit_val=exasolTabModulId_val.timeunit[current_exa_modul_time_unit_index]"
								    		ng-change="change_exa_unit()">
									</select>
							  	</div>
							  	<div class="form_group col-md-2">
							  		<label class="control-label" for="exasolTabTimeKey">Time Key:</label>
							  		<select id="exasolTabTimeKey" 
											name="exasolTabTimeKey"
											class="form-control" 
											ng-model="exasolTabTimeKey_val" 
											ng-options="tk.name_tk for tk in exasolTabModulId_val.timekey | filter:{id_tk:exasolTabTimeUnit_val.id_tu} track by tk.key_tk" 
											ng-init="exasolTabTimeKey_val = exasolTabModulId_val.timekey[current_exa_modul_time_key_index]">
									</select>
							  	</div>		
							  	<div class="form_group col-md-1">
							  		<label class="control-label" for="exasolTabTop">Top:</label>
							  		<select id="exasolTabTop" 
											name="exasolTabTop"
											class="form-control" 
											ng-model="exasolTabTop_val" 
											ng-options="top.top for top in top_chk_box track by top.top" 
											ng-init="exasolTabTop_val = top_chk_box[0]">
									</select>
							  	</div>					  	
							  	<div class="form_group col-md-1 exebtn_widget">							  		
							  		<button class="btn btn-primary" onclick="importchart()">Execute<span class="glyphicon icon-submit"></span></button>
								</div>
							</form>
							</div>
						</div>
						<div class="divider"></div>
						<div class="divider"></div>
						<div class="row">
							<div class="col-md-12">
								<img src="/icons/ajax-loader.gif" id="loading-indicator" style="display:none" />
								<h4> Logs: </h4>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-12">
								<textarea class="form-control" rows="13" id="exasollogs"></textarea>								
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</body>
</html>