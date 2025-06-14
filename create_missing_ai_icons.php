<?php
/**
 * 不足しているAIアイコンを作成
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>AIアイコン作成</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }";
echo ".section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".error { color: #dc3545; font-weight: bold; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background-color: #f8f9fa; }";
echo ".icon-preview { width: 48px; height: 48px; margin: 5px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🎨 AIアイコン作成ツール</h1>";

// imagesディレクトリを作成
$images_dir = 'images';
if (!is_dir($images_dir)) {
    mkdir($images_dir, 0755, true);
}

// アイコン作成関数
function createAIIcon($filename, $text, $bg_color, $text_color, $size = 64) {
    if (!extension_loaded('gd')) {
        return false;
    }
    
    $img = imagecreatetruecolor($size, $size);
    
    // 背景色
    $bg = imagecolorallocate($img, $bg_color[0], $bg_color[1], $bg_color[2]);
    imagefill($img, 0, 0, $bg);
    
    // 角を丸くする効果（簡易版）
    $corner_radius = $size / 8;
    for ($x = 0; $x < $corner_radius; $x++) {
        for ($y = 0; $y < $corner_radius; $y++) {
            if (($x * $x + $y * $y) > ($corner_radius * $corner_radius)) {
                imagesetpixel($img, $x, $y, imagecolorallocatealpha($img, 0, 0, 0, 127));
                imagesetpixel($img, $size - 1 - $x, $y, imagecolorallocatealpha($img, 0, 0, 0, 127));
                imagesetpixel($img, $x, $size - 1 - $y, imagecolorallocatealpha($img, 0, 0, 0, 127));
                imagesetpixel($img, $size - 1 - $x, $size - 1 - $y, imagecolorallocatealpha($img, 0, 0, 0, 127));
            }
        }
    }
    
    // テキスト色
    $text_col = imagecolorallocate($img, $text_color[0], $text_color[1], $text_color[2]);
    
    // テキストを中央に配置
    $font_size = $size > 32 ? 5 : 3;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    
    $x = ($size - $text_width) / 2;
    $y = ($size - $text_height) / 2;
    
    imagestring($img, $font_size, $x, $y, $text, $text_col);
    
    // 透明度を有効にする
    imagesavealpha($img, true);
    
    // PNGとして保存
    $result = imagepng($img, $filename);
    imagedestroy($img);
    
    return $result;
}

// AIサービスのアイコン設定
$ai_icons = [
    'chatgpt-icon.png' => [
        'text' => 'GPT',
        'bg' => [16, 163, 127],    // OpenAI Green
        'text' => [255, 255, 255],
        'name' => 'ChatGPT'
    ],
    'claude-icon.png' => [
        'text' => 'CLD',
        'bg' => [255, 107, 53],    // Anthropic Orange
        'text' => [255, 255, 255],
        'name' => 'Claude'
    ],
    'gemini-icon.png' => [
        'text' => 'GEM',
        'bg' => [66, 133, 244],    // Google Blue
        'text' => [255, 255, 255],
        'name' => 'Gemini'
    ],
    'copilot-icon.png' => [
        'text' => 'COP',
        'bg' => [0, 120, 212],     // Microsoft Blue
        'text' => [255, 255, 255],
        'name' => 'Copilot'
    ],
    'perplexity-icon.png' => [
        'text' => 'PPX',
        'bg' => [32, 201, 151],    // Perplexity Teal
        'text' => [255, 255, 255],
        'name' => 'Perplexity'
    ],
    'default-ai-icon.png' => [
        'text' => 'AI',
        'bg' => [108, 117, 125],   // Bootstrap Gray
        'text' => [255, 255, 255],
        'name' => 'Default AI'
    ]
];

echo "<div class='section'>";
echo "<h2>🎨 アイコン作成結果</h2>";
echo "<table>";
echo "<tr><th>ファイル名</th><th>AIサービス</th><th>結果</th><th>プレビュー</th></tr>";

foreach ($ai_icons as $filename => $config) {
    $filepath = $images_dir . '/' . $filename;
    $success = false;
    $message = "";
    
    if (file_exists($filepath)) {
        $message = "既に存在します";
        $success = true;
    } else {
        if (createAIIcon($filepath, $config['text'], $config['bg'], $config['text'], 64)) {
            $success = true;
            $message = "作成成功";
        } else {
            $message = "作成失敗（GD拡張が必要）";
        }
    }
    
    echo "<tr>";
    echo "<td>$filename</td>";
    echo "<td>" . $config['name'] . "</td>";
    echo "<td style='color: " . ($success ? "green" : "red") . ";'>" . ($success ? "✓" : "✗") . " $message</td>";
    if ($success && file_exists($filepath)) {
        echo "<td><img src='$filepath' class='icon-preview' alt='" . $config['name'] . "'></td>";
    } else {
        echo "<td>-</td>";
    }
    echo "</tr>";
}

echo "</table>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🔧 アイコン最適化のヒント</h2>";
echo "<h3>より良いアイコンを取得する方法:</h3>";
echo "<ol>";
echo "<li><strong>公式サイトから:</strong> 各AIサービスの公式サイトのプレスキットやブランドガイドライン</li>";
echo "<li><strong>アイコンライブラリ:</strong> Font Awesome、Heroicons、Lucide などのアイコンライブラリ</li>";
echo "<li><strong>無料リソース:</strong> Unsplash、Pixabay、Freepik などの無料画像サイト</li>";
echo "<li><strong>AI生成:</strong> DALL-E、Midjourney、Stable Diffusion でカスタムアイコン生成</li>";
echo "</ol>";

echo "<h3>推奨される公式リソース:</h3>";
echo "<ul>";
echo "<li><strong>ChatGPT:</strong> <a href='https://openai.com/brand' target='_blank'>OpenAI Brand Guidelines</a></li>";
echo "<li><strong>Claude:</strong> <a href='https://www.anthropic.com' target='_blank'>Anthropic公式サイト</a></li>";
echo "<li><strong>Gemini:</strong> <a href='https://developers.google.com/identity/branding-guidelines' target='_blank'>Google Brand Guidelines</a></li>";
echo "<li><strong>Copilot:</strong> <a href='https://docs.microsoft.com/en-us/style-guide/brand-voice-above-all-simple-human' target='_blank'>Microsoft Brand Guidelines</a></li>";
echo "</ul>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🎯 次のステップ</h2>";
echo "<ol>";
echo "<li><a href='check_ai_icons.php'>アイコン状況を再確認</a></li>";
echo "<li><a href='AI_comparison.php?ids[]=1&ids[]=2'>ChatGPT vs Claude比較をテスト</a></li>";
echo "<li>必要に応じて高品質なアイコンに置き換え</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
