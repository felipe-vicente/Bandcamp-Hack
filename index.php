<?
function toAscii($str, $replace=array(), $delimiter='-') {
	if( !empty($replace) ) $str = str_replace((array)$replace, ' ', $str);
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
	return $clean;
}

function urlTest(){
	preg_match( "/https:\/\/(?:[^\.]*)\.bandcamp\.com(.?)([^\/]*)\/([^\/]+)/", $_POST["url"], $urlMatches);
	if( $urlMatches[0] ){
		if( $urlMatches[2] != "album" ) return array("ok" => false, "msg" => "The Url sent is not an album in bandcamp.");
		if( $urlMatches[2] == "album" ) return array("ok" => true);
		return array("ok" => true);
	}else{
		return array("ok" => false, "msg" => "The Url sent is not a Bandcamp Url.");
	}
}

if( $_POST["url"]){
	$testedUrl = urlTest($_POST["url"]);
	include( "logs.php" );
}

if( $testedUrl["ok"] ){

exec("curl " . $_POST["url"], $o, $r);

for( $l = 0 ; $l <= count( $o ) ; $l++ ){
	if( strpos( $o[$l] , "trackinfo:" ) ) $trackInfo = $o[$l];
	if( strpos( $o[$l] , "<title>" ) ) $pageTitle = $o[$l];
	if( strpos( $o[$l] , "shortcut icon" ) ) $shortcutIcon = $o[$l];
	if( strpos( $o[$l] , "image_src" ) ) $imageSrc = $o[$l];
	if( strpos( $o[$l] , "album_title:" ) ) $albumName = $o[$l];
	if( strpos( $o[$l] , "artist:" ) ) $artistName = $o[$l];
}
$albumName = str_replace( "\",", "", substr( $albumName, strpos( $albumName, "album_title: \"") + 14 ) );
$artistName = str_replace( "\",", "", substr( $artistName, strpos( $artistName, "artist: \"") + 9 ) );
$imageSrc = str_replace( "\">", "", substr( $imageSrc, strpos( $imageSrc, "href=\"" ) + 6) );

preg_match("/(?:[^<]*)<title>([^|]*)(?: | )([^<]*)<\/title>/", $pageTitle, $titleArr);
$pageTitle = "Getting Album " . $titleArr[1] . " by " . substr($titleArr[2], 2) . " from Bandcamp";

exec("mkdir \"downloads/" . toAscii( $albumName ) . "\""); //Create Dir
exec("curl -o \"downloads/" . toAscii( $albumName ) . "/cover.jpg\" " . $imageSrc); //Download Cover

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="https://fonts.googleapis.com/css?family=Dosis" rel="stylesheet">  
<style type="text/css">
body{
	font-family: 'Dosis', sans-serif;
	margin: 0;
	padding: 0;
	color: #000;
	background: #000;
}
.bgCover{
	display: block;
	position: fixed;
	width: 100%;
	height: 100%;
	z-index:-9999;
	background: url("<?=$imageSrc?>") no-repeat center center / cover;
	-webkit-filter: blur(10px);
	-moz-filter: blur(10px);
	-o-filter: blur(10px);
	-ms-filter: blur(10px);
	filter: blur(10px);
	opacity: 0.6;
	top: 0;
	left: 0;
}

.playerContent{
	background: #fff none repeat scroll 0 0;
    border-radius: 4px;
    box-shadow: 2px 2px 5px -2px #000;
    margin: 20px auto;
    max-width: 400px;
    padding: 20px;
    text-align: center;
    transition: all 0.4s linear 0s;
	position: relative;
}

.playerContent .albumCover{
	display: block;
    margin: 0 auto;
    overflow: hidden;
    position: relative;
    width: 400px;
}

.playerContent .albumCover img{
	width: 100%;
}

.playerContent .tracks{
	margin-top: 0;
}

.downB{
	background: #cb2d3e none repeat scroll 0 0;
    border-bottom: 8px solid #ab0d1e;
    border-radius: 4px;
    display: block;
    height: 60px;
    left: -180px;
    margin: 0 auto 0 50%;
    opacity: 0;
    padding: 10px;
    position: absolute;
    text-align: center;
    text-decoration: none;
    top: 310px;
    transition: all 0.2s linear 0s;
    width: 340px;
}
.downB.show{
	opacity: 1;
}

.downB:active{
	top: 322px;
	border-bottom-width: 0;
}

.downB img{
	display: inline-block;
    margin-top: 5px;
    vertical-align: top;
    width: 50px;
}

.downB .album{
	color: #fff;
    display: inline-block;
    font-family: 'Dosis', sans-serif;
    font-size: 18px;
    margin: 10px 10px;
    text-align: right;
    vertical-align: top;
}

.downB .album span{
	font-size: 14px;
}

.albumCover{
	opacity: 0.3;
	transition: opacity 0.2s linear;
}

.albumCover.show{ opacity: 1; }

#loading{
	background: #fff none repeat scroll 0 0;
    border-radius: 50%;
    display: block;
    height: 40px;
    left: -30px;
    margin-left: 50%;
    margin-top: 50%;
    padding: 10px;
    position: absolute;
    top: -30px;
    width: 40px;
}

</style>
<title><?=$pageTitle?></title>
<? echo $shortcutIcon . "\n";?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>
<script src="color-thief.js" type="text/javascript"></script>
<script type="text/javascript">

var infos = {<?=$trackInfo?>};
var tracks = [];

$(function(){

$.each(infos.trackinfo, function(index, value){
	tracks[index] = {
		title	: infos.trackinfo[index]["title"],
		src	: infos.trackinfo[index]["file"]["mp3-128"]
	};
});

$.ajax({
	method	: "POST",
	url			: "ajax.php",
	data		: {
		track		: tracks,
		artist		: "<?=$artistName?>",
		album	: "<?=$albumName?>",
		cover		: "<?=$imageSrc?>"
	},
}).done(function(response, status, xhr){
	console.log( status );
	console.log( xhr.getResponseHeader('Location') );
	$(".tracks").html(response);
});

});

</script>
</head>

<body>

<div class="playerContent">
<div class="albumCover">
	<img id="albumCover" src="<? echo "downloads/" . toAscii( $albumName ) . "/cover.jpg"; ?>" />
</div>
<div class="tracks">
	<img id="loading" src="assets/images/loading.gif" />
</div>
</div>

<div class="bgCover"></div>
</body>
</html>
<? }else{ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="https://fonts.googleapis.com/css?family=Dosis" rel="stylesheet"> 
<title>Download Album from Bandcamp</title>
<style type="text/css">
body{
	font-family: 'Dosis', sans-serif;
	padding: 0;
	color: #333;
	background: #cb2d3e; /* fallback for old browsers */
	background: -webkit-linear-gradient(to down, #cb2d3e , #ef473a); /* Chrome 10-25, Safari 5.1-6 */
	background: linear-gradient(to down, #cb2d3e , #ef473a); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
}
form{
	opacity: 0;
	display: block;
    font-size: 0;
    margin: 155px auto 0;
    max-width: 780px;
    position: relative;
    width: 80%;
	text-align: center;
	transition: all 0.5s ease-in-out 0s;
}
form.show{
	opacity: 1;
	margin: 140px auto 0;
}
form .urlSpan{
	background: rgba(255, 255, 255, 0.7) none repeat scroll 0 0;
    border-radius: 6px 0 0 6px;
    display: inline-block;
    padding: 10px;
    position: relative;
    vertical-align: top;
    width: 80%;
}
form .urlSpan input{
	background: transparent none repeat scroll 0 0;
    border: medium none;
    color: #000;
    display: block;
    font-family: "Dosis",sans-serif;
    font-size: 17px;
    line-height: 2;
    padding: 3px 0;
    width: 100%;
}
form button{
	background: rgba(0, 0, 0, 0.2) none repeat scroll 0 0;
    border: medium none;
    border-radius: 0 6px 6px 0;
    cursor: pointer;
    display: inline-block;
    height: 60px;
    vertical-align: top;
    width: 60px;
}
form button img{
	display: block;
    margin: 0 auto;
    width: 60%;
}
.siteInfo{
	color: #fff;
    display: block;
    font-size: 20px;
    margin-top: 35px;
    text-align: center;
    transition: all 0.5s ease 0.5s;
	opacity: 0;
}
.siteInfo.show{ opacity: 1; margin-top: 20px; }
.urlError{
	display: block;
    font-size: 28px;
    margin-bottom: 10px;
    text-shadow: 2px 2px #000;
}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function(){
	setTimeout(function(){
		$("form, .siteInfo").addClass("show");
	}, 500);
});
</script>
</head>
<body>
<form method="post">
	<span class="urlSpan">
    	<input type="text" name="url" placeholder="Album URL on Bandcamp. Ex: https://birthmark.bandcamp.com/album/shaking-hands" />
    </span>
    <button type="submit">
    	<img src="assets/images/search.svg" />
    </button>
</form>
<div class="siteInfo">
	<? if( isset( $testedUrl["msg"] ) ) echo "<strong class='urlError'>" . $testedUrl["msg"] . "</strong>"; ?>
	Download <b>Full Albums</b> from Bandcamp<br /><b>For Free...</b>
</div>
</body>
</html>
<? } ?>