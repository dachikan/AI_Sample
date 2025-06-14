<?php
// エラー表示を有効にする
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...<br>";

try {
    // データベース接続情報（db_connect.phpから）
    $host = "mysql213.phy.lolipop.lan";
    $user = "LAA1337491";
    $password = "kami2004";
    $database = "LAA1337491-nsk";

    echo "Attempting to connect to: $host<br>";
    
    $conn = new mysqli($host, $user, $password, $database);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "✅ Database connection successful!<br>";
    
    // テーブルの存在確認
    $result = $conn->query("SHOW TABLES LIKE 'AIInfo'");
    if ($result && $result->num_rows > 0) {
        echo "✅ AIInfo table exists<br>";
        
        // レコード数確認
        $count_result = $conn->query("SELECT COUNT(*) as count FROM AIInfo");
        if ($count_result) {
            $row = $count_result->fetch_assoc();
            echo "✅ AIInfo table has " . $row['count'] . " records<br>";
        }
    } else {
        echo "❌ AIInfo table NOT FOUND<br>";
    }

    $conn->close();

} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}
?>