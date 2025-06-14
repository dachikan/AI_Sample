<?php
/**
 * AI_list.phpからLIMIT句を削除して全件表示にするスクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>LIMIT句削除修正</title></head>";
echo "<body>";
echo "<h1>🔧 AI_list.php LIMIT句削除修正</h1>";

$ai_list_file = 'AI_list.php';

if (file_exists($ai_list_file)) {
    // バックアップを作成
    $backup_file = $ai_list_file . '.limit_backup.' . date('Ymd_His');
    copy($ai_list_file, $backup_file);
    echo "<p style='color:green'>✓ バックアップを作成しました: $backup_file</p>";
    
    // ファイル内容を読み取り
    $content = file_get_contents($ai_list_file);
    
    echo "<h2>🔍 修正前の状況</h2>";
    
    // LIMIT句を検索
    if (preg_match_all('/LIMIT\s+\d+/i', $content, $matches)) {
        echo "<p style='color:red'>⚠ 見つかったLIMIT句:</p>";
        echo "<ul>";
        foreach ($matches[0] as $limit) {
            echo "<li><code style='background:#f8f9fa;padding:2px 6px;'>$limit</code></li>";
        }
        echo "</ul>";
    }
    
    // 修正処理
    echo "<h2>🔄 修正処理</h2>";
    
    // LIMIT句を削除
    $original_content = $content;
    $content = preg_replace('/\s+LIMIT\s+\d+/i', '', $content);
    
    // ページネーション関連も削除
    $content = preg_replace('/\$page\s*=.*?;/s', '', $content);
    $content = preg_replace('/\$per_page\s*=.*?;/s', '', $content);
    $content = preg_replace('/\$offset\s*=.*?;/s', '', $content);
    
    // ページネーションHTML削除
    $content = preg_replace('/<nav[^>]*aria-label=["\']pagination["\'][^>]*>.*?<\/nav>/is', '', $content);
    $content = preg_replace('/<ul[^>]*class=["\'][^"\']*pagination[^"\']*["\'][^>]*>.*?<\/ul>/is', '', $content);
    
    if ($content !== $original_content) {
        // ファイルを更新
        if (file_put_contents($ai_list_file, $content)) {
            echo "<p style='color:green'>✓ LIMIT句とページネーションを削除しました</p>";
            echo "<p style='color:green'>✓ AI_list.phpが全件表示に修正されました</p>";
        } else {
            echo "<p style='color:red'>✗ ファイルの更新に失敗しました</p>";
        }
    } else {
        echo "<p style='color:orange'>⚠ 修正する箇所が見つかりませんでした</p>";
    }
    
} else {
    echo "<p style='color:red'>✗ AI_list.phpが見つかりません</p>";
}

echo "<h2>🧪 テスト</h2>";
echo "<p><a href='AI_list.php' target='_blank' style='display:inline-block;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;'>修正されたAI_list.phpを確認</a></p>";

echo "<h2>📝 修正内容</h2>";
echo "<ul>";
echo "<li>SQLクエリからLIMIT句を削除</li>";
echo "<li>ページネーション関連の変数を削除</li>";
echo "<li>ページネーションHTMLを削除</li>";
echo "<li>全件表示に変更</li>";
echo "</ul>";

echo "</body></html>";
?>
