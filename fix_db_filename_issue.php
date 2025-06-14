<?php
/**
 * データベースファイル名の問題を修正
 */
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 データベースファイル名修正</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .warning { background-color: #fff3cd; color: #856404; }
        .info { background-color: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <h1>🔧 データベースファイル名修正</h1>
    
    <?php
    // ファイル存在確認
    $db_connect_exists = file_exists('db_connect.php');
    $db_connection_exists = file_exists('db_connection.php');
    
    echo "<div class='status info'>";
    echo "<h3>📁 ファイル存在確認</h3>";
    echo "<p>db_connect.php: " . ($db_connect_exists ? "✅ 存在" : "❌ 不存在") . "</p>";
    echo "<p>db_connection.php: " . ($db_connection_exists ? "✅ 存在" : "❌ 不存在") . "</p>";
    echo "</div>";
    
    if ($db_connect_exists) {
        echo "<div class='status success'>";
        echo "<h3>✅ db_connect.php が見つかりました</h3>";
        
        // db_connect.php の内容をテスト
        try {
            include 'db_connect.php';
            if (isset($conn) && $conn) {
                echo "<p>✅ データベース接続成功</p>";
                
                // テーブル確認
                $tables = ['AIInfo', 'ai_tools'];
                foreach ($tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($result && $result->num_rows > 0) {
                        $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
                        $count = $count_result->fetch_assoc()['count'];
                        echo "<p>✅ $table テーブル: $count 件</p>";
                    } else {
                        echo "<p>⚠️ $table テーブル: 存在しません</p>";
                    }
                }
            } else {
                echo "<p>❌ データベース接続変数が設定されていません</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ 接続エラー: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
        
        // 他のファイルでの使用状況を確認
        echo "<div class='status warning'>";
        echo "<h3>🔍 ファイル使用状況確認</h3>";
        
        $files_to_check = ['AI_index.php', 'AI_list.php', 'AI_ranking.php', 'list.php'];
        $files_using_wrong_name = [];
        
        foreach ($files_to_check as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (strpos($content, 'db_connection.php') !== false) {
                    $files_using_wrong_name[] = $file;
                    echo "<p>⚠️ $file は db_connection.php を参照しています</p>";
                } elseif (strpos($content, 'db_connect.php') !== false) {
                    echo "<p>✅ $file は正しく db_connect.php を参照しています</p>";
                } else {
                    echo "<p>❓ $file はデータベース接続ファイルを参照していません</p>";
                }
            }
        }
        echo "</div>";
        
        // 修正が必要なファイルがある場合
        if (!empty($files_using_wrong_name)) {
            echo "<div class='status error'>";
            echo "<h3>🛠️ 修正が必要なファイル</h3>";
            echo "<p>以下のファイルで db_connection.php を db_connect.php に変更する必要があります：</p>";
            echo "<ul>";
            foreach ($files_using_wrong_name as $file) {
                echo "<li>$file</li>";
            }
            echo "</ul>";
            
            if (isset($_POST['fix_files'])) {
                $fixed_files = [];
                foreach ($files_using_wrong_name as $file) {
                    $content = file_get_contents($file);
                    $new_content = str_replace('db_connection.php', 'db_connect.php', $content);
                    if (file_put_contents($file, $new_content)) {
                        $fixed_files[] = $file;
                    }
                }
                
                if (!empty($fixed_files)) {
                    echo "<div class='status success'>";
                    echo "<h4>✅ 修正完了</h4>";
                    echo "<p>以下のファイルを修正しました：</p>";
                    echo "<ul>";
                    foreach ($fixed_files as $file) {
                        echo "<li>$file</li>";
                    }
                    echo "</ul>";
                    echo "</div>";
                }
            } else {
                echo "<form method='POST'>";
                echo "<button type='submit' name='fix_files' style='background-color: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>自動修正を実行</button>";
                echo "</form>";
            }
            echo "</div>";
        } else {
            echo "<div class='status success'>";
            echo "<h3>✅ すべてのファイルが正しい名前を使用しています</h3>";
            echo "</div>";
        }
        
    } else {
        echo "<div class='status error'>";
        echo "<h3>❌ db_connect.php が見つかりません</h3>";
        echo "<p>データベース接続ファイルを作成する必要があります。</p>";
        echo "</div>";
    }
    
    // 診断スクリプトの更新
    if (file_exists('quick_diagnosis.php')) {
        echo "<div class='status info'>";
        echo "<h3>🔄 診断スクリプト更新</h3>";
        
        if (isset($_POST['update_diagnosis'])) {
            $diagnosis_content = file_get_contents('quick_diagnosis.php');
            $updated_content = str_replace('db_connection.php', 'db_connect.php', $diagnosis_content);
            if (file_put_contents('quick_diagnosis.php', $updated_content)) {
                echo "<p>✅ quick_diagnosis.php を更新しました</p>";
            } else {
                echo "<p>❌ quick_diagnosis.php の更新に失敗しました</p>";
            }
        } else {
            echo "<p>診断スクリプトも正しいファイル名を使用するように更新できます。</p>";
            echo "<form method='POST'>";
            echo "<button type='submit' name='update_diagnosis' style='background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>診断スクリプトを更新</button>";
            echo "</form>";
        }
        echo "</div>";
    }
    ?>
    
    <hr>
    <h3>🎯 次のステップ</h3>
    <ol>
        <li><a href="quick_diagnosis.php">更新された診断スクリプトを実行</a></li>
        <li><a href="AI_index.php">メインページをテスト</a></li>
        <li><a href="AI_list.php">リストページをテスト</a></li>
        <li><a href="AI_ranking.php">ランキングページをテスト</a></li>
    </ol>
</body>
</html>
