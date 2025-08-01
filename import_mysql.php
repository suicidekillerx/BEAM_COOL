<?php
// Import database using MySQL command line
require_once 'config/database.php';

echo "Importing database using MySQL command line...\n";

$sqlFile = 'beam_ecommerce (2).sql';

// Try different MySQL paths for XAMPP
$mysqlPaths = [
    'C:\\xampp2\\mysql\\bin\\mysql.exe',
    'C:\\xampp\\mysql\\bin\\mysql.exe',
    'mysql'
];

$mysqlPath = null;
foreach ($mysqlPaths as $path) {
    if (file_exists($path) || $path === 'mysql') {
        $mysqlPath = $path;
        break;
    }
}

if (!$mysqlPath) {
    die("MySQL executable not found. Please check your XAMPP installation.\n");
}

$mysqlCommand = "\"$mysqlPath\" -u " . DB_USER . " -p" . DB_PASS . " " . DB_NAME . " < " . $sqlFile;

echo "Running: $mysqlCommand\n";

// Execute MySQL command
$output = [];
$returnCode = 0;

exec($mysqlCommand, $output, $returnCode);

if ($returnCode === 0) {
    echo "Database imported successfully!\n";
} else {
    echo "Error importing database. Return code: $returnCode\n";
    echo "Output:\n";
    foreach ($output as $line) {
        echo $line . "\n";
    }
}

// Alternative method using shell_exec
echo "\nTrying alternative method...\n";
$result = shell_exec($mysqlCommand . " 2>&1");
echo "Result: $result\n";
?> 