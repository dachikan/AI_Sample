<?php
/**
 * AIアイコンの確認と最適化
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>AIアイコン確認・最適化</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }";
echo ".section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".error { color: #dc3545; font-weight: bold; }";
echo ".warning { color: #ffc107; font-weight: bold; }";
echo ".info { color: #17a2b8; font-weight: bold; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background-color: #f8f9fa; font-weight: bold; }";
echo ".icon-preview { width: 32px; height: 32px; margin-right: 10px; }";
echo ".btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; background-color: #007bff; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🖼️ AIアイコン確認・最適化</h1>";
echo "<p>実行時刻: " . date('Y-m-d H:i:s') . "</p>";

// 1. imagesディレクトリの確認
echo "<div class='section'>";
echo "<h2>📁 imagesディレクトリ確認</h2>";

$images_dir = 'images';
if (!is_dir($images_dir)) {
    echo "<p class='error'>✗ imagesディレクトリが存在しません</p>";
    if (mkdir($images_dir, 0755, true)) {
        echo "<p class='success'>✓ imagesディレクトリを作成しました</p>";
    }
} else {
    echo "<p class='success'>✓ imagesディレクトリが存在します</p>";
}

// 2. 既存のアイコンファイルを確認
echo "<h3>既存のアイコンファイル</h3>";
$existing_files = [];
if (is_dir($images_dir)) {
    $files = scandir($images_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && preg_match('/\.(png|jpg|jpeg|gif|svg)$/i', $file)) {
            $existing_files[] = $file;
        }
    }
}

if (!empty($existing_files)) {
    echo "<table>";
    echo "<tr><th>ファイル名</th><th>プレビュー</th><th>サイズ</th><th>更新日時</th></tr>";
    foreach ($existing_files as $file) {
        $filepath = $images_dir . '/' . $file;
        $size = filesize($filepath);
        $modified = date('Y-m-d H:i:s', filemtime($filepath));
        echo "<tr>";
        echo "<td>$file</td>";
        echo "<td><img src='$filepath' class='icon-preview' alt='$file' onerror='this.style.display=\"none\"'></td>";
        echo "<td>" . number_format($size) . " bytes</td>";
        echo "<td>$modified</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠ アイコンファイルが見つかりませんでした</p>";
}
echo "</div>";

// 3. 必要なアイコンの定義
echo "<div class='section'>";
echo "<h2>🎯 必要なAIアイコン</h2>";

$required_icons = [
    'chatgpt-icon.png' => 'ChatGPT',
    'claude-icon.png' => 'Claude',
    'gemini-icon.png' => 'Gemini',
    'copilot-icon.png' => 'Microsoft Copilot',
    'perplexity-icon.png' => 'Perplexity',
    'default-ai-icon.png' => 'デフォルトAI'
];

echo "<table>";
echo "<tr><th>必要なファイル</th><th>AIサービス</th><th>状態</th><th>アクション</th></tr>";
foreach ($required_icons as $filename => $service_name) {
    $filepath = $images_dir . '/' . $filename;
    $exists = file_exists($filepath);
    echo "<tr>";
    echo "<td>$filename</td>";
    echo "<td>$service_name</td>";
    if ($exists) {
        echo "<td><span class='success'>✓ 存在</span></td>";
        echo "<td><img src='$filepath' class='icon-preview' alt='$service_name'></td>";
    } else {
        echo "<td><span class='error'>✗ 不足</span></td>";
        echo "<td>作成が必要</td>";
    }
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 4. アイコンの最適化提案
echo "<div class='section'>";
echo "<h2>💡 アイコン最適化提案</h2>";
echo "<ol>";
echo "<li><strong>サイズ統一:</strong> 32x32px または 64x64px に統一</li>";
echo "<li><strong>フォーマット:</strong> PNG形式（透明背景対応）</li>";
echo "<li><strong>命名規則:</strong> [service]-icon.png の形式</li>";
echo "<li><strong>品質:</strong> 高解像度でクリアな画像</li>";
echo "<li><strong>ブランドガイドライン:</strong> 各サービスの公式カラーを使用</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
