<?php
function rmdirr($dirname) {
    // Sanity check
    if (!file_exists($dirname)) {
        return false;
    }

    // Simple delete for a file
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }

    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Recurse
        rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
    }

    // Clean up
    $dir->close();
    return rmdir($dirname);
}
function send_file($name) {
  $path = $name;
  $path_info=pathinfo($path);
  $basename = $path_info['basename'];  
  if (!is_file($path) or connection_status()!=0) return(false);
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");
  header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
  header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
  header("Content-Type: application/octet-stream");
  header("Content-Length: ".(string)(filesize($path)));
  header("Content-Disposition: inline; filename=$basename");
  header("Content-Transfer-Encoding: binary\n");
  if ($file = fopen($path, 'rb')) {
   while(!feof($file) and (connection_status()==0)) {
     print(fread($file, 1024*8));
     flush();
   }
   fclose($file);
  } 
  return((connection_status()==0) and !connection_aborted());
}
$tmp='';
if(isset($_GET['file'])){
	$tmp = urldecode($_GET['file']);
}
if (!send_file($tmp)) {
rmdirr(urldecode($_GET['tmp_dir']));
die ("file transfer failed");
} else {
rmdirr(urldecode($_GET['tmp_dir']));
}
?>