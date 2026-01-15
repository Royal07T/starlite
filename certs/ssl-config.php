<?php
// Set the CA certificate path for cURL and OpenSSL
putenv('CURL_CA_BUNDLE=' . __DIR__ . '/cacert.pem');
putenv('SSL_CERT_FILE=' . __DIR__ . '/cacert.pem');
