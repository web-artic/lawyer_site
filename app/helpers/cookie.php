<?php
function encryptCookieValue($value, $key) {
    $iv = random_bytes(16); 
    $encrypted = openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decryptCookieValue($value, $key) {
    $data = base64_decode($value);
    $iv = substr($data, 0, 16); 
    $encrypted = substr($data, 16); 
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}
?>