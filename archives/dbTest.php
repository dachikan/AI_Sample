<?php
// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース接続情報
$servername = "mysql213.phy.lolipop.lan";
$username = "LAA1337491";
$password = "kami2004"; // パスワードは適切なものに変更してください
$dbname = "LAA1337491-nsk";

try {
    // データベース接続
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "データベース接続成功！";
    
    // テーブル一覧を取得
    $stmt = $conn->query("SHOW TABLES");
    echo "<h3>テーブル一覧:</h3>";
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "データベース接続エラー: " . $e->getMessage();
}
?>