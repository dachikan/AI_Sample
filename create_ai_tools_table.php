<?php
/**
 * ai_toolsテーブルにサンプルデータを追加
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>AIツールデータ作成</title></head>";
echo "<body>";
echo "<h1>🗃️ AIツールデータベース作成</h1>";

// データベース接続
include "db_connect_extended.php";

// ai_toolsテーブルの存在確認
$table_exists = false;
$result = $conn->query("SHOW TABLES LIKE 'ai_tools'");
if ($result && $result->num_rows > 0) {
    $table_exists = true;
    echo "<p style='color:green'>✓ ai_toolsテーブルは既に存在します</p>";
} else {
    echo "<p style='color:orange'>⚠ ai_toolsテーブルが存在しません。作成します...</p>";
    
    // テーブル作成
    $create_table_sql = "CREATE TABLE `ai_tools` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ai_service` varchar(200) NOT NULL,
        `description` text,
        `category_id` int(11),
        `website_url` varchar(500),
        `logo_url` varchar(500),
        `rating` decimal(3,2) DEFAULT 0.00,
        `review_count` int(11) DEFAULT 0,
        `is_free` tinyint(1) DEFAULT 0,
        `is_featured` tinyint(1) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($create_table_sql) === TRUE) {
        $table_exists = true;
        echo "<p style='color:green'>✓ ai_toolsテーブルを作成しました</p>";
    } else {
        echo "<p style='color:red'>✗ テーブル作成エラー: " . $conn->error . "</p>";
    }
}

// サンプルデータの追加
if ($table_exists) {
    // 既存のデータ数を確認
    $count_result = $conn->query("SELECT COUNT(*) as count FROM ai_tools");
    $count = 0;
    if ($count_result && $row = $count_result->fetch_assoc()) {
        $count = $row['count'];
    }
    
    if ($count > 0) {
        echo "<p style='color:green'>✓ ai_toolsテーブルには既に $count 件のデータがあります</p>";
    } else {
        echo "<p style='color:orange'>⚠ ai_toolsテーブルにデータがありません。サンプルデータを追加します...</p>";
        
        // サンプルデータ
        $sample_data = [
            [
                'ai_service' => 'ChatGPT',
                'description' => 'OpenAI開発の対話型AI。自然な会話、コード生成、文章作成など多様なタスクに対応。GPT-4技術を使用し、プラグインにも対応。',
                'website_url' => 'https://chat.openai.com/',
                'logo_url' => 'images/chatgpt-icon.png',
                'rating' => 4.8,
                'review_count' => 1250,
                'is_free' => 1,
                'is_featured' => 1
            ],
            [
                'ai_service' => 'Claude',
                'description' => 'Anthropic開発のAI。長文処理に優れ、安全性を重視した設計。Constitutional AI技術を採用し、ファイルアップロードにも対応。',
                'website_url' => 'https://claude.ai/',
                'logo_url' => 'images/claude-icon.png',
                'rating' => 4.7,
                'review_count' => 980,
                'is_free' => 1,
                'is_featured' => 1
            ],
            [
                'ai_service' => 'Gemini',
                'description' => 'Google開発のAI。マルチモーダル対応で、テキスト、画像、音声を理解。Google検索と連携し、最新情報にアクセス可能。',
                'website_url' => 'https://gemini.google.com/',
                'logo_url' => 'images/gemini-icon.png',
                'rating' => 4.6,
                'review_count' => 850,
                'is_free' => 1,
                'is_featured' => 1
            ],
            [
                'ai_service' => 'Copilot',
                'description' => 'Microsoft開発のAI。Bing検索と統合され、Office製品との連携が強み。GPT-4技術をベースに、Microsoftのエコシステムと密接に連携。',
                'website_url' => 'https://copilot.microsoft.com/',
                'logo_url' => 'images/copilot-icon.png',
                'rating' => 4.5,
                'review_count' => 720,
                'is_free' => 1,
                'is_featured' => 0
            ],
            [
                'ai_service' => 'Perplexity',
                'description' => '検索特化型AI。質問に対して情報源を明示し、ファクトチェック機能も充実。学術論文検索や引用機能が特徴的。',
                'website_url' => 'https://www.perplexity.ai/',
                'logo_url' => 'images/perplexity-icon.png',
                'rating' => 4.4,
                'review_count' => 650,
                'is_free' => 1,
                'is_featured' => 0
            ]
        ];
        
        // データ挿入
        $success_count = 0;
        foreach ($sample_data as $data) {
            $sql = "INSERT INTO ai_tools (ai_service, description, website_url, logo_url, rating, review_count, is_free, is_featured) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssdiis", 
                $data['ai_service'], 
                $data['description'], 
                $data['website_url'], 
                $data['logo_url'], 
                $data['rating'], 
                $data['review_count'], 
                $data['is_free'], 
                $data['is_featured']
            );
            
            if ($stmt->execute()) {
                $success_count++;
            }
            $stmt->close();
        }
        
        echo "<p style='color:green'>✓ $success_count 件のサンプルデータを追加しました</p>";
    }
    
    // テーブルの内容を表示
    $data_result = $conn->query("SELECT * FROM ai_tools");
    if ($data_result && $data_result->num_rows > 0) {
        echo "<h3>ai_toolsテーブルの内容:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>AIサービス</th><th>説明</th><th>評価</th><th>レビュー数</th><th>無料</th><th>おすすめ</th></tr>";
        
        while ($row = $data_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['ai_service'] . "</td>";
            echo "<td>" . substr($row['description'], 0, 50) . "...</td>";
            echo "<td>" . $row['rating'] . "</td>";
            echo "<td>" . $row['review_count'] . "</td>";
            echo "<td>" . ($row['is_free'] ? 'はい' : 'いいえ') . "</td>";
            echo "<td>" . ($row['is_featured'] ? 'はい' : 'いいえ') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
}

echo "<h2>🎯 次のステップ</h2>";
echo "<ol>";
echo "<li><a href='fix_index_icons.php'>一覧表示のアイコン修正</a></li>";
echo "<li><a href='index.php'>トップページを確認</a></li>";
echo "<li><a href='AI_comparison.php?ids[]=1&ids[]=2'>比較ページを確認</a></li>";
echo "</ol>";

echo "</body></html>";
?>
