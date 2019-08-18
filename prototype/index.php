<?php

ini_set('display_errors', 1);

// sanitize input
$_GET = array_map('intval', $_GET);

// read positions of DHT markers in JFIF file
$file = "progressive.jpg";
$jpgdata = file_get_contents($file);
$positions = [];
$offset = 0;
while ($pos = strpos($jpgdata, "\xFF\xC4", $offset)) {
	$positions[] = $pos+2;
	$offset = $pos+2;
}

// validate form data
$requests = ['1' => 'HTTP request', '2' => 'Data URI'];
$input_config = [
	'request' => ['options' => array_keys($requests), 'default' => 1],
	'scan' => ['options' => array_keys(array_slice($positions, 1)), 'default' => 2],
	'blur' => ['options' => range(0,4), 'default' => 4],
	'timeout' => ['options' => range(0,10), 'default' => 2],
];
$input = [];
foreach ($input_config as $name => $field_config) {
	if (!isset($_GET[$name])) {
		$input[$name] = $field_config['default'];
		continue;
	}
	if (!in_array($_GET[$name], $field_config['options'])) {
		$input[$name] = $field_config['default'];
		continue;
	}
	$input[$name] = $_GET[$name];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Embedded Image Preview (EIP)</title>

	<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
	<link href="bare.min.css" rel="stylesheet">
	<link href="styles.css" rel="stylesheet">
</head>
<body>

<section>
	<h1>Embedded Image Preview (EIP)</h1>

	<grid>
		<div col="1/2">
			<p>
				Using the Embedded Image Preview (EIP) technology presented here, we can load qualitatively different preview images from progressive JPEGs, depending on the application purpose, with the help of Ajax and HTTP Range Requests. The data from these preview images is not discarded but reused to display the entire image.
			</p>
		</div>

		<div col="1/2">
			<p>
				For a complete description of how it works, please look here:
				<a href="https://smashingmagazine.com/..." target="_blank">https://smashingmagazine.com/...</a>
			</p>
			
			<p>
				Find the source code of this example in <a href="https://github.com/McSodbrenner/embedded-image-preview" att target="_blank">this GitHub repository</a>.
			</p>
		</div>
	</grid>

	<grid>
		<div col="1/2">
			<form action="" method="get">
				<card>
					<label>
						Load preview image as<br />
						<select name="request">
							<?php foreach ($requests as $key => $value) { ?>
								<option value="<?= $key ?>" <?= $input['request'] === $key ? 'selected' : '' ?>><?= $value ?></option>
							<?php } ?>
						</select>
					</label>

					<label>
						Use Scan # for preview image<br />
						<select name="scan">
							<?php foreach ($positions as $scan => $bytes) { ?>
								<?php if ($scan === 0) continue; ?>
								<option value="<?= $scan ?>" <?= $input['scan'] === $scan ? 'selected' : '' ?>><?= $scan .' ('. number_format($bytes) .' bytes)' ?></option>
							<?php } ?>
						</select>
					</label>

					<label>
						Blur radius of preview image<br />
						<select name="blur">
							<?php foreach ($input_config['blur']['options'] as $radius) { ?>
								<option value="<?= $radius ?>" <?= $input['blur'] === $radius ? 'selected' : '' ?>><?= $radius .' pixel(s)' ?></option>
							<?php } ?>
						</select>
					</label>

					<label>
						Load final image data after<br />
						<select name="timeout">
							<?php foreach ($input_config['timeout']['options'] as $second) { ?>
								<option value="<?= $second ?>" <?= $input['timeout'] === $second ? 'selected' : '' ?>><?= $second .' second(s)' ?></option>
							<?php } ?>
						</select>
					</label>
					
					<input primary type="submit" value="Submit">
					
				</card>

			</form>
		</div>

		<div col="1/2">
			<card>
				<?php if ($input['request'] == '1') { ?>
					<img data-src="progressive.jpg" data-bytes="<?= $positions[$input['scan']] ?>" alt="">
				<?php } else { ?>
					<?php
					$fp = fopen($file, 'r');
					$data_uri = 'data:image/jpeg;base64,'. base64_encode(fread($fp, $positions[$input['scan']]));
					fclose($fp);
					?>
					<img src="<?= $data_uri ?>" data-src="progressive.jpg" alt="">
				<?php } ?>

				<p>File size of full image: <?= number_format(filesize($file)) ?> bytes</p>
			</card>
		</div>
	</grid>
</section>

<footer>
	<p>
		Created by <a href="https://twitter.com/McSodbrenner" att target="_blank">McSodbrenner</a>
	</p>
</footer>


<script>
var request = "<?= $input['request'] === 1 ? 'request' : 'data-uri' ?>";

var $img = document.querySelector("img[data-src]");
var blur_strength = <?= $input['blur'] ?>;

// load the HTTP request preview image
if (request === 'request') {
	var URL = window.URL || window.webkitURL;
	var xhr = new XMLHttpRequest();

	$img.style.opacity = 0;
	setTimeout(function(){
		$img.style.transition = 'opacity .3s, filter .3s';
	});

	xhr.onload = function(){
		if (this.status === 206){
			$img.style.opacity = 1;
			$img.style.filter = 'blur('+blur_strength+'px)';
			$img.src_part = this.response;
			$img.src = URL.createObjectURL(this.response);
		}
	}

	xhr.open('GET', $img.getAttribute('data-src'));
	xhr.setRequestHeader("Range", "bytes=0-" + $img.getAttribute('data-bytes'));
	xhr.responseType = 'blob';
	xhr.send();

// load the data URI preview image
} else if (request === 'data-uri') {
	$img.style.filter = 'blur('+blur_strength+'px)';
	setTimeout(function(){
		$img.style.transition = 'opacity .3s, filter .3s';
	});

	var binary = atob($img.src.slice(23));
	var n = binary.length;
	var view = new Uint8Array(n);
	while(n--) { view[n] = binary.charCodeAt(n); }

	$img.src_part = new Blob([view], { type: 'image/jpeg' });
	$img.setAttribute('data-bytes', $img.src_part.size - 1);
}

// load the final image data
setTimeout(function(){
	var xhr = new XMLHttpRequest();
	xhr.onload = function(){
		if (this.status === 206){
			var blob = new Blob([$img.src_part, this.response], { type: 'image/jpeg'} );
			$img.src = URL.createObjectURL(blob);
			$img.style.filter = 'blur(0px)';
		}
	}
	xhr.open('GET', $img.getAttribute('data-src'));
	xhr.setRequestHeader("Range", "bytes="+ (parseInt($img.getAttribute('data-bytes'), 10)+1) +'-');
	xhr.responseType = 'blob';
	xhr.send();
}, <?= $input['timeout'] * 1000 ?>);
</script>

</body>
</html>