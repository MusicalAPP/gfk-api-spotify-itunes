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
session_unset();
session_destroy();
header('Location: index.php');
exit();
?>
