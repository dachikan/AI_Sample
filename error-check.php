<?php
// エラーを表示するための設定
ini_set('display_errors', 1);
error_reporting(E_ALL);

// データベース接続設定
require_once 'config/db_connect.php';

// 接続
$conn = new mysqli($host, $user, $password, $database);

// 接続エラーチェック
if ($conn->connect_error) {
    die("接続エラー: " . $conn->connect_error);
}

echo "<h2>接続成功</h2>";

// AIPromptTemplatesテーブルの内容を表示
$sql = "SELECT * FROM AIPromptTemplates";
$result = $conn->query($sql);

echo "<h3>AIPromptTemplates</h3>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>カテゴリID</th><th>AIタイプID</th><th>名前</th><th>説明</th></tr>";

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["category_id"] . "</td>";
        echo "<td>" . $row["ai_type_id"] . "</td>";
        echo "<td>" . $row["name"] . "</td>";
        echo "<td>" . $row["description"] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>結果がありません</td></tr>";
}
echo "</table>";

$conn->close();
?>