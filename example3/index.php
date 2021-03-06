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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Example 3</title>
	<style>
	</style>
</head>
<body>

<p>
	Loading the image up to the second DHT marker & loading the final image data.
</p>

<img data-src="progressive.jpg" data-bytes="<?= $positions[1] ?>" alt="">

<script>
var $img = document.querySelector("img[data-src]");
var URL = window.URL || window.webkitURL;

var xhr = new XMLHttpRequest();
xhr.onload = function(){
    if (this.status === 206){
		$img.src_part = this.response;
		$img.src = URL.createObjectURL(this.response);
	}
}

xhr.open('GET', $img.getAttribute('data-src'));
xhr.setRequestHeader("Range", "bytes=0-" + $img.getAttribute('data-bytes'));
xhr.responseType = 'blob';
xhr.send();

setTimeout(function(){
	var xhr = new XMLHttpRequest();
	xhr.onload = function(){
		if (this.status === 206){
			var blob = new Blob([$img.src_part, this.response], { type: 'image/jpeg'} );
			$img.src = URL.createObjectURL(blob);
		}
	}
	xhr.open('GET', $img.getAttribute('data-src'));
	xhr.setRequestHeader("Range", "bytes="+ (parseInt($img.getAttribute('data-bytes'), 10)+1) +'-');
	xhr.responseType = 'blob';
	xhr.send();
}, 2000);
</script>

</body>
</html>