<?php
/**
 * SSL Certificate Fix Script
 * 
 * This script will:
 * 1. Download a trusted CA certificate bundle
 * 2. Find the active php.ini file
 * 3. Update the curl.cainfo and openssl.cafile settings
 */

echo "Starting SSL certificate fix...\n";

// Step 1: Download the CA certificate bundle
$url = 'https://curl.se/ca/cacert.pem';
$certPath = __DIR__ . '/certs/cacert.pem';

// Create directory if it doesn't exist
if (!file_exists(dirname($certPath))) {
    mkdir(dirname($certPath), 0755, true);
    echo "Created certs directory\n";
}

// Try to download with SSL verification disabled (just for this download)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification just for this download
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL verification just for this download
$certData = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error downloading certificate: ' . curl_error($ch) . "\n";
    exit(1);
}

curl_close($ch);

// Save the certificate bundle
if (file_put_contents($certPath, $certData)) {
    echo "Certificate bundle downloaded successfully to: $certPath\n";
} else {
    echo "Failed to save certificate bundle to: $certPath\n";
    exit(1);
}

// Step 2: Find the active php.ini file
$phpIniPath = php_ini_loaded_file();
echo "Active php.ini file: $phpIniPath\n";

if (!$phpIniPath || !file_exists($phpIniPath)) {
    echo "Could not find the active php.ini file\n";
    exit(1);
}

// Step 3: Create a local php.ini file in the project directory
$localPhpIniPath = __DIR__ . '/php.ini';
$phpIniContent = "; Local PHP configuration for SSL certificate fix\n";
$phpIniContent .= "curl.cainfo = \"" . realpath($certPath) . "\"\n";
$phpIniContent .= "openssl.cafile = \"" . realpath($certPath) . "\"\n";

if (file_put_contents($localPhpIniPath, $phpIniContent)) {
    echo "Created local php.ini file at: $localPhpIniPath\n";
} else {
    echo "Failed to create local php.ini file\n";
    exit(1);
}

// Step 4: Create a bootstrap file to load before any requests
$bootstrapPath = __DIR__ . '/ssl-bootstrap.php';
$bootstrapContent = "<?php\n";
$bootstrapContent .= "// Set SSL certificate paths for cURL and OpenSSL\n";
$bootstrapContent .= "putenv('CURL_CA_BUNDLE=" . realpath($certPath) . "');\n";
$bootstrapContent .= "putenv('SSL_CERT_FILE=" . realpath($certPath) . "');\n";
$bootstrapContent .= "ini_set('curl.cainfo', '" . realpath($certPath) . "');\n";
$bootstrapContent .= "ini_set('openssl.cafile', '" . realpath($certPath) . "');\n";

if (file_put_contents($bootstrapPath, $bootstrapContent)) {
    echo "Created SSL bootstrap file at: $bootstrapPath\n";
} else {
    echo "Failed to create SSL bootstrap file\n";
    exit(1);
}

// Step 5: Update the public/index.php file to include the bootstrap file
$indexPath = __DIR__ . '/public/index.php';
$indexContent = file_get_contents($indexPath);

if ($indexContent === false) {
    echo "Failed to read index.php file\n";
    exit(1);
}

// Check if the bootstrap file is already included
if (strpos($indexContent, 'ssl-bootstrap.php') === false) {
    // Add the bootstrap file include at the top of the file
    $indexContent = preg_replace(
        '/^<\?php/',
        "<?php\n\n// Load SSL certificate configuration\nrequire __DIR__.'/../ssl-bootstrap.php';",
        $indexContent
    );

    if (file_put_contents($indexPath, $indexContent)) {
        echo "Updated index.php to include SSL bootstrap file\n";
    } else {
        echo "Failed to update index.php\n";
        exit(1);
    }
} else {
    echo "SSL bootstrap file is already included in index.php\n";
}

echo "\nSSL certificate fix completed successfully!\n";
echo "Please restart your web server for the changes to take effect.\n";
