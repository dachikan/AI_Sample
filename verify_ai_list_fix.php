<?php
/**
 * AI_list.php修正後の確認スクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>AI_list.php修正確認</title></head>";
echo "<body>";
echo "<h1>✅ AI_list.php修正確認</h1>";

// データベース接続
include "db_connect.php";

echo "<h2>📊 データベース確認</h2>";
if (isset($conn) && $conn) {
    $total_sql = "SELECT COUNT(*) as total FROM ai_tools";
    $total_result = $conn->query($total_sql);
    
    if ($total_result) {
        $total_count = $total_result->fetch_assoc()['total'];
        echo "<p style='color:green'>✓ データベース内総件数: <strong>$total_count</strong> 件</p>";
    }
}

echo "<h2>📄 AI_list.phpファイル確認</h2>";
$ai_list_file = 'AI_list.php';
if (file_exists($ai_list_file)) {
    $content = file_get_contents($ai_list_file);
    
    // LIMIT句の確認
    if (preg_match('/LIMIT\s+\d+/i', $content)) {
        echo "<p style='color:red'>✗ まだLIMIT句が残っています</p>";
    } else {
        echo "<p style='color:green'>✓ LIMIT句は削除されています</p>";
    }
    
    // ページネーションの確認
    if (preg_match('/pagination/i', $content)) {
        echo "<p style='color:orange'>⚠ ページネーション関連のコードが残っている可能性があります</p>";
    } else {
        echo "<p style='color:green'>✓ ページネーション関連のコードは削除されています</p>";
    }
    
    echo "<h3>SQLクエリ確認:</h3>";
    if (preg_match('/\$sql\s*=\s*["\']([^"\']+)["\']/', $content, $matches)) {
        echo "<pre style='background:#f8f9fa;padding:10px;border-radius:5px;'>" . htmlspecialchars($matches[1]) . "</pre>";
    }
}

echo "<h2>🔗 テストリンク</h2>";
echo "<div style='margin:20px 0;'>";
echo "<a href='AI_list.php' target='_blank' style='display:inline-block;padding:12px 24px;background:#007bff;color:white;text-decoration:none;border-radius:5px;margin-right:10px;'>AI_list.php（修正版）</a>";
echo "<a href='ranking.php' target='_blank' style='display:inline-block;padding:12px 24px;background:#28a745;color:white;text-decoration:none;border-radius:5px;'>ランキングページ（比較用）</a>";
echo "</div>";

echo "<h2>📋 期待される結果</h2>";
echo "<ul>";
echo "<li>AI_list.phpで20件以上のAIサービスが表示される</li>";
echo "<li>カード型レイアウトが維持される</li>";
echo "<li>検索・フィルター機能が正常に動作する</li>";
echo "<li>ページネーションが表示されない（スクロール型）</li>";
echo "</ul>";

echo "</body></html>";
?>
