<?php
/**
 * すべてのページのアイコン表示を強制修正
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>全ページアイコン修正</title></head>";
echo "<body>";
echo "<h1>🔧 全ページアイコン表示強制修正</h1>";

// 修正対象のページ
$pages = [
    'index.php' => 'トップページ',
    'AI_list.php' => '一覧ページ',
    'AI_comparison.php' => '比較ページ'
];

foreach ($pages as $filename => $page_name) {
    echo "<h2>📄 $page_name ($filename) の修正</h2>";
    
    if (!file_exists($filename)) {
        echo "<p style='color:red'>✗ $filename が見つかりません</p>";
        continue;
    }
    
    // バックアップを作成
    $backup_filename = $filename . '.backup.' . date('Ymd_His');
    if (copy($filename, $backup_filename)) {
        echo "<p style='color:green'>✓ バックアップ作成: $backup_filename</p>";
    }
    
    $content = file_get_contents($filename);
    
    // アイコン表示用のCSSを追加（存在しない場合）
    if (strpos($content, '.ai-icon') === false) {
        $css = '
<style>
.ai-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    margin-right: 8px;
    vertical-align: middle;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: inline-block;
}
.ai-icon-small {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    margin-right: 5px;
    vertical-align: middle;
}
</style>';
        
        $content = str_replace('</head>', $css . "\n</head>", $content);
        echo "<p style='color:blue'>ℹ CSSスタイルを追加しました</p>";
    }
    
    // 特定のページごとの修正
    if ($filename === 'index.php') {
        // トップページの修正
        // カードタイトルにアイコンを追加
        $pattern = '/<h5 class="card-title">\s*([^<]+)\s*<\/h5>/';
        $replacement = '<h5 class="card-title">
            <img src="images/default-ai-icon.png" alt="AI" class="ai-icon-small" onerror="this.style.display=\'none\'">
            $1
        </h5>';
        $content = preg_replace($pattern, $replacement, $content);
        
    } elseif ($filename === 'AI_list.php') {
        // 一覧ページの修正
        // サービス名の前にアイコンを追加
        $pattern = '/(<h[3-6][^>]*>)\s*([^<]+)\s*(<\/h[3-6]>)/';
        $replacement = '$1<img src="images/default-ai-icon.png" alt="AI" class="ai-icon-small" onerror="this.style.display=\'none\'">$2$3';
        $content = preg_replace($pattern, $replacement, $content);
        
    } elseif ($filename === 'AI_comparison.php') {
        // 比較ページは既に修正済みの可能性が高いので、確認のみ
        if (strpos($content, 'ai-icon') !== false) {
            echo "<p style='color:green'>✓ 比較ページは既にアイコン対応済みです</p>";
        }
    }
    
    // ファイルに書き込み
    if (file_put_contents($filename, $content)) {
        echo "<p style='color:green'>✓ $page_name を修正しました</p>";
    } else {
        echo "<p style='color:red'>✗ $page_name の修正に失敗しました</p>";
    }
    
    echo "<hr>";
}

echo "<h2>🎯 修正完了</h2>";
echo "<p>以下のページを確認してください：</p>";
echo "<ul>";
foreach ($pages as $filename => $page_name) {
    echo "<li><a href='$filename' target='_blank'>$page_name</a></li>";
}
echo "</ul>";

echo "<p><strong>注意:</strong> ブラウザのキャッシュをクリア（Ctrl+F5）してから確認してください。</p>";

echo "</body></html>";
?>
