<?php
/**
 * AI_ranking.phpを調査してAI_list.phpを修正
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>AI_list.php修正（AI_ranking.php基準）</title></head>";
echo "<body>";
echo "<h1>🔧 AI_list.php修正（AI_ranking.php基準）</h1>";

// データベース接続
include "db_connect.php";

// AI_ranking.phpがどのテーブルを使用しているかを確認
$ranking_table = null;
if (file_exists('AI_ranking.php')) {
    $ranking_content = file_get_contents('AI_ranking.php');
    echo "<p style='color:green'>✓ AI_ranking.phpが見つかりました</p>";
    
    // FROM句を検索
    if (preg_match('/FROM\s+([a-zA-Z_][a-zA-Z0-9_]*)/i', $ranking_content, $matches)) {
        $ranking_table = $matches[1];
        echo "<p style='color:green'>✓ AI_ranking.phpは <strong>$ranking_table</strong> テーブルを使用</p>";
    } else {
        // バッククォートで囲まれている場合も検索
        if (preg_match('/FROM\s+`([a-zA-Z_][a-zA-Z0-9_]*)`/i', $ranking_content, $matches)) {
            $ranking_table = $matches[1];
            echo "<p style='color:green'>✓ AI_ranking.phpは <strong>$ranking_table</strong> テーブルを使用</p>";
        }
    }
    
    // AIInfoテーブルが使用されているかも確認
    if (strpos($ranking_content, 'AIInfo') !== false) {
        $ranking_table = 'AIInfo';
        echo "<p style='color:green'>✓ AI_ranking.phpは <strong>AIInfo</strong> テーブルを使用していることを確認</p>";
    }
} else {
    echo "<p style='color:red'>✗ AI_ranking.phpが見つかりません</p>";
}

// AIInfoテーブルが45件あることを確認
if (isset($conn)) {
    $aiinfo_count = 0;
    $count_sql = "SELECT COUNT(*) as count FROM AIInfo";
    $count_result = $conn->query($count_sql);
    if ($count_result) {
        $aiinfo_count = $count_result->fetch_assoc()['count'];
        echo "<p>AIInfoテーブルのレコード数: <strong>$aiinfo_count</strong> 件</p>";
    }
    
    // AIInfoテーブルの構造を確認
    echo "<h2>📋 AIInfoテーブルの構造</h2>";
    $structure_sql = "DESCRIBE AIInfo";
    $structure_result = $conn->query($structure_sql);
    if ($structure_result) {
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>カラム名</th><th>データ型</th><th>NULL</th><th>キー</th></tr>";
        while ($row = $structure_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // AIInfoテーブルのサンプルデータを表示
    echo "<h2>📊 AIInfoテーブルのサンプルデータ（最初の5件）</h2>";
    $sample_sql = "SELECT * FROM AIInfo LIMIT 5";
    $sample_result = $conn->query($sample_sql);
    if ($sample_result) {
        echo "<table border='1' style='border-collapse:collapse;'>";
        $first_row = true;
        while ($row = $sample_result->fetch_assoc()) {
            if ($first_row) {
                echo "<tr>";
                foreach (array_keys($row) as $column) {
                    echo "<th>" . htmlspecialchars($column) . "</th>";
                }
                echo "</tr>";
                $first_row = false;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // AI_list.phpを修正
    if ($aiinfo_count >= 20) {
        echo "<h2>🔧 AI_list.php修正</h2>";
        
        $ai_list_file = 'AI_list.php';
        if (file_exists($ai_list_file)) {
            // バックアップを作成
            $backup_file = $ai_list_file . '.aiinfo_backup.' . date('Ymd_His');
            copy($ai_list_file, $backup_file);
            echo "<p style='color:green'>✓ バックアップを作成: $backup_file</p>";
            
            $content = file_get_contents($ai_list_file);
            
            // ai_toolsをAIInfoに置換
            $updated_content = str_replace('ai_tools', 'AIInfo', $content);
            
            // FROM `ai_tools` の場合も対応
            $updated_content = str_replace('FROM `ai_tools`', 'FROM `AIInfo`', $updated_content);
            $updated_content = str_replace('FROM ai_tools', 'FROM AIInfo', $updated_content);
            
            // ファイルを更新
            if (file_put_contents($ai_list_file, $updated_content)) {
                echo "<p style='color:green'>✓ AI_list.phpのテーブルを <strong>ai_tools</strong> から <strong>AIInfo</strong> に変更しました</p>";
                echo "<p style='color:green'>✓ これで45件のデータが表示されるはずです</p>";
            } else {
                echo "<p style='color:red'>✗ ファイルの更新に失敗しました</p>";
            }
        } else {
            echo "<p style='color:red'>✗ AI_list.phpが見つかりません</p>";
        }
    }
}

echo "<h2>🧪 テスト</h2>";
echo "<p><a href='AI_list.php' target='_blank' style='display:inline-block;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;'>修正されたAI_list.phpを確認</a></p>";
echo "<p><a href='AI_ranking.php' target='_blank' style='display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;'>AI_ranking.phpと比較</a></p>";

echo "<h2>📋 期待される結果</h2>";
echo "<ul>";
echo "<li>AI_list.phpで45件のAIサービスが表示される</li>";
echo "<li>AI_ranking.phpと同じデータソース（AIInfo）を使用</li>";
echo "<li>カード型レイアウトが維持される</li>";
echo "</ul>";

echo "</body></html>";
?>
