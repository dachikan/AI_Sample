<?php
/**
 * エラーハンドリングを改善するためのヘルパー関数
 */

// カスタムエラーハンドラー
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $errorType = [
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Standards',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
    ];

    $type = isset($errorType[$errno]) ? $errorType[$errno] : 'Unknown Error';
    
    // エラーをログに記録
    error_log("[$type] $errstr in $errfile on line $errline");
    
    // 開発環境では詳細なエラーを表示
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 4px;'>";
        echo "<h4>$type</h4>";
        echo "<p>$errstr</p>";
        echo "<p>File: $errfile</p>";
        echo "<p>Line: $errline</p>";
        echo "</div>";
    } else {
        // 本番環境ではユーザーフレンドリーなメッセージを表示
        if ($errno == E_USER_ERROR) {
            echo "<div class='alert alert-danger'>申し訳ありませんが、エラーが発生しました。後でもう一度お試しください。</div>";
        }
    }
    
    // E_USER_ERROR の場合は処理を停止
    if ($errno == E_USER_ERROR) {
        exit(1);
    }
    
    // PHPの内部エラーハンドラに処理を渡さないようにする
    return true;
}

// 例外ハンドラー
function customExceptionHandler($exception) {
    // エラーをログに記録
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    // 開発環境では詳細な例外情報を表示
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 4px;'>";
        echo "<h4>Uncaught Exception</h4>";
        echo "<p>" . $exception->getMessage() . "</p>";
        echo "<p>File: " . $exception->getFile() . "</p>";
        echo "<p>Line: " . $exception->getLine() . "</p>";
        echo "<h5>Stack Trace:</h5>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        // 本番環境ではユーザーフレンドリーなメッセージを表示
        echo "<div class='alert alert-danger'>申し訳ありませんが、エラーが発生しました。後でもう一度お試しください。</div>";
    }
    
    exit(1);
}

// 開発モードの設定（本番環境ではfalseに変更）
define('DEVELOPMENT_MODE', true);

// カスタムエラーハンドラーを設定
set_error_handler('customErrorHandler');

// カスタム例外ハンドラーを設定
set_exception_handler('customExceptionHandler');

// エラー表示設定
if (DEVELOPMENT_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

/**
 * データベース接続をテストする関数
 */
function testDatabaseConnection() {
    global $conn;
    
    if (!$conn) {
        return false;
    }
    
    try {
        $result = $conn->query("SELECT 1");
        return ($result && $result->fetch_row());
    } catch (Exception $e) {
        return false;
    }
}

/**
 * 安全にJSONでデータを出力する関数
 */
function debugJson($data) {
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 直接実行された場合のテスト
if (basename($_SERVER['PHP_SELF']) == 'improved_error_handling.php') {
    echo "<h2>🔧 エラーハンドリング設定テスト</h2>";
    echo "<p>✅ エラーハンドリングが正常に設定されました</p>";
    echo "<p>開発モード: " . (DEVELOPMENT_MODE ? "有効" : "無効") . "</p>";
    echo "<p>エラー表示: " . (ini_get('display_errors') ? "有効" : "無効") . "</p>";
    
    // データベース接続テスト
    if (file_exists('db_connection.php')) {
        include 'db_connection.php';
        $dbTest = testDatabaseConnection();
        echo "<p>データベース接続: " . ($dbTest ? "✅ 成功" : "❌ 失敗") . "</p>";
    }
    
    echo "<hr>";
    echo "<h3>🧪 テスト用エラー</h3>";
    echo "<button onclick='testWarning()'>Warning テスト</button> ";
    echo "<button onclick='testNotice()'>Notice テスト</button>";
    
    echo "<script>
    function testWarning() {
        fetch('improved_error_handling.php?test=warning');
    }
    function testNotice() {
        fetch('improved_error_handling.php?test=notice');
    }
    </script>";
    
    // テスト用エラーの生成
    if (isset($_GET['test'])) {
        if ($_GET['test'] == 'warning') {
            trigger_error("これはテスト用のWarningです", E_USER_WARNING);
        } elseif ($_GET['test'] == 'notice') {
            trigger_error("これはテスト用のNoticeです", E_USER_NOTICE);
        }
    }
}
?>
