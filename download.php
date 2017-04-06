<?

$title = $_POST["track"][0]["title"];
$src = substr($_POST["track"][0]["src"], 2);

set_time_limit(0); // unlimited max execution time

$fp = fopen('downloads/' . $title . '.mp3', "w");

$options = array(
  CURLOPT_FILE    => $fp,
  CURLOPT_TIMEOUT =>  28800, // set this to 8 hours so we dont timeout on big files
  CURLOPT_URL     => "http://" . $src
);

$ch = curl_init();
curl_setopt_array($ch, $options);
curl_exec($ch);
curl_close($ch);

?>