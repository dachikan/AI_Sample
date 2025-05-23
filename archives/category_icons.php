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
$categories = [];
$editCategory = null;
$conn = null;

// アイコン一覧
$icons = [
    'document' => '📄',
    'image' => '🖼️',
    'accounting' => '💹',
    'event' => '🎪',
    'info' => '📢',
    'disaster' => '🚨',
    'community' => '👥',
    'other' => '📌'
];

// CSRFトークンの生成と検証
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

try {
    // データベース接続
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // icon_keyカラムの追加（存在しない場合）
    $sql = "SHOW COLUMNS FROM AISampleCategories LIKE 'icon_key'";
    $result = $conn->query($sql);
    
    if ($result->rowCount() == 0) {
        $sql = "ALTER TABLE AISampleCategories ADD COLUMN icon_key VARCHAR(20) DEFAULT 'other'";
        $conn->exec($sql);
        $success = "icon_keyカラムを追加しました。";
        
        // デフォルトのアイコンを設定
        $defaultIcons = [
            '文書作成' => 'document',
            '画像生成' => 'image',
            '会計管理' => 'accounting',
            'イベント企画' => 'event',
            '情報発信' => 'info',
            '防災対策' => 'disaster',
            'コミュニティ活性化' => 'community',
            'その他' => 'other'
        ];
        
        $stmt = $conn->prepare("UPDATE AISampleCategories SET icon_key = :icon_key WHERE name = :name");
        
        foreach ($defaultIcons as $name => $icon) {
            $stmt->bindParam(':icon_key', $icon);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
        }
    }
    
    // アイコンの更新処理
    if (isset($_POST['update_icons']) && isset($_POST['icons']) && isset($_POST['csrf_token'])) {
        // CSRFトークンの検証
        if ($_POST['csrf_token'] !== $csrf_token) {
            throw new Exception("セキュリティトークンが無効です。ページを再読み込みしてください。");
        }
        
        $iconUpdates = $_POST['icons'];
        
        $stmt = $conn->prepare("UPDATE AISampleCategories SET icon_key = :icon_key WHERE id = :id");
        
        foreach ($iconUpdates as $id => $icon_key) {
            $stmt->bindParam(':icon_key', $icon_key);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        }
        
        $success = "カテゴリのアイコンを更新しました。";
    }
    
    // カテゴリ一覧の取得
    $stmt = $conn->query("SELECT c.*, COUNT(s.id) as sample_count 
                         FROM AISampleCategories c 
                         LEFT JOIN AISampleInfo s ON c.id = s.category_id 
                         GROUP BY c.id 
                         ORDER BY c.display_order, c.name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "データベースエラー: " . $e->getMessage();
} catch(Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カテゴリアイコン設定</title>
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
            padding: 8px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-success {
            background-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .icon-select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .icon-preview {
            font-size: 24px;
            margin-right: 10px;
            vertical-align: middle;
        }
        .instructions {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        .icon-item {
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        .icon-item:hover {
            background-color: #f8f9fa;
        }
        .icon-item.selected {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .icon-emoji {
            font-size: 24px;
            display: block;
            margin-bottom: 5px;
        }
        .icon-name {
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <h1>カテゴリアイコン設定</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="container">
        <h2>カテゴリのアイコン設定</h2>
        
        <div class="instructions">
            <p><strong>使い方:</strong></p>
            <ol>
                <li>各カテゴリに適したアイコンを選択してください。</li>
                <li>アイコンはカテゴリ一覧やサンプル表示時に使用されます。</li>
                <li>設定が完了したら「保存」ボタンをクリックしてください。</li>
            </ol>
        </div>
        
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <table>
                <thead>
                    <tr>
                        <th>カテゴリ名</th>
                        <th>現在のアイコン</th>
                        <th>アイコン選択</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <?php 
                        $currentIcon = isset($category['icon_key']) && isset($icons[$category['icon_key']]) 
                            ? $icons[$category['icon_key']] 
                            : $icons['other'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><span class="icon-preview"><?php echo $currentIcon; ?></span></td>
                            <td>
                                <select name="icons[<?php echo $category['id']; ?>]" class="icon-select">
                                    <?php foreach ($icons as $key => $emoji): ?>
                                        <option value="<?php echo $key; ?>" <?php echo (isset($category['icon_key']) && $category['icon_key'] == $key) ? 'selected' : ''; ?>>
                                            <?php echo $emoji . ' ' . ucfirst($key); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <button type="submit" name="update_icons" class="btn btn-success">保存</button>
            <a href="category_manager.php" class="btn">カテゴリ管理に戻る</a>
        </form>
    </div>
    
    <div class="container">
        <h2>利用可能なアイコン一覧</h2>
        
        <div class="icon-grid">
            <?php foreach ($icons as $key => $emoji): ?>
                <div class="icon-item">
                    <span class="icon-emoji"><?php echo $emoji; ?></span>
                    <span class="icon-name"><?php echo ucfirst($key); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>