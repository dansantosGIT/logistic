<?php
$url = 'http://127.0.0.1:8001/vehicle/maintenance';
$content = @file_get_contents($url);
$status = 0;
if (isset($http_response_header[0])) {
    if (preg_match('#HTTP/\\d\\.\\d\\s+(\\d{3})#', $http_response_header[0], $m)) $status = $m[1];
}
echo "STATUS:$status\n";
if ($content) {
    if (strpos($content, 'function openUploadedPhoto') !== false) echo "MATCH:function openUploadedPhoto\n";
    if (strpos($content, 'const toast =') !== false) echo "MATCH:const toast =\n";
} else {
    echo "NO_BODY\n";
}
