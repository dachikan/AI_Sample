<?php
// デバッグ情報を表示するためのファイル
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 現在のディレクトリ構造を表示
function listDirectory($dir) {
    if (is_dir($dir)) {
        echo "<h3>ディレクトリ: " . htmlspecialchars($dir) . "</h3>";
        echo "<ul>";
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    echo "<li>📁 " . htmlspecialchars($file) . "</li>";
                } else {
                    echo "<li>📄 " . htmlspecialchars($file) . " (" . filesize($path) . " bytes)</li>";
                }
            }
        }
        echo "</ul>";
    } else {
        echo "<p>ディレクトリ " . htmlspecialchars($dir) . " は存在しません。</p>";
    }
}

// サーバー情報
echo "<h2>サーバー情報</h2>";
echo "<p>サーバーソフトウェア: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>ドキュメントルート: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>現在のスクリプトパス: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";

// PHPの設定情報
echo "<h2>PHP設定</h2>";
echo "<p>display_errors: " . ini_get('display_errors') . "</p>";
echo "<p>error_reporting: " . ini_get('error_reporting') . "</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>memory_limit: " . ini_get('memory_limit') . "</p>";

// ディレクトリ構造を表示
echo "<h2>ディレクトリ構造</h2>";
listDirectory(".");
listDirectory("images");
listDirectory("uploads");

// 画像ファイルのテスト表示
echo "<h2>画像テスト</h2>";
echo "<p>以下の画像が正しく表示されるか確認してください：</p>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
echo "<p>sample-image.png:</p>";
echo "<img src='sample-image.png' alt='サンプル画像' style='max-width: 200px;'>";
echo "</div>";
echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
echo "<p>placeholder.png:</p>";
echo "<img src='placeholder.png' alt='プレースホルダー' style='max-width: 200px;'>";
echo "</div>";
echo "</div>";

// エラーログの最新部分を表示（アクセス権限がある場合）
echo "<h2>エラーログ</h2>";
$errorLogFile = ini_get('error_log');
if (file_exists($errorLogFile) && is_readable($errorLogFile)) {
    echo "<p>エラーログファイル: " . htmlspecialchars($errorLogFile) . "</p>";
    echo "<pre>";
    passthru("tail -n 20 " . escapeshellarg($errorLogFile));
    echo "</pre>";
} else {
    echo "<p>エラーログファイルにアクセスできません。</p>";
    
    // 代替として、PHPのエラーログを作成
    $customLogFile = 'php-errors.log';
    ini_set('error_log', $customLogFile);
    error_log('デバッグ情報ページからのテストメッセージ');
    
    if (file_exists($customLogFile)) {
        echo "<p>カスタムエラーログファイル: " . htmlspecialchars($customLogFile) . "</p>";
        echo "<pre>";
        echo file_get_contents($customLogFile);
        echo "</pre>";
    }
}
?>
