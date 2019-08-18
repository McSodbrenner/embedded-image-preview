<?php

// ini_set('display_errors', 1);

$img = "progressive.jpg";
$jpgdata = file_get_contents($img);
$positions = [];
$offset = 0;
while ($pos = strpos($jpgdata, "\xFF\xC4", $offset)) {
	$positions[] = $pos+2;
	$offset = $pos+2;
}

$fp = fopen($img, 'r');
$data_uri = 'data:image/jpeg;base64,'. base64_encode(fread($fp, $positions[1]));
fclose($fp);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Example 2</title>
	<style>
	</style>
</head>
<body>

<p>
    Loading the image up to the second DHT marker as data URI.
</p>

<img src="<?= $data_uri ?>" data-src="progressive.jpg" alt="">

<script>
var $img = document.querySelector("img[data-src]");

var binary = atob($img.src.slice(23));
var n = binary.length;
var view = new Uint8Array(n);
while(n--) { view[n] = binary.charCodeAt(n); }

$img.src_part = new Blob([view], { type: 'image/jpeg' });
$img.setAttribute('data-bytes', $img.src_part.size);
</script>

</body>
</html>