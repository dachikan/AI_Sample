<?php
// 基本的なPHPテスト
echo "PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Directory: " . getcwd() . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// ファイルの存在確認
$required_files = [
    'db_connect_extended.php',
    'config/db_connect.php',
    'includes/header.php',
    'includes/footer.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file NOT FOUND<br>";
    }
}
?>