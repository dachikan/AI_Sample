<?php
/**
 * シンプルデバッグツール
 * 基本的なシステム情報とエラー状況を確認
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>デバッグ情報</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }";
echo ".section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".error { color: #dc3545; font-weight: bold; }";
echo ".warning { color: #ffc107; font-weight: bold; }";
echo ".info { color: #17a2b8; font-weight: bold; }";
echo "pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }";
echo "table { width: 100%; border-collapse: collapse; }";
echo "th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background-color: #f2f2f2; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 システムデバッグ情報</h1>";
echo "<p>実行時刻: " . date('Y-m-d H:i:s') . "</p>";

// 1. PHP基本情報
echo "<div class='section'>";
echo "<h2>📋 PHP基本情報</h2>";
echo "<table>";
echo "<tr><th>項目</th><th>値</th></tr>";
echo "<tr><td>PHPバージョン</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>サーバーソフトウェア</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>ドキュメントルート</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>現在のディレクトリ</td><td>" . getcwd() . "</td></tr>";
echo "<tr><td>スクリプト名</td><td>" . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "</td></tr>";
echo "</table>";
echo "</div>";

// 2. ファイル存在チェック
echo "<div class='section'>";
echo "<h2>📁 重要ファイル存在チェック</h2>";
$important_files = [
    'db_connect.php',
    'db_connect_extended.php',
    'includes/header.php',
    'includes/footer.php',
    'index.php',
    'list.php',
    'detail.php',
    'comparison.php',
    'ranking.php',
    'search.php',
    'AI_comparison.php'
];

echo "<table>";
echo "<tr><th>ファイル名</th><th>存在</th><th>読み取り可能</th><th>サイズ</th></tr>";
foreach ($important_files as $file) {
    $exists = file_exists($file);
    $readable = $exists ? is_readable($file) : false;
    $size = $exists ? filesize($file) : 0;
    
    echo "<tr>";
    echo "<td>$file</td>";
    echo "<td>" . ($exists ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td>";
    echo "<td>" . ($readable ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td>";
    echo "<td>" . ($exists ? number_format($size) . " bytes" : "-") . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 3. データベース接続テスト
echo "<div class='section'>";
echo "<h2>🗄️ データベース接続テスト</h2>";

// db_connect.phpの読み込みテスト
if (file_exists('db_connect.php')) {
    echo "<p class='info'>db_connect.php を読み込み中...</p>";
    try {
        ob_start();
        include 'db_connect.php';
        $include_output = ob_get_clean();
        
        if (!empty($include_output)) {
            echo "<p class='warning'>include時の出力:</p>";
            echo "<pre>" . htmlspecialchars($include_output) . "</pre>";
        }
        
        if (isset($conn)) {
            echo "<p class='success'>✓ \$conn変数が定義されました</p>";
            
            // 接続テスト
            if ($conn instanceof mysqli) {
                echo "<p class='success'>✓ MySQLi接続オブジェクトです</p>";
                
                // 簡単なクエリテスト
                $result = $conn->query("SELECT 1 as test");
                if ($result) {
                    echo "<p class='success'>✓ データベースクエリが成功しました</p>";
                    $result->free();
                } else {
                    echo "<p class='error'>✗ クエリエラー: " . $conn->error . "</p>";
                }
                
                // テーブル存在チェック
                $tables = ['ai_tools', 'categories', 'features', 'pricing_plans'];
                echo "<h3>テーブル存在チェック</h3>";
                echo "<table>";
                echo "<tr><th>テーブル名</th><th>存在</th><th>レコード数</th></tr>";
                
                foreach ($tables as $table) {
                    $check_result = $conn->query("SHOW TABLES LIKE '$table'");
                    $exists = $check_result && $check_result->num_rows > 0;
                    
                    $count = 0;
                    if ($exists) {
                        $count_result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
                        if ($count_result) {
                            $count_row = $count_result->fetch_assoc();
                            $count = $count_row['cnt'];
                            $count_result->free();
                        }
                    }
                    
                    echo "<tr>";
                    echo "<td>$table</td>";
                    echo "<td>" . ($exists ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td>";
                    echo "<td>" . ($exists ? number_format($count) : "-") . "</td>";
                    echo "</tr>";
                    
                    if ($check_result) $check_result->free();
                }
                echo "</table>";
                
            } else {
                echo "<p class='error'>✗ \$connは有効なMySQLi接続ではありません</p>";
                echo "<p>型: " . gettype($conn) . "</p>";
            }
        } else {
            echo "<p class='error'>✗ \$conn変数が定義されていません</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ include エラー: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>✗ db_connect.php が見つかりません</p>";
}
echo "</div>";

// 4. エラーログチェック
echo "<div class='section'>";
echo "<h2>📝 エラーログ情報</h2>";
$error_log_path = ini_get('error_log');
echo "<p>エラーログパス: " . ($error_log_path ?: 'デフォルト') . "</p>";

// 最近のエラーログを確認（可能な場合）
if ($error_log_path && file_exists($error_log_path) && is_readable($error_log_path)) {
    $log_content = file_get_contents($error_log_path);
    $lines = explode("\n", $log_content);
    $recent_lines = array_slice($lines, -10); // 最後の10行
    
    echo "<h3>最近のエラーログ（最後の10行）</h3>";
    echo "<pre>";
    foreach ($recent_lines as $line) {
        if (!empty(trim($line))) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p class='warning'>エラーログファイルにアクセスできません</p>";
}
echo "</div>";

// 5. PHP設定情報
echo "<div class='section'>";
echo "<h2>⚙️ 重要なPHP設定</h2>";
echo "<table>";
echo "<tr><th>設定項目</th><th>値</th></tr>";
echo "<tr><td>display_errors</td><td>" . (ini_get('display_errors') ? 'On' : 'Off') . "</td></tr>";
echo "<tr><td>error_reporting</td><td>" . ini_get('error_reporting') . "</td></tr>";
echo "<tr><td>log_errors</td><td>" . (ini_get('log_errors') ? 'On' : 'Off') . "</td></tr>";
echo "<tr><td>max_execution_time</td><td>" . ini_get('max_execution_time') . " 秒</td></tr>";
echo "<tr><td>memory_limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "<tr><td>upload_max_filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "</table>";
echo "</div>";

// 6. 拡張モジュール
echo "<div class='section'>";
echo "<h2>🔧 重要な拡張モジュール</h2>";
$extensions = ['mysqli', 'pdo', 'gd', 'curl', 'json', 'mbstring'];
echo "<table>";
echo "<tr><th>拡張モジュール</th><th>状態</th></tr>";
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<tr>";
    echo "<td>$ext</td>";
    echo "<td>" . ($loaded ? "<span class='success'>✓ 有効</span>" : "<span class='error'>✗ 無効</span>") . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🎯 次のステップ</h2>";
echo "<ol>";
echo "<li>上記の情報を確認して、赤い✗マークの項目を修正</li>";
echo "<li>データベース接続に問題がある場合は、db_connect.php を確認</li>";
echo "<li>ファイルが見つからない場合は、ファイルパスを確認</li>";
echo "<li>問題が特定できたら、該当するファイルを修正</li>";
echo "</ol>";
echo "</div>";

echo "</body>";
echo "</html>";
?>
