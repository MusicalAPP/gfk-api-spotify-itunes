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
require_once('profil.php');
if(isset($_SESSION['login']) && $_SESSION['login'] == true){
	header('Location: main.php');
	exit();
}
if(isset($_SESSION['login_widgetapi']) && $_SESSION['login_widgetapi'] == true){
	header('Location: main_widgetapi.php');
	exit();
}
if($_SERVER['REQUEST_METHOD']=='POST'){
	if(isset($_POST['usernametxt']) && isset($_POST['passwordtxt'])){
		if(isset($profil[$_POST['usernametxt']]) && $profil[$_POST['usernametxt']]==md5($_POST['passwordtxt'])){
			$_SESSION['login'] = true;			
			header('Location: main.php');
			exit();
		}elseif(isset($profil_widget_api[$_POST['usernametxt']]) && $profil_widget_api[$_POST['usernametxt']]==md5($_POST['passwordtxt'])){
			$_SESSION['login_widgetapi'] = true;
			header('Location: main_widgetapi.php');
			exit();
		}elseif(isset($profil_bokbasen_api[$_POST['usernametxt']]) && $profil_bokbasen_api[$_POST['usernametxt']]==md5($_POST['passwordtxt'])){
			$_SESSION['login_bokbasenapi'] = true;
			header('Location: main_bokbasen.php');
			exit();
		}
	}
}

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
	<div class="container">	
		<div class="divider x-large"></div>		
		<div class="row">
			<div class="tabbable custom-tabs">
				<div class="tab-content ">
					<div class="tab-pane active">
						<div class="row">
						<div class="col-md-4"></div>
						<div class="col-md-4">
							<h4>Please login:</h4>
						<form class="form-horizontal" role="form" id="loginFrm" action="index.php" method="post">
							<div class="form-group">
								<label for="usernametxt" class="col-md-1 control-label">Username:</label>
							</div>
							<div class="form-group">
								<div class="col-md-8">
								<input type="text" class="form-control" name="usernametxt" placeholder="Enter username">
								</div>
							</div>
							<div class="form-group">
								<label for="passwordtxt" class="col-md-1 control-label">Password:</label>
							</div>
							<div class="form-group">
								<div class="col-md-8">
								<input type="password" class="form-control" name="passwordtxt" placeholder="Password">
								</div>
							</div>
							<h4></h4>
							<?php
							if($_SERVER['REQUEST_METHOD']=='POST'){
							?>
							<div class="form-group">
								<div class="col-md-8">
									<div class="alert alert-danger ">
										Login incorrect!
									</div>
								</div>
							</div>
							<?php } ?>
							<div class="form-group">
								<div class="col-md-3">
									<button type="submit" class="btn btn-primary" >Login<span class="glyphicon icon-submit"></span></button>
								</div>
							</div>
						</form>
						</div>
						<div class="col-md-4"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>