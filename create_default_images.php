<?php
/**
 * デフォルトのAIアイコン画像を作成
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>デフォルト画像作成</title></head>";
echo "<body>";
echo "<h1>🖼️ デフォルトAIアイコン作成</h1>";

// imagesディレクトリを作成
$images_dir = 'images';
if (!is_dir($images_dir)) {
    if (mkdir($images_dir, 0755, true)) {
        echo "<p style='color:green'>✓ imagesディレクトリを作成しました</p>";
    } else {
        echo "<p style='color:red'>✗ imagesディレクトリの作成に失敗しました</p>";
    }
} else {
    echo "<p style='color:green'>✓ imagesディレクトリは既に存在します</p>";
}

// デフォルトアイコンを作成する関数
function createDefaultIcon($filename, $text, $bg_color, $text_color) {
    if (!extension_loaded('gd')) {
        return false;
    }
    
    $width = 64;
    $height = 64;
    
    $img = imagecreatetruecolor($width, $height);
    
    // 背景色
    $bg = imagecolorallocate($img, $bg_color[0], $bg_color[1], $bg_color[2]);
    imagefill($img, 0, 0, $bg);
    
    // テキスト色
    $text_col = imagecolorallocate($img, $text_color[0], $text_color[1], $text_color[2]);
    
    // テキストを中央に配置
    $font_size = 3;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    imagestring($img, $font_size, $x, $y, $text, $text_col);
    
    // PNGとして保存
    $result = imagepng($img, $filename);
    imagedestroy($img);
    
    return $result;
}

// AIサービスのアイコンを作成
$ai_services = [
    'chatgpt-icon.png' => ['text' => 'GPT', 'bg' => [16, 163, 127], 'text' => [255, 255, 255]],
    'claude-icon.png' => ['text' => 'CLD', 'bg' => [255, 107, 53], 'text' => [255, 255, 255]],
    'gemini-icon.png' => ['text' => 'GEM', 'bg' => [66, 133, 244], 'text' => [255, 255, 255]],
    'copilot-icon.png' => ['text' => 'COP', 'bg' => [0, 120, 212], 'text' => [255, 255, 255]],
    'perplexity-icon.png' => ['text' => 'PPX', 'bg' => [32, 201, 151], 'text' => [255, 255, 255]]
];

echo "<h2>アイコン作成結果</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ファイル名</th><th>結果</th><th>詳細</th></tr>";

foreach ($ai_services as $filename => $config) {
    $filepath = $images_dir . '/' . $filename;
    $success = false;
    $message = "";
    
    if (file_exists($filepath)) {
        $message = "既に存在します";
        $success = true;
    } else {
        if (createDefaultIcon($filepath, $config['text'], $config['bg'], $config['text'])) {
            $success = true;
            $message = "作成成功";
        } else {
            $message = "作成失敗（GD拡張が必要）";
        }
    }
    
    echo "<tr>";
    echo "<td>$filename</td>";
    echo "<td style='color: " . ($success ? "green" : "red") . ";'>" . ($success ? "✓" : "✗") . "</td>";
    echo "<td>$message</td>";
    echo "</tr>";
}

echo "</table>";

// デフォルト画像も作成
$default_icon = $images_dir . '/default-ai-icon.png';
if (!file_exists($default_icon)) {
    if (createDefaultIcon($default_icon, 'AI', [128, 128, 128], [255, 255, 255])) {
        echo "<p style='color:green'>✓ デフォルトアイコンを作成しました: $default_icon</p>";
    }
}

echo "<h2>🎯 次のステップ</h2>";
echo "<ol>";
echo "<li>AI_comparison.phpの修正を実行</li>";
echo "<li>修正後、AI_comparison.phpにアクセスしてテスト</li>";
echo "<li>必要に応じて画像ファイルを実際のAIサービスのロゴに置き換え</li>";
echo "</ol>";

echo "</body></html>";
?>
