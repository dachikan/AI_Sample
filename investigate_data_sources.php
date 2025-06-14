<?php
/**
 * 各ページのデータソースを調査するスクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>データソース調査</title></head>";
echo "<body>";
echo "<h1>🔍 データソース調査</h1>";

// データベース接続
include "db_connect.php";

echo "<h2>📊 データベーステーブル確認</h2>";

if (isset($conn) && $conn) {
    // 全テーブルを表示
    $tables_sql = "SHOW TABLES";
    $tables_result = $conn->query($tables_sql);
    
    echo "<h3>存在するテーブル:</h3>";
    echo "<ul>";
    while ($table = $tables_result->fetch_array()) {
        $table_name = $table[0];
        echo "<li><strong>$table_name</strong>";
        
        // 各テーブルのレコード数を確認
        $count_sql = "SELECT COUNT(*) as count FROM `$table_name`";
        $count_result = $conn->query($count_sql);
        if ($count_result) {
            $count = $count_result->fetch_assoc()['count'];
            echo " - <span style='color:blue'>$count 件</span>";
        }
        echo "</li>";
    }
    echo "</ul>";
}

echo "<h2>📄 ファイル別データソース確認</h2>";

// AI_list.phpの確認
echo "<h3>AI_list.php:</h3>";
if (file_exists('AI_list.php')) {
    $ai_list_content = file_get_contents('AI_list.php');
    
    // SQLクエリを抽出
    if (preg_match('/FROM\s+([a-zA-Z_][a-zA-Z0-9_]*)/i', $ai_list_content, $matches)) {
        echo "<p>使用テーブル: <strong style='color:red'>{$matches[1]}</strong></p>";
    }
    
    // SQLクエリ全体を表示
    if (preg_match('/\$sql\s*=\s*["\']([^"\']+)["\']/', $ai_list_content, $matches)) {
        echo "<pre style='background:#f8f9fa;padding:10px;border-radius:5px;'>" . htmlspecialchars($matches[1]) . "</pre>";
    }
} else {
    echo "<p style='color:red'>AI_list.phpが見つかりません</p>";
}

// ranking.phpの確認
echo "<h3>ranking.php:</h3>";
if (file_exists('ranking.php')) {
    $ranking_content = file_get_contents('ranking.php');
    
    // SQLクエリを抽出
    if (preg_match('/FROM\s+([a-zA-Z_][a-zA-Z0-9_]*)/i', $ranking_content, $matches)) {
        echo "<p>使用テーブル: <strong style='color:green'>{$matches[1]}</strong></p>";
    }
    
    // SQLクエリ全体を表示
    if (preg_match('/\$sql\s*=\s*["\']([^"\']+)["\']/', $ranking_content, $matches)) {
        echo "<pre style='background:#f8f9fa;padding:10px;border-radius:5px;'>" . htmlspecialchars($matches[1]) . "</pre>";
    }
} else {
    echo "<p style='color:red'>ranking.phpが見つかりません</p>";
}

// list.phpの確認
echo "<h3>list.php:</h3>";
if (file_exists('list.php')) {
    $list_content = file_get_contents('list.php');
    
    // SQLクエリを抽出
    if (preg_match('/FROM\s+([a-zA-Z_][a-zA-Z0-9_]*)/i', $list_content, $matches)) {
        echo "<p>使用テーブル: <strong style='color:blue'>{$matches[1]}</strong></p>";
    }
} else {
    echo "<p style='color:orange'>list.phpが見つかりません</p>";
}

echo "<h2>🔧 推奨される修正方法</h2>";
echo "<div style='background:#e7f3ff;padding:15px;border-radius:5px;margin:10px 0;'>";
echo "<h4>方法1: AI_list.phpを ranking.php と同じテーブルを使用するように変更</h4>";
echo "<p>ranking.phpが使用しているテーブルをAI_list.phpでも使用する</p>";
echo "</div>";

echo "<div style='background:#f0f8e7;padding:15px;border-radius:5px;margin:10px 0;'>";
echo "<h4>方法2: データを統合</h4>";
echo "<p>ranking.phpのデータをAI_list.phpが使用しているテーブルにコピーする</p>";
echo "</div>";

echo "</body></html>";
?>
