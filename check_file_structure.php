<?php
/**
 * ファイル構造を確認し、正しいファイルを特定するスクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>ファイル構造確認</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }";
echo ".section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".error { color: #dc3545; font-weight: bold; }";
echo ".warning { color: #ffc107; font-weight: bold; }";
echo ".info { color: #17a2b8; font-weight: bold; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background-color: #f8f9fa; }";
echo ".btn { display: inline-block; padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; background-color: #007bff; border: none; cursor: pointer; }";
echo ".btn-success { background-color: #28a745; }";
echo ".btn-warning { background-color: #ffc107; color: #000; }";
echo ".btn-danger { background-color: #dc3545; }";
echo "pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; max-height: 300px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 ファイル構造確認</h1>";
echo "<p>実行時刻: " . date('Y-m-d H:i:s') . "</p>";

// 1. 関連ファイルの検索
echo "<div class='section'>";
echo "<h2>📁 関連ファイルの検索</h2>";

$search_patterns = [
    '*list*.php',
    '*index*.php',
    'AI_*.php'
];

$found_files = [];
foreach ($search_patterns as $pattern) {
    $files = glob($pattern);
    if ($files) {
        $found_files = array_merge($found_files, $files);
    }
}

// 重複を除去してソート
$found_files = array_unique($found_files);
sort($found_files);

if (!empty($found_files)) {
    echo "<p class='success'>✓ " . count($found_files) . " 個の関連ファイルが見つかりました</p>";
    
    echo "<table>";
    echo "<tr><th>ファイル名</th><th>サイズ</th><th>更新日時</th><th>内容確認</th><th>URL</th></tr>";
    
    foreach ($found_files as $file) {
        if (file_exists($file)) {
            $size = filesize($file);
            $modified = date('Y-m-d H:i:s', filemtime($file));
            
            // ファイルの最初の数行を取得
            $content_preview = "";
            $handle = fopen($file, "r");
            if ($handle) {
                for ($i = 0; $i < 5; $i++) {
                    if (($buffer = fgets($handle, 4096)) !== false) {
                        $content_preview .= htmlspecialchars($buffer);
                    } else {
                        break;
                    }
                }
                fclose($handle);
            }
            
            echo "<tr>";
            echo "<td><strong>$file</strong></td>";
            echo "<td>" . number_format($size) . " bytes</td>";
            echo "<td>$modified</td>";
            echo "<td><pre style='max-height:100px;overflow:auto;margin:0;'>" . $content_preview . "...</pre></td>";
            echo "<td><a href='$file' target='_blank' class='btn'>開く</a></td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
} else {
    echo "<p class='error'>✗ 関連ファイルが見つかりませんでした</p>";
}
echo "</div>";

// 2. ファイル内容の検索（ページネーション関連）
echo "<div class='section'>";
echo "<h2>🔎 ページネーション関連コードの検索</h2>";

$pagination_files = [];
foreach ($found_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'pagination') !== false || 
            strpos($content, 'page-item') !== false || 
            strpos($content, 'page-link') !== false ||
            preg_match('/[\'"]page[\'"]\s*=>\s*/', $content)) {
            $pagination_files[$file] = $content;
        }
    }
}

if (!empty($pagination_files)) {
    echo "<p class='info'>ℹ " . count($pagination_files) . " 個のファイルにページネーション関連コードが見つかりました</p>";
    
    echo "<table>";
    echo "<tr><th>ファイル名</th><th>ページネーション関連コード</th></tr>";
    
    foreach ($pagination_files as $file => $content) {
        // ページネーション関連の行を抽出
        preg_match_all('/.*(pagination|page-item|page-link|[\'"]page[\'"]\s*=>).*/', $content, $matches);
        $pagination_lines = implode("\n", array_slice($matches[0], 0, 5)); // 最初の5行だけ表示
        
        echo "<tr>";
        echo "<td><strong>$file</strong></td>";
        echo "<td><pre style='max-height:150px;overflow:auto;margin:0;'>" . htmlspecialchars($pagination_lines) . "...</pre></td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p class='warning'>⚠ ページネーション関連コードが見つかりませんでした</p>";
}
echo "</div>";

// 3. 実際のAI_list.phpの内容を確認
echo "<div class='section'>";
echo "<h2>📄 AI_list.phpの内容確認</h2>";

$ai_list_file = 'AI_list.php';
if (file_exists($ai_list_file)) {
    $content = file_get_contents($ai_list_file);
    $size = filesize($ai_list_file);
    $modified = date('Y-m-d H:i:s', filemtime($ai_list_file));
    
    echo "<p class='success'>✓ AI_list.phpが存在します</p>";
    echo "<p>サイズ: " . number_format($size) . " bytes</p>";
    echo "<p>更新日時: $modified</p>";
    
    // 重要な部分を抽出
    $html_structure = "";
    if (preg_match('/<body.*?>(.*?)<\/body>/is', $content, $matches)) {
        $html_structure = $matches[1];
    }
    
    echo "<h3>HTML構造の一部:</h3>";
    echo "<pre style='max-height:300px;overflow:auto;'>" . htmlspecialchars(substr($html_structure, 0, 1000)) . "...</pre>";
    
    // ページネーション部分を特定
    $pagination = "";
    if (preg_match('/<ul\s+class=["\']pagination.*?<\/ul>/is', $content, $matches)) {
        $pagination = $matches[0];
    }
    
    if (!empty($pagination)) {
        echo "<h3>ページネーション部分:</h3>";
        echo "<pre style='max-height:150px;overflow:auto;'>" . htmlspecialchars($pagination) . "</pre>";
    }
} else {
    echo "<p class='error'>✗ AI_list.phpが見つかりません</p>";
}
echo "</div>";

// 4. 修正方法の提案
echo "<div class='section'>";
echo "<h2>🔧 修正方法の提案</h2>";

echo "<p>ファイル構造の確認結果に基づいて、以下の修正方法を提案します：</p>";
echo "<ol>";
echo "<li>実際に使用されているファイル名を特定（AI_list.php または別名）</li>";
echo "<li>特定したファイルのバックアップを作成</li>";
echo "<li>カード型レイアウトのコードを正しいファイルに適用</li>";
echo "<li>ページネーションを削除し、スクロール型に変更</li>";
echo "</ol>";

echo "<p>以下のリンクから修正スクリプトを実行できます：</p>";
echo "<a href='fix_actual_list_page.php' class='btn btn-success'>実際のリストページを修正</a>";
echo "</div>";

echo "</body></html>";
?>
