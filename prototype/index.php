<?php

ini_set('display_errors', 1);

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
$requests = ['1' => 'HTTP request', '2' => 'data URI'];
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
	$_GET[$name] = (int)$_GET[$name];
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
	<link href="styles.css" rel="stylesheet">
</head>
<body>

<div id="canvas">
	<h1>Embedded Image Preview (EIP)</h1>
	
	<p>
		This page is a prototype for the image preview technique described at<br />
		<a href="https://smashingmagazine.com/..." target="_blank">https://smashingmagazine.com/...</a>
	</p>
	
	<p>
		Find the source code of this example at<br />
		<a href="https://github.com/McSodbrenner/embedded-image-preview" target="_blank">https://github.com/McSodbrenner/embedded-image-preview</a>
	</p>

	<div class="settings">

		<form action="" method="get">
			<p>
				File size of full image: <?= filesize($file) ?> bytes
			</p>

			<p>
				<label>
					Load preview as<br />
					<select name="request">
						<?php foreach ($requests as $key => $value) { ?>
							<option value="<?= $key ?>" <?= $input['request'] === $key ? 'selected' : '' ?>><?= $value ?></option>
						<?php } ?>
					</select>
				</label>
			</p>

			<div class="flex">
				<label>
					Use Scan # for preview<br />
					<select name="scan">
						<?php foreach ($positions as $scan => $bytes) { ?>
							<?php if ($scan === 0) continue; ?>
							<option value="<?= $scan ?>" <?= $input['scan'] === $scan ? 'selected' : '' ?>><?= $scan .' ('. $bytes .' bytes)' ?></option>
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
					Show full image after<br />
					<select name="timeout">
						<?php foreach ($input_config['timeout']['options'] as $second) { ?>
							<option value="<?= $second ?>" <?= $input['timeout'] === $second ? 'selected' : '' ?>><?= $second .' second(s)' ?></option>
						<?php } ?>
					</select>
				</label>

				<div>
					<input type="submit" value="Submit">
				</div>
			</div>

		</form>
	</div>

	<div class="preview">
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
	</div>
</div>

<script>
var request = "<?= $input['request'] == '1' ? 'request' : 'data-uri' ?>";

var $img = document.querySelector("img[data-src]");
var blur_strength = <?= $input['blur'] ?>;

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
	$img.setAttribute('data-bytes', $img.src_part.size);
}


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
	xhr.setRequestHeader("Range", "bytes="+ $img.getAttribute('data-bytes') +'-');
	xhr.responseType = 'blob';
	xhr.send();
}, <?= $input['timeout'] * 1000 ?>);
</script>

</body>
</html>