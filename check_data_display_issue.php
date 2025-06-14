<?php
/**
 * データ表示件数の問題を調査するスクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>データ表示件数調査</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }";
echo ".section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".error { color: #dc3545; font-weight: bold; }";
echo ".warning { color: #ffc107; font-weight: bold; }";
echo ".info { color: #17a2b8; font-weight: bold; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background-color: #f8f9fa; }";
echo ".btn { display: inline-block; padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; background-color: #007bff; border: none; cursor: pointer; }";
echo ".btn-success { background-color: #28a745; }";
echo ".btn-warning { background-color: #ffc107; color: #000; }";
echo ".btn-danger { background-color: #dc3545; }";
echo "pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; max-height: 300px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 データ表示件数調査</h1>";
echo "<p>実行時刻: " . date('Y-m-d H:i:s') . "</p>";

// データベース接続
include "db_connect.php";

// 1. データベース内の総件数確認
echo "<div class='section'>";
echo "<h2>📊 データベース内の総件数確認</h2>";

if (isset($conn) && $conn) {
    // ai_toolsテーブルの総件数
    $total_sql = "SELECT COUNT(*) as total FROM ai_tools";
    $total_result = $conn->query($total_sql);
    
    if ($total_result) {
        $total_count = $total_result->fetch_assoc()['total'];
        echo "<p class='success'>✓ ai_toolsテーブル総件数: <strong>$total_count</strong> 件</p>";
        
        // 詳細な内訳
        $breakdown_queries = [
            '有効なサービス' => "SELECT COUNT(*) as count FROM ai_tools WHERE ai_service IS NOT NULL AND ai_service != ''",
            '説明があるサービス' => "SELECT COUNT(*) as count FROM ai_tools WHERE description IS NOT NULL AND description != ''",
            '無料サービス' => "SELECT COUNT(*) as count FROM ai_tools WHERE is_free = 1",
            'おすすめサービス' => "SELECT COUNT(*) as count FROM ai_tools WHERE is_featured = 1",
            '評価があるサービス' => "SELECT COUNT(*) as count FROM ai_tools WHERE rating > 0"
        ];
        
        echo "<table>";
        echo "<tr><th>カテゴリ</th><th>件数</th></tr>";
        
        foreach ($breakdown_queries as $label => $query) {
            $result = $conn->query($query);
            if ($result) {
                $count = $result->fetch_assoc()['count'];
                echo "<tr><td>$label</td><td>$count</td></tr>";
            }
        }
        echo "</table>";
        
    } else {
        echo "<p class='error'>✗ データベースクエリに失敗しました: " . $conn->error . "</p>";
    }
} else {
    echo "<p class='error'>✗ データベース接続に失敗しました</p>";
}
echo "</div>";

// 2. AI_list.phpの内容確認
echo "<div class='section'>";
echo "<h2>📄 AI_list.phpのクエリ確認</h2>";

$ai_list_file = 'AI_list.php';
if (file_exists($ai_list_file)) {
    $content = file_get_contents($ai_list_file);
    
    // LIMIT句の検索
    $limit_matches = [];
    if (preg_match_all('/LIMIT\s+\d+/i', $content, $limit_matches)) {
        echo "<p class='warning'>⚠ LIMIT句が見つかりました:</p>";
        echo "<ul>";
        foreach ($limit_matches[0] as $limit) {
            echo "<li><code>$limit</code></li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='info'>ℹ LIMIT句は見つかりませんでした</p>";
    }
    
    // SQLクエリの抽出
    $sql_pattern = '/\$sql\s*=\s*["\']([^"\']+)["\'];?/';
    if (preg_match($sql_pattern, $content, $sql_matches)) {
        echo "<h3>メインSQLクエリ:</h3>";
        echo "<pre>" . htmlspecialchars($sql_matches[1]) . "</pre>";
    }
    
    // ページネーション関連の検索
    $pagination_keywords = ['page', 'offset', 'limit', 'per_page'];
    $found_pagination = [];
    foreach ($pagination_keywords as $keyword) {
        if (stripos($content, $keyword) !== false) {
            $found_pagination[] = $keyword;
        }
    }
    
    if (!empty($found_pagination)) {
        echo "<p class='warning'>⚠ ページネーション関連のキーワードが見つかりました: " . implode(', ', $found_pagination) . "</p>";
    } else {
        echo "<p class='success'>✓ ページネーション関連のキーワードは見つかりませんでした</p>";
    }
    
} else {
    echo "<p class='error'>✗ AI_list.phpが見つかりません</p>";
}
echo "</div>";

// 3. 実際のクエリテスト
echo "<div class='section'>";
echo "<h2>🧪 実際のクエリテスト</h2>";

if (isset($conn) && $conn) {
    // 基本クエリ
    $test_sql = "SELECT * FROM ai_tools ORDER BY ai_service ASC";
    $test_result = $conn->query($test_sql);
    
    if ($test_result) {
        $actual_count = $test_result->num_rows;
        echo "<p class='success'>✓ 基本クエリ結果: <strong>$actual_count</strong> 件取得</p>";
        
        if ($actual_count > 0) {
            echo "<h3>取得されたサービス一覧（最初の10件）:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>サービス名</th><th>説明</th><th>作成日</th></tr>";
            
            $count = 0;
            while ($row = $test_result->fetch_assoc() && $count < 10) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['ai_service']) . "</td>";
                echo "<td>" . htmlspecialchars(substr($row['description'], 0, 50)) . "...</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
                $count++;
            }
            echo "</table>";
            
            if ($actual_count > 10) {
                echo "<p class='info'>... 他 " . ($actual_count - 10) . " 件</p>";
            }
        }
    } else {
        echo "<p class='error'>✗ テストクエリに失敗しました: " . $conn->error . "</p>";
    }
}
echo "</div>";

// 4. 修正提案
echo "<div class='section'>";
echo "<h2>🔧 修正提案</h2>";

echo "<p>5件しか表示されない原因として考えられるもの:</p>";
echo "<ol>";
echo "<li><strong>LIMIT句の設定</strong> - SQLクエリにLIMIT 5が設定されている</li>";
echo "<li><strong>ページネーションの残存</strong> - 古いページネーション機能が残っている</li>";
echo "<li><strong>データベースの問題</strong> - 実際のデータが5件しかない</li>";
echo "<li><strong>条件フィルタ</strong> - 何らかの条件でデータが絞り込まれている</li>";
echo "</ol>";

echo "<p>修正方法:</p>";
echo "<ul>";
echo "<li>AI_list.phpからLIMIT句を削除</li>";
echo "<li>ページネーション関連のコードを完全に削除</li>";
echo "<li>全件表示するように修正</li>";
echo "</ul>";

echo "<form method='post'>";
echo "<button type='submit' name='fix_display_limit' class='btn btn-success'>表示件数制限を修正</button>";
echo "</form>";
echo "</div>";

// 5. 修正処理
if (isset($_POST['fix_display_limit'])) {
    echo "<div class='section'>";
    echo "<h2>🔄 表示件数制限修正</h2>";
    
    if (file_exists($ai_list_file)) {
        // バックアップを作成
        $backup_file = $ai_list_file . '.backup.' . date('Ymd_His');
        copy($ai_list_file, $backup_file);
        echo "<p class='success'>✓ バックアップを作成しました: $backup_file</p>";
        
        $content = file_get_contents($ai_list_file);
        
        // LIMIT句を削除
        $content = preg_replace('/\s+LIMIT\s+\d+/i', '', $content);
        
        // ページネーション関連のコードを削除
        $content = preg_replace('/\$page\s*=.*?;/', '', $content);
        $content = preg_replace('/\$per_page\s*=.*?;/', '', $content);
        $content = preg_replace('/\$offset\s*=.*?;/', '', $content);
        
        // ページネーションHTMLを削除
        $content = preg_replace('/<nav\s+aria-label=["\']pagination["\'].*?<\/nav>/is', '', $content);
        $content = preg_replace('/<ul\s+class=["\']pagination.*?<\/ul>/is', '', $content);
        
        if (file_put_contents($ai_list_file, $content)) {
            echo "<p class='success'>✓ AI_list.phpを修正しました</p>";
            echo "<p><a href='AI_list.php' target='_blank' class='btn btn-success'>修正されたページを確認</a></p>";
        } else {
            echo "<p class='error'>✗ ファイルの更新に失敗しました</p>";
        }
    }
    echo "</div>";
}

echo "</body></html>";
?>
