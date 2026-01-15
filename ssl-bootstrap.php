<?php
// Set SSL certificate paths for cURL and OpenSSL
putenv('CURL_CA_BUNDLE=C:\wamp64\www\starlite\certs\cacert.pem');
putenv('SSL_CERT_FILE=C:\wamp64\www\starlite\certs\cacert.pem');
ini_set('curl.cainfo', 'C:\wamp64\www\starlite\certs\cacert.pem');
ini_set('openssl.cafile', 'C:\wamp64\www\starlite\certs\cacert.pem');
