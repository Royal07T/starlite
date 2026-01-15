<?php
// Script to download the CA certificate bundle
$url = 'https://curl.se/ca/cacert.pem';
$certPath = __DIR__ . '/certs/cacert.pem';

// Create directory if it doesn't exist
if (!file_exists(dirname($certPath))) {
    mkdir(dirname($certPath), 0755, true);
}

// Try to download with SSL verification disabled (just for this download)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification just for this download
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL verification just for this download
$certData = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error downloading certificate: ' . curl_error($ch) . PHP_EOL;
    exit(1);
}

curl_close($ch);

// Save the certificate bundle
if (file_put_contents($certPath, $certData)) {
    echo "Certificate bundle downloaded successfully to: $certPath" . PHP_EOL;
} else {
    echo "Failed to save certificate bundle to: $certPath" . PHP_EOL;
    exit(1);
}

// Create a PHP configuration file that sets the CA certificate path
$configPath = __DIR__ . '/certs/ssl-config.php';
$configContent = "<?php\n";
$configContent .= "// Set the CA certificate path for cURL and OpenSSL\n";
$configContent .= "putenv('CURL_CA_BUNDLE=' . __DIR__ . '/cacert.pem');\n";
$configContent .= "putenv('SSL_CERT_FILE=' . __DIR__ . '/cacert.pem');\n";

if (file_put_contents($configPath, $configContent)) {
    echo "SSL configuration file created successfully at: $configPath" . PHP_EOL;
    echo "Add 'require __DIR__ . \"/certs/ssl-config.php\";' to the top of your index.php file to use this configuration." . PHP_EOL;
} else {
    echo "Failed to create SSL configuration file at: $configPath" . PHP_EOL;
}
