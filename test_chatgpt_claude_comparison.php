<?php
/**
 * ChatGPTとClaudeの比較テスト専用スクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>ChatGPT vs Claude 比較テスト</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }";
echo ".test-section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".error { color: #dc3545; font-weight: bold; }";
echo ".warning { color: #ffc107; font-weight: bold; }";
echo ".info { color: #17a2b8; font-weight: bold; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background-color: #f8f9fa; font-weight: bold; }";
echo ".btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; }";
echo ".btn-primary { background-color: #007bff; }";
echo ".btn-success { background-color: #28a745; }";
echo ".btn-info { background-color: #17a2b8; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🧪 ChatGPT vs Claude 比較テスト</h1>";
echo "<p>実行時刻: " . date('Y-m-d H:i:s') . "</p>";

// テスト1: AI_comparison.phpファイルの存在確認
echo "<div class='test-section'>";
echo "<h2>📁 テスト1: ファイル存在確認</h2>";
if (file_exists('AI_comparison.php')) {
    echo "<p class='success'>✓ AI_comparison.php が存在します</p>";
    echo "<p>ファイルサイズ: " . number_format(filesize('AI_comparison.php')) . " bytes</p>";
} else {
    echo "<p class='error'>✗ AI_comparison.php が見つかりません</p>";
}
echo "</div>";

// テスト2: URLパラメータのシミュレーション
echo "<div class='test-section'>";
echo "<h2>🔗 テスト2: URLパラメータシミュレーション</h2>";
$_GET['ids'] = ['1', '2']; // ChatGPT(1) と Claude(2)
echo "<p class='info'>シミュレートされたパラメータ: ids[]=1&ids[]=2</p>";
echo "<p>処理後: ";
$selectedIds = array_map("intval", $_GET['ids']);
var_dump($selectedIds);
echo "</p>";
echo "</div>";

// テスト3: サービスデータの確認
echo "<div class='test-section'>";
echo "<h2>📊 テスト3: サービスデータ確認</h2>";

$services = [
    [
        "id" => 1,
        "ai_service" => "ChatGPT",
        "ai_icon" => "images/chatgpt-icon.png",
        "basic_info" => "OpenAI開発の対話型AI<br>GPT-4技術を使用<br>2022年11月リリース",
        "pricing" => "<strong>無料:</strong> GPT-3.5使用<br><strong>Plus ($20/月):</strong> GPT-4使用<br><strong>Team ($25/月):</strong> チーム機能"
    ],
    [
        "id" => 2,
        "ai_service" => "Claude",
        "ai_icon" => "images/claude-icon.png",
        "basic_info" => "Anthropic開発のAI<br>Constitutional AI技術<br>2023年3月リリース",
        "pricing" => "<strong>無料:</strong> 基本機能<br><strong>Pro ($20/月):</strong> 優先アクセス<br><strong>Team ($25/月):</strong> チーム機能"
    ]
];

$validSelectedServices = [];
foreach ($selectedIds as $id) {
    foreach ($services as $service) {
        if ($service["id"] == $id) {
            $validSelectedServices[] = $service;
            break;
        }
    }
}

echo "<p class='success'>✓ 選択されたサービス数: " . count($validSelectedServices) . "</p>";
echo "<table>";
echo "<tr><th>ID</th><th>サービス名</th><th>アイコンパス</th><th>基本情報</th></tr>";
foreach ($validSelectedServices as $service) {
    echo "<tr>";
    echo "<td>" . $service['id'] . "</td>";
    echo "<td>" . $service['ai_service'] . "</td>";
    echo "<td>" . $service['ai_icon'] . "</td>";
    echo "<td>" . strip_tags($service['basic_info']) . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// テスト4: 実際のページ読み込みテスト
echo "<div class='test-section'>";
echo "<h2>🌐 テスト4: 実際のページ読み込み</h2>";
try {
    $url = 'AI_comparison.php?ids[]=1&ids[]=2';
    echo "<p class='info'>テストURL: <code>$url</code></p>";
    
    // 簡単なHTTPヘッダーチェック
    $headers = get_headers('http://' . $_SERVER['HTTP_HOST'] . '/' . $url);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "<p class='success'>✓ ページが正常に応答しています (HTTP 200)</p>";
    } elseif ($headers && strpos($headers[0], '500') !== false) {
        echo "<p class='error'>✗ サーバーエラーが発生しています (HTTP 500)</p>";
    } else {
        echo "<p class='warning'>⚠ 応答を確認できませんでした</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ テスト中にエラーが発生しました: " . $e->getMessage() . "</p>";
}
echo "</div>";

// テスト5: 画像ファイルの確認
echo "<div class='test-section'>";
echo "<h2>🖼️ テスト5: 画像ファイル確認</h2>";
$image_files = ['images/chatgpt-icon.png', 'images/claude-icon.png', 'images/default-ai-icon.png'];
echo "<table>";
echo "<tr><th>画像ファイル</th><th>存在</th><th>サイズ</th></tr>";
foreach ($image_files as $image) {
    $exists = file_exists($image);
    $size = $exists ? filesize($image) : 0;
    echo "<tr>";
    echo "<td>$image</td>";
    echo "<td>" . ($exists ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td>";
    echo "<td>" . ($exists ? number_format($size) . " bytes" : "-") . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// アクションボタン
echo "<div class='test-section'>";
echo "<h2>🎯 テストアクション</h2>";
echo "<a href='AI_comparison.php?ids[]=1&ids[]=2' target='_blank' class='btn btn-primary'>ChatGPT vs Claude を開く</a>";
echo "<a href='AI_comparison.php' target='_blank' class='btn btn-info'>比較ページを開く</a>";
echo "<a href='simple_debug.php' class='btn btn-success'>デバッグ情報に戻る</a>";
echo "</div>";

// 期待される結果
echo "<div class='test-section'>";
echo "<h2>✅ 期待される結果</h2>";
echo "<ol>";
echo "<li>ページが正常に読み込まれる（500エラーなし）</li>";
echo "<li>ChatGPTとClaudeのチェックボックスが選択状態で表示される</li>";
echo "<li>比較テーブルに2つの列が表示される</li>";
echo "<li>各列にChatGPTとClaudeの固有情報が表示される</li>";
echo "<li>料金プラン、特徴などが異なる内容で表示される</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
