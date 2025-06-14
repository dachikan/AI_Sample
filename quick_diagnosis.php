<?php
/**
 * 簡単な診断スクリプト
 */
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 システム診断</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .warning { background-color: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <h1>🔍 システム診断</h1>
    
    <?php
    // PHP基本情報
    echo "<div class='status success'>";
    echo "<h3>✅ PHP基本情報</h3>";
    echo "<p>PHPバージョン: " . PHP_VERSION . "</p>";
    echo "<p>メモリ制限: " . ini_get('memory_limit') . "</p>";
    echo "<p>実行時間制限: " . ini_get('max_execution_time') . "秒</p>";
    echo "</div>";
    
    // ファイル存在確認
    $files = [
        'AI_index.php' => 'メインページ',
        'AI_list.php' => 'リストページ', 
        'AI_ranking.php' => 'ランキングページ',
        'db_connection.php' => 'データベース接続',
        'improved_error_handling.php' => 'エラーハンドリング'
    ];
    
    echo "<div class='status'>";
    echo "<h3>📁 ファイル存在確認</h3>";
    foreach ($files as $file => $description) {
        $exists = file_exists($file);
        $class = $exists ? 'success' : 'error';
        $icon = $exists ? '✅' : '❌';
        echo "<p class='$class'>$icon $description ($file): " . ($exists ? '存在' : '不存在') . "</p>";
    }
    echo "</div>";
    
    // データベース接続テスト
    echo "<div class='status'>";
    echo "<h3>🗄️ データベース接続テスト</h3>";
    if (file_exists('db_connection.php')) {
        try {
            include 'db_connection.php';
            if (isset($conn) && $conn) {
                echo "<p class='success'>✅ データベース接続成功</p>";
                
                // テーブル確認
                $tables = ['AIInfo', 'ai_tools'];
                foreach ($tables as $table) {
                    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
                    if ($result) {
                        $row = $result->fetch_assoc();
                        echo "<p class='success'>✅ $table テーブル: {$row['count']}件</p>";
                    } else {
                        echo "<p class='error'>❌ $table テーブル: アクセスエラー</p>";
                    }
                }
            } else {
                echo "<p class='error'>❌ データベース接続失敗</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ データベースエラー: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='warning'>⚠️ db_connection.php が見つかりません</p>";
    }
    echo "</div>";
    
    // 推奨アクション
    echo "<div class='status warning'>";
    echo "<h3>🎯 推奨アクション</h3>";
    echo "<ol>";
    echo "<li><a href='AI_index.php'>AI_index.php</a> をテスト</li>";
    echo "<li><a href='AI_list.php'>AI_list.php</a> をテスト</li>";
    echo "<li><a href='AI_ranking.php'>AI_ranking.php</a> をテスト</li>";
    echo "<li><a href='improved_error_handling.php'>エラーハンドリング</a> をテスト</li>";
    echo "</ol>";
    echo "</div>";
    ?>
</body>
</html>
