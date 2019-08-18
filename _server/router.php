<?php

ini_set('display_errors', 1);

// this router adds support for HTTP Range headers to PHPs builtin server
header('Accept-Ranges: bytes');

if (isset($_SERVER['HTTP_RANGE'])) {
    if (!preg_match("~^bytes=(\d*)-(\d*)$~", $_SERVER['HTTP_RANGE'], $matches)) {
        die('Request not valid');
    }
 
    list($full, $offset_start, $offset_end) = $matches;
    $request_uri = str_replace('..', '', $_SERVER['REQUEST_URI']);
    $file = '.'. $request_uri;
    $filesize = filesize($file);
    
    $offset_start = max(0, $offset_start);
    if ($offset_end === '') {
        $offset_end = $filesize;
    }
    $length = $offset_end - $offset_start;
    if ($offset_start === 0) $length++;
    
    $handle = fopen($file, 'r');
    fseek($handle, $offset_start);
    $data = fread($handle, $length);
    fclose($handle);
    
    http_response_code(206);
    header('Content-Type: image/jpeg');
    header("Content-Length: {$length}");
    header("Content-Range: bytes {$offset_start}-{$offset_end}/{$filesize}");
    echo $data;
    return true;
}

return false;


