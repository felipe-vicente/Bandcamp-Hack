<?
exec("stat downloads/*", $arr, $err);

$fileCount = 0;

foreach( $arr as $key => $one ){
	
	preg_match( "/(File: )(.*)/", trim($one), $filePreg); //Testo se é um arquivo novo
	if( $filePreg[1] ){
		$fileCount++;
		$files[$fileCount]["src"] = ltrim( rtrim($filePreg[2], "'"), "`");
	}
	
	preg_match( "/(Modify: )(.*)/", trim($one), $modPreg);
	if( $modPreg[1] ){
		preg_match( "/([^\s]*)\s([^\.]*)/", $modPreg[2], $modsPreg);
		$timestamp = strtotime($modsPreg[1]);
		$files[$fileCount]["modified"] = $timestamp;
		$datediff = $files[$fileCount]["modified"] - strtotime( date("Y-m-d") );
		$difference = floor($datediff/(60*60*24));
		if( $difference <= -1 ) $files[$fileCount]["delete"] = true;
		else $files[$fileCount]["delete"] = false;
	}
	
}

foreach( $files as $file ){
	if( $file["delete"] ) exec("rm -rf \"" . $file["src"] . "\"", $arrExec);
}

print_r( $arrExec );
?>