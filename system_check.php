<?php
// システム全体の状態をチェックするページ
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 システム状態チェック</h1>";

// 1. ファイル存在チェック
echo "<h2>📁 ファイル存在チェック</h2>";
$requiredFiles = [
    'db_connect.php',
    'db_connect_extended.php',
    'includes/header.php',
    'includes/footer.php',
    'AI_index.php',
    'AI_list.php',
    'AI_detail.php',
    'AI_comparison.php',
    'AI_ranking.php',
    'AI_search.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file<br>";
    } else {
        echo "❌ $file <strong>NOT FOUND</strong><br>";
    }
}

// 2. imagesフォルダチェック
echo "<h2>🖼️ 画像フォルダチェック</h2>";
if (file_exists('images') && is_dir('images')) {
    echo "✅ imagesフォルダが存在します<br>";
    
    $imageFiles = glob('images/*.{png,jpg,jpeg,gif,svg}', GLOB_BRACE);
    echo "📊 画像ファイル数: " . count($imageFiles) . "<br>";
    
    if (file_exists('images/default-ai-icon.png')) {
        echo "✅ default-ai-icon.png が存在します<br>";
    } else {
        echo "❌ default-ai-icon.png が見つかりません<br>";
    }
} else {
    echo "❌ imagesフォルダが存在しません<br>";
}

// 3. データベース接続チェック
echo "<h2>🗄️ データベース接続チェック</h2>";
try {
    require_once 'db_connect_extended.php';
    echo "✅ データベース接続ファイル読み込み成功<br>";
    
    $count = getAIServiceCount();
    echo "✅ AIサービス数: $count<br>";
    
    $types = getAITypesFromInfo();
    echo "✅ AIタイプ数: " . count($types) . "<br>";
    
} catch (Exception $e) {
    echo "❌ データベースエラー: " . $e->getMessage() . "<br>";
}

// 4. 権限チェック
echo "<h2>🔐 権限チェック</h2>";
if (is_writable('.')) {
    echo "✅ 現在のディレクトリに書き込み権限があります<br>";
} else {
    echo "❌ 現在のディレクトリに書き込み権限がありません<br>";
}

if (file_exists('images') && is_writable('images')) {
    echo "✅ imagesディレクトリに書き込み権限があります<br>";
} else {
    echo "❌ imagesディレクトリに書き込み権限がありません<br>";
}

echo "<br><h2>🚀 推奨アクション</h2>";
echo "<ol>";
echo "<li><a href='create_images_folder.php'>create_images_folder.phpを実行</a>して画像ファイルを作成</li>";
echo "<li>不足しているファイルをアップロード</li>";
echo "<li><a href='AI_index.php'>AI_index.php</a>にアクセスして動作確認</li>";
echo "</ol>";
?>
