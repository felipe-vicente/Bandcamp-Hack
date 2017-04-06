<?
$txt = date("Y-m-d H:i:s")  . "\t\t" . $_POST["url"] . "\t\t";
$myfile = file_put_contents( $_SERVER['DOCUMENT_ROOT'] . "bandcamp/logs/logs.txt" , $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
?>