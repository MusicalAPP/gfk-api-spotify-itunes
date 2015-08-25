<?php 

require_once('config/config.php');
$curr_dir = dirname(__FILE__);
$arr = scandir($curr_dir);
echo '<pre>';print_r($arr);

foreach ($arr as $value) {
   if(substr($value, 0, 3) == '141' || substr($value, 0, 10) == 'itunes_141'){
    rmdirr($value);
   }
}


?>