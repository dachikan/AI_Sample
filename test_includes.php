<?php
// エラー表示を有効にする
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing file includes...<br>";

// 1. db_connect.phpのテスト
echo "1. Testing db_connect.php...<br>";
try {
    if (file_exists('config/db_connect.php')) {
        include_once 'config/db_connect.php';
        echo "✅ db_connect.php loaded successfully<br>";
    } else {
        echo "❌ db_connect.php not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading db_connect.php: " . $e->getMessage() . "<br>";
}

// 2. db_connect_extended.phpのテスト
echo "2. Testing db_connect_extended.php...<br>";
try {
    if (file_exists('db_connect_extended.php')) {
        include_once 'db_connect_extended.php';
        echo "✅ db_connect_extended.php loaded successfully<br>";
    } else {
        echo "❌ db_connect_extended.php not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading db_connect_extended.php: " . $e->getMessage() . "<br>";
}

// 3. 関数の存在確認
$functions_to_check = [
    'getAITypesFromInfo',
    'getTopAIServices',
    'getAIServiceCount'
];

foreach ($functions_to_check as $func) {
    if (function_exists($func)) {
        echo "✅ Function $func exists<br>";
    } else {
        echo "❌ Function $func NOT FOUND<br>";
    }
}
?>