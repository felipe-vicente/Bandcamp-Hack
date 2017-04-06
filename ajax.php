<?

function toAscii($str, $replace=array(), $delimiter='-') {
	if( !empty($replace) ) $str = str_replace((array)$replace, ' ', $str);
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
	return $clean;
}


if( $_POST["track"] ){

set_time_limit(0);

foreach( $_POST["track"] as $key => $one ){
	exec("curl -L -o \"downloads/" . toAscii( $_POST["album"] ) . "/" . $one["title"] . ".mp3\" \"https:" . $one["src"] . "\"");
}

exec("zip -r \"downloads/" . toAscii( $_POST["artist"] ) . "_" . toAscii( $_POST["album"] ) . "\" \"downloads/" . toAscii( $_POST["album"] ) . "\"", $arr);
	
}
?>
<a href="<? echo "downloads/" . toAscii( $_POST["artist"] ) . "_" . toAscii( $_POST["album"] ) . ".zip"; ?>" class="downB">
    <span class="down">
    	<img src="assets/images/download.svg" />
    </span>
    <span class="album"><? if( strlen($_POST["album"]) > 30 ) echo substr($_POST["album"], 0, 30) . "..."; else echo $_POST["album"]; ?><br><span>by <? if( strlen($_POST["artist"]) > 30 ) echo substr($_POST["artist"], 0, 30) . "..."; else echo $_POST["artist"]; ?></span></span>
</a>
<script>$(function(){ setTimeout(function(){ $(".downB, .albumCover").addClass("show"); }, 400); });</script>