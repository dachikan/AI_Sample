<?php
/**
 * AI_comparison.phpの内容を確認するスクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>AI_comparison.php確認</title></head>";
echo "<body>";
echo "<h1>🔍 AI_comparison.php確認</h1>";

// ファイルの存在確認
if (file_exists('AI_comparison.php')) {
    echo "<p style='color:green'>✓ ファイルは存在します</p>";
    
    // ファイルの内容を取得
    $content = file_get_contents('AI_comparison.php');
    
    if ($content !== false) {
        echo "<h2>ファイルの内容（安全に表示）</h2>";
        echo "<pre style='background:#f5f5f5;padding:10px;border:1px solid #ddd;overflow:auto;max-height:400px'>";
        echo htmlspecialchars($content);
        echo "</pre>";
        
        // 基本的な構文エラーをチェック
        try {
            $tokens = token_get_all($content);
            echo "<p style='color:green'>✓ PHP構文エラーはありません</p>";
        } catch (Exception $e) {
            echo "<p style='color:red'>✗ PHP構文エラー: " . $e->getMessage() . "</p>";
        }
        
        // データベース関連の参照を確認
        $db_references = [];
        if (preg_match_all('/\$conn->query\([\'"]SELECT.*?FROM\s+([a-zA-Z0-9_]+)/is', $content, $matches)) {
            $db_references = $matches[1];
        }
        
        if (!empty($db_references)) {
            echo "<h2>参照しているテーブル</h2>";
            echo "<ul>";
            foreach ($db_references as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>データベーステーブルへの明示的な参照は見つかりませんでした</p>";
        }
        
        // include/require文を確認
        $includes = [];
        if (preg_match_all('/(include|require|include_once|require_once)\s*[\(\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
            $includes = $matches[2];
        }
        
        if (!empty($includes)) {
            echo "<h2>インクルードしているファイル</h2>";
            echo "<ul>";
            foreach ($includes as $file) {
                $exists = file_exists($file);
                echo "<li>$file " . ($exists ? "<span style='color:green'>✓</span>" : "<span style='color:red'>✗</span>") . "</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "<p style='color:red'>✗ ファイルの内容を読み取れませんでした</p>";
    }
} else {
    echo "<p style='color:red'>✗ AI_comparison.phpファイルが見つかりません</p>";
}

echo "<h2>🔧 推奨される対応</h2>";
echo "<ol>";
echo "<li>AI_comparison.phpの内容を確認し、エラーの原因となっている部分を特定</li>";
echo "<li>参照しているテーブルがある場合は、それらが存在するか確認</li>";
echo "<li>インクルードしているファイルが存在するか確認</li>";
echo "</ol>";

echo "<p><a href='simple_debug.php'>デバッグ情報に戻る</a></p>";

echo "</body></html>";
?>
