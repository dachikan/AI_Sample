<?php
/**
 * アイコン表示問題の診断と修正
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>アイコン問題診断</title>";
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
echo ".icon-test { width: 32px; height: 32px; margin: 5px; border: 1px solid #ddd; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 アイコン表示問題の診断</h1>";
echo "<p>実行時刻: " . date('Y-m-d H:i:s') . "</p>";

// 1. imagesディレクトリの確認
echo "<div class='section'>";
echo "<h2>📁 Step 1: imagesディレクトリの確認</h2>";

$images_dir = 'images';
if (!is_dir($images_dir)) {
    echo "<p class='error'>✗ imagesディレクトリが存在しません</p>";
    if (mkdir($images_dir, 0755, true)) {
        echo "<p class='success'>✓ imagesディレクトリを作成しました</p>";
    }
} else {
    echo "<p class='success'>✓ imagesディレクトリが存在します</p>";
    echo "<p>パス: " . realpath($images_dir) . "</p>";
    echo "<p>権限: " . substr(sprintf('%o', fileperms($images_dir)), -4) . "</p>";
}
echo "</div>";

// 2. 必要なアイコンファイルの確認
echo "<div class='section'>";
echo "<h2>🖼️ Step 2: 必要なアイコンファイルの確認</h2>";

$required_icons = [
    'chatgpt-icon.png' => 'ChatGPT',
    'claude-icon.png' => 'Claude',
    'gemini-icon.png' => 'Gemini',
    'copilot-icon.png' => 'Copilot',
    'perplexity-icon.png' => 'Perplexity',
    'default-ai-icon.png' => 'Default AI'
];

echo "<table>";
echo "<tr><th>ファイル名</th><th>AIサービス</th><th>存在</th><th>サイズ</th><th>プレビュー</th><th>URL確認</th></tr>";

foreach ($required_icons as $filename => $service_name) {
    $filepath = $images_dir . '/' . $filename;
    $exists = file_exists($filepath);
    $size = $exists ? filesize($filepath) : 0;
    
    echo "<tr>";
    echo "<td>$filename</td>";
    echo "<td>$service_name</td>";
    echo "<td>" . ($exists ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td>";
    echo "<td>" . ($exists ? number_format($size) . " bytes" : "-") . "</td>";
    
    if ($exists) {
        echo "<td><img src='$filepath' class='icon-test' alt='$service_name' onerror='this.style.border=\"2px solid red\"'></td>";
        echo "<td><a href='$filepath' target='_blank'>確認</a></td>";
    } else {
        echo "<td>-</td>";
        echo "<td>-</td>";
    }
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 3. 実際のアイコンを作成
echo "<div class='section'>";
echo "<h2>🎨 Step 3: 不足アイコンの作成</h2>";

function createSimpleIcon($filename, $text, $bg_color, $text_color, $size = 64) {
    if (!extension_loaded('gd')) {
        return false;
    }
    
    $img = imagecreatetruecolor($size, $size);
    
    // 背景色
    $bg = imagecolorallocate($img, $bg_color[0], $bg_color[1], $bg_color[2]);
    imagefill($img, 0, 0, $bg);
    
    // テキスト色
    $text_col = imagecolorallocate($img, $text_color[0], $text_color[1], $text_color[2]);
    
    // テキストを中央に配置
    $font_size = 5;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    
    $x = ($size - $text_width) / 2;
    $y = ($size - $text_height) / 2;
    
    imagestring($img, $font_size, $x, $y, $text, $text_col);
    
    // PNGとして保存
    $result = imagepng($img, $filename);
    imagedestroy($img);
    
    return $result;
}

$icon_configs = [
    'chatgpt-icon.png' => ['text' => 'GPT', 'bg' => [16, 163, 127], 'text' => [255, 255, 255]],
    'claude-icon.png' => ['text' => 'CLD', 'bg' => [255, 107, 53], 'text' => [255, 255, 255]],
    'gemini-icon.png' => ['text' => 'GEM', 'bg' => [66, 133, 244], 'text' => [255, 255, 255]],
    'copilot-icon.png' => ['text' => 'COP', 'bg' => [0, 120, 212], 'text' => [255, 255, 255]],
    'perplexity-icon.png' => ['text' => 'PPX', 'bg' => [32, 201, 151], 'text' => [255, 255, 255]],
    'default-ai-icon.png' => ['text' => 'AI', 'bg' => [108, 117, 125], 'text' => [255, 255, 255]]
];

$created_count = 0;
foreach ($icon_configs as $filename => $config) {
    $filepath = $images_dir . '/' . $filename;
    if (!file_exists($filepath)) {
        if (createSimpleIcon($filepath, $config['text'], $config['bg'], $config['text'], 64)) {
            $created_count++;
            echo "<p class='success'>✓ $filename を作成しました</p>";
        } else {
            echo "<p class='error'>✗ $filename の作成に失敗しました</p>";
        }
    } else {
        echo "<p class='info'>ℹ $filename は既に存在します</p>";
    }
}

if ($created_count > 0) {
    echo "<p class='success'>✓ $created_count 個のアイコンを作成しました</p>";
}
echo "</div>";

// 4. HTMLでのアイコン参照テスト
echo "<div class='section'>";
echo "<h2>🌐 Step 4: HTMLでのアイコン参照テスト</h2>";

echo "<p>以下は実際のHTMLでアイコンが表示されるかのテストです：</p>";
echo "<table>";
echo "<tr><th>サービス名</th><th>アイコン表示テスト</th><th>パス</th></tr>";

foreach ($required_icons as $filename => $service_name) {
    $filepath = $images_dir . '/' . $filename;
    echo "<tr>";
    echo "<td>$service_name</td>";
    echo "<td>";
    if (file_exists($filepath)) {
        echo "<img src='$filepath' style='width:32px;height:32px;border:1px solid #ddd;' alt='$service_name' onerror='this.style.border=\"2px solid red\"; this.alt=\"ERROR\"'>";
    } else {
        echo "<span style='color:red;'>ファイルなし</span>";
    }
    echo "</td>";
    echo "<td>$filepath</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 5. 既存ページのアイコン参照を確認
echo "<div class='section'>";
echo "<h2>📄 Step 5: 既存ページのアイコン参照確認</h2>";

$pages_to_check = ['index.php', 'AI_list.php', 'AI_comparison.php'];
foreach ($pages_to_check as $page) {
    if (file_exists($page)) {
        $content = file_get_contents($page);
        $icon_references = [];
        
        // 画像参照を検索
        preg_match_all('/src=["\']([^"\']*\.(?:png|jpg|jpeg|gif|svg))["\']/', $content, $matches);
        if (!empty($matches[1])) {
            $icon_references = array_unique($matches[1]);
        }
        
        echo "<h4>$page</h4>";
        if (!empty($icon_references)) {
            echo "<ul>";
            foreach ($icon_references as $ref) {
                $exists = file_exists($ref);
                echo "<li>" . ($exists ? "✓" : "✗") . " $ref</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='warning'>⚠ 画像参照が見つかりませんでした</p>";
        }
    } else {
        echo "<p class='error'>✗ $page が見つかりません</p>";
    }
}
echo "</div>";

// 6. 修正提案
echo "<div class='section'>";
echo "<h2>🔧 Step 6: 修正提案</h2>";
echo "<ol>";
echo "<li><strong>アイコンファイルの確認</strong>: 上記で作成されたアイコンが正しく表示されるか確認</li>";
echo "<li><strong>パスの修正</strong>: HTMLでの画像パスが正しいか確認</li>";
echo "<li><strong>権限の確認</strong>: imagesディレクトリとファイルの読み取り権限を確認</li>";
echo "<li><strong>キャッシュのクリア</strong>: ブラウザのキャッシュをクリアして再確認</li>";
echo "</ol>";

echo "<h3>次のアクション:</h3>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>トップページを確認</a></li>";
echo "<li><a href='AI_list.php' target='_blank'>一覧ページを確認</a></li>";
echo "<li><a href='AI_comparison.php?ids[]=1&ids[]=2' target='_blank'>比較ページを確認</a></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>
