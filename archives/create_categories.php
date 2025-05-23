<?php
// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース接続情報
$servername = "mysql213.phy.lolipop.lan";
$username = "LAA1337491";
$password = "kami2004";
$dbname = "LAA1337491-nsk";

// 初期化
$error = "";
$success = "";

// カテゴリ一覧
$categories = [
    ['name' => '文書作成', 'description' => '議事録、報告書、お知らせなどの文書作成に関するサンプル'],
    ['name' => '画像生成', 'description' => 'ポスター、チラシ、イラストなどの画像生成に関するサンプル'],
    ['name' => '会計管理', 'description' => '会費管理、予算作成、経費精算などの会計業務に関するサンプル'],
    ['name' => 'イベント企画', 'description' => '祭り、清掃活動、防災訓練などのイベント企画に関するサンプル'],
    ['name' => '情報発信', 'description' => 'ウェブサイト、SNS、メールなどでの情報発信に関するサンプル'],
    ['name' => '防災対策', 'description' => '防災マニュアル、避難計画、安全対策に関するサンプル'],
    ['name' => 'コミュニティ活性化', 'description' => '住民交流、参加促進、コミュニティ形成に関するサンプル'],
    ['name' => 'その他', 'description' => 'その他の目的に関するサンプル']
];

try {
    // データベース接続
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // トランザクション開始
    $conn->beginTransaction();
    
    // カテゴリテーブルの作成
    $sql = "CREATE TABLE IF NOT EXISTS AISampleCategories (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql);
    
    // AISampleInfoテーブルにcategory_idカラムを追加
    $sql = "SHOW COLUMNS FROM AISampleInfo LIKE 'category_id'";
    $result = $conn->query($sql);
    
    if ($result->rowCount() == 0) {
        $sql = "ALTER TABLE AISampleInfo ADD COLUMN category_id INT(11) DEFAULT NULL";
        $conn->exec($sql);
        
        $sql = "ALTER TABLE AISampleInfo ADD CONSTRAINT fk_category_id FOREIGN KEY (category_id) REFERENCES AISampleCategories(id) ON DELETE SET NULL";
        $conn->exec($sql);
    }
    
    // カテゴリの追加
    $stmt = $conn->prepare("INSERT INTO AISampleCategories (name, description) VALUES (:name, :description)");
    
    foreach ($categories as $category) {
        // 既存のカテゴリをチェック
        $checkStmt = $conn->prepare("SELECT id FROM AISampleCategories WHERE name = :name");
        $checkStmt->bindParam(':name', $category['name']);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            $stmt->bindParam(':name', $category['name']);
            $stmt->bindParam(':description', $category['description']);
            $stmt->execute();
        }
    }
    
    // トランザクションをコミット
    $conn->commit();
    
    $success = "カテゴリテーブルの作成とカテゴリの追加が完了しました。";
    
} catch(PDOException $e) {
    // エラーが発生した場合はロールバック
    if (isset($conn)) {
        $conn->rollback();
    }
    $error = "エラー: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カテゴリテーブル作成</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <h1>カテゴリテーブル作成</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="container">
        <h2>作成したカテゴリ一覧</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>カテゴリ名</th>
                    <th>説明</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($conn)) {
                    $stmt = $conn->query("SELECT * FROM AISampleCategories ORDER BY id");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
        
        <a href="assign_categories.php" class="btn">既存サンプルにカテゴリを割り当てる</a>
        <a href="View.php" class="btn">老人向けＡＩ活用サンプル一覧に戻る</a>
    </div>
</body>
</html>