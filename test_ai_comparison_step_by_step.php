<?php
/**
 * AI_comparison.phpを段階的にテストして500エラーの原因を特定
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>AI_comparison.php段階テスト</title></head>";
echo "<body>";
echo "<h1>🔍 AI_comparison.php段階テスト</h1>";

// ステップ1: db_connect_extended.phpのテスト
echo "<h2>ステップ1: db_connect_extended.php テスト</h2>";
try {
    ob_start();
    include 'db_connect_extended.php';
    $db_output = ob_get_clean();
    
    if (!empty($db_output)) {
        echo "<p style='color:orange'>⚠ db_connect_extended.php からの出力:</p>";
        echo "<pre style='background:#fff3cd;padding:10px;border:1px solid #ffeaa7'>" . htmlspecialchars($db_output) . "</pre>";
    } else {
        echo "<p style='color:green'>✓ db_connect_extended.php は正常に読み込まれました</p>";
    }
    
    if (isset($conn)) {
        echo "<p style='color:green'>✓ \$conn変数が定義されています</p>";
    } else {
        echo "<p style='color:red'>✗ \$conn変数が定義されていません</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ db_connect_extended.php エラー: " . $e->getMessage() . "</p>";
}

// ステップ2: includes/header.phpのテスト
echo "<h2>ステップ2: includes/header.php テスト</h2>";
try {
    ob_start();
    include 'includes/header.php';
    $header_output = ob_get_clean();
    
    if (!empty($header_output)) {
        echo "<p style='color:green'>✓ header.php が正常に読み込まれました</p>";
        echo "<p>出力サイズ: " . strlen($header_output) . " 文字</p>";
    } else {
        echo "<p style='color:orange'>⚠ header.php からの出力がありません</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ header.php エラー: " . $e->getMessage() . "</p>";
}

// ステップ3: AI_comparison.phpの主要部分をテスト
echo "<h2>ステップ3: AI_comparison.php 主要部分テスト</h2>";

// $servicesが定義されているかチェック
if (isset($services)) {
    echo "<p style='color:green'>✓ \$services変数が定義されています</p>";
    echo "<p>サービス数: " . count($services) . "</p>";
} else {
    echo "<p style='color:red'>✗ \$services変数が定義されていません</p>";
    echo "<p>これが500エラーの原因の可能性があります</p>";
}

// ステップ4: 実際にAI_comparison.phpを実行してみる
echo "<h2>ステップ4: AI_comparison.php 実行テスト</h2>";
echo "<p>AI_comparison.phpを安全に実行してみます...</p>";

try {
    // 出力バッファリングを使用して安全に実行
    ob_start();
    
    // エラーハンドラーを一時的に設定
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });
    
    include 'AI_comparison.php';
    
    // エラーハンドラーを元に戻す
    restore_error_handler();
    
    $comparison_output = ob_get_clean();
    
    echo "<p style='color:green'>✓ AI_comparison.php が正常に実行されました</p>";
    echo "<p>出力サイズ: " . strlen($comparison_output) . " 文字</p>";
    
    // 出力の最初の500文字を表示
    if (strlen($comparison_output) > 0) {
        echo "<h3>出力の最初の部分:</h3>";
        echo "<pre style='background:#f8f9fa;padding:10px;border:1px solid #dee2e6;max-height:200px;overflow:auto'>";
        echo htmlspecialchars(substr($comparison_output, 0, 500));
        if (strlen($comparison_output) > 500) {
            echo "\n... (続きがあります)";
        }
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ AI_comparison.php 実行エラー: " . $e->getMessage() . "</p>";
    echo "<p>ファイル: " . $e->getFile() . "</p>";
    echo "<p>行: " . $e->getLine() . "</p>";
    
    // これが500エラーの原因です
    echo "<div style='background:#f8d7da;color:#721c24;padding:15px;margin:10px 0;border:1px solid #f5c6cb;border-radius:5px'>";
    echo "<h3>🚨 500エラーの原因が特定されました</h3>";
    echo "<p><strong>エラー:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>場所:</strong> " . basename($e->getFile()) . " の " . $e->getLine() . " 行目</p>";
    echo "</div>";
    
    ob_end_clean(); // バッファをクリア
}

echo "<h2>🔧 推奨される対応</h2>";
echo "<ol>";
echo "<li>上記で特定されたエラーを修正する</li>";
echo "<li>\$services変数が未定義の場合は、適切に定義する</li>";
echo "<li>修正後、再度AI_comparison.phpにアクセスしてテストする</li>";
echo "</ol>";

echo "<p><a href='simple_debug.php'>デバッグ情報に戻る</a></p>";

echo "</body></html>";
?>
