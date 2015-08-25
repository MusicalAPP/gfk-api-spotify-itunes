<?php
if(!isset($_POST['modid']) || !isset($_POST['name']) || !isset($_POST['pk']) || !isset($_POST['tkey']) || !isset($_POST['tunit']) || !isset($_POST['value'])) {

}else{
require_once ('config/dbconfig.php');
require_once('config/function_widgetapi.php');
update_link($_POST['modid'],$_POST['tunit'],$_POST['tkey'],$_POST['pk'],$_POST['name'],$_POST['value']);
}
?>