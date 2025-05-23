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
$sample = null;
$categories = [];

// サンプルIDの取得
$sampleId = isset($_GET['id']) ? intval($_GET['id']) : null;

// フォーム送信の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    try {
        // データベース接続
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 入力値の取得
        $title = $_POST['title'] ?? '';
        $aiName = $_POST['aiName'] ?? '';
        $prompt = $_POST['prompt'] ?? '';
        $product = $_POST['product'] ?? '';
        $description = $_POST['description'] ?? '';
        $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $isAdvanced = isset($_POST['is_advanced']) ? 1 : 0;
        $needsModification = isset($_POST['needs_modification']) ? 1 : 0;
        
        // 入力チェック
        if (empty($title) || empty($aiName) || empty($prompt)) {
            throw new Exception("タイトル、AI名、プロンプトは必須項目です。");
        }
        
        // サンプル情報の更新
        $stmt = $conn->prepare("UPDATE AISampleInfo SET 
                               Title = :title,
                               AiName = :aiName,
                               Prompt = :prompt,
                               Product = :product,
                               HowToUse = :description,
                               category_id = :categoryId,
                               is_advanced = :isAdvanced,
                               needs_modification = :needsModification
                               WHERE id = :id");
        // サンプル情報の更新部分（約60行目付近）を以下のように修正
// 入力チェック後、UPDATE文の前に以下を追加

// デバッグ情報の表示
// echo "<div style='background-color: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;'>";
// echo "<h3>デバッグ情報</h3>";
// echo "<p><strong>サンプルID:</strong> " . $sampleId . "</p>";
// echo "<p><strong>タイトル:</strong> " . htmlspecialchars($title) . "</p>";
// echo "<p><strong>AI名:</strong> " . htmlspecialchars($aiName) . "</p>";
// echo "<p><strong>カテゴリID:</strong> " . ($categoryId ? $categoryId : "未設定") . "</p>";
// echo "<p><strong>上級者向け:</strong> " . ($isAdvanced ? "はい" : "いいえ") . "</p>";
// echo "<p><strong>中級者向け:</strong> " . ($needsModification ? "はい" : "いいえ") . "</p>";

// // 実行されるSQLクエリを表示
// $sql = "UPDATE AISampleInfo SET 
//        Title = '$title',
//        AiName = '$aiName',
//        Prompt = '[プロンプト内容]',
//        Product = '[生成物内容]',
//        Description = '[説明内容]',
//        category_id = " . ($categoryId ? $categoryId : "NULL") . ",
//        is_advanced = $isAdvanced,
//        needs_modification = $needsModification
//        WHERE id = $sampleId";

// echo "<p><strong>実行されるSQL:</strong></p>";
// echo "<pre>" . htmlspecialchars($sql) . "</pre>";
// echo "</div>";

// 実際のUPDATE文はそのまま実行
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':aiName', $aiName);
        $stmt->bindParam(':prompt', $prompt);
        $stmt->bindParam(':product', $product);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':categoryId', $categoryId);
        $stmt->bindParam(':isAdvanced', $isAdvanced);
        $stmt->bindParam(':needsModification', $needsModification);
        $stmt->bindParam(':id', $sampleId);
        
        $stmt->execute();
        
        $success = "サンプル情報を更新しました。";
        
        // 更新後のサンプル情報を再取得
        $stmt = $conn->prepare("SELECT * FROM AISampleInfo WHERE id = :id");
        $stmt->bindParam(':id', $sampleId);
        $stmt->execute();
        $sample = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        $error = "データベースエラー: " . $e->getMessage();
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
} else {
    try {
        // データベース接続
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // サンプル情報の取得
        if ($sampleId) {
            $stmt = $conn->prepare("SELECT * FROM AISampleInfo WHERE id = :id");
            $stmt->bindParam(':id', $sampleId);
            $stmt->execute();
            $sample = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sample) {
                $error = "指定されたサンプルが見つかりません。";
            }
        } else {
            $error = "サンプルIDが指定されていません。";
        }
        
        // カテゴリ一覧の取得
        $stmt = $conn->query("SELECT * FROM AISampleCategories ORDER BY display_order, name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        $error = "データベースエラー: " . $e->getMessage();
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// サンプルの削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    try {
        // データベース接続
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // サンプルの削除
        $stmt = $conn->prepare("DELETE FROM AISampleInfo WHERE id = :id");
        $stmt->bindParam(':id', $sampleId);
        $stmt->execute();
        
        // 削除後はリストページにリダイレクト
        header("Location: AISampleList_with_advanced.php?deleted=1");
        exit;
        
    } catch(PDOException $e) {
        $error = "データベースエラー: " . $e->getMessage();
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIサンプル編集</title>
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #6c757d;
            --secondary-dark: #5a6268;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-color: #dee2e6;
            --text-color: #333;
            --text-muted: #6c757d;
            --bg-color: #f9f9f9;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: var(--bg-color);
        }
        
        h1, h2, h3, h4 {
            color: var(--dark-color);
            margin-top: 0;
        }
        
        a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .error, .success {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .checkbox-group {
            margin-top: 10px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            font-weight: normal;
            cursor: pointer;
        }
        
        .checkbox-label input[type="checkbox"] {
            margin-right: 10px;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--secondary-color);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            margin-right: 10px;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn:hover {
            background-color: var(--secondary-dark);
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-success {
            background-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header-title h1 {
            margin: 0;
            font-size: 28px;
        }
        
        .header-description {
            color: var(--text-muted);
            margin-top: 5px;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--text-muted);
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        /* モバイル対応 */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                margin-top: 15px;
                flex-wrap: wrap;
            }
            
            .btn {
                margin-bottom: 10px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">
            <h1>AIサンプル編集</h1>
            <div class="header-description">既存のAIサンプル情報を編集します</div>
        </div>
        <div class="header-actions">
            <a href="AISample_main.php" class="btn">メイン画面に戻る</a>
            <a href="AISampleList_with_advanced.php" class="btn">サンプル一覧に戻る</a>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($sample): ?>
        <div class="container">
            <form method="post" action="">
                <div class="form-group">
                    <label for="title">タイトル <span style="color: red;">*</span></label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($sample['Title'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="aiName">AI名 <span style="color: red;">*</span></label>
                    <input type="text" id="aiName" name="aiName" value="<?php echo htmlspecialchars($sample['AiName'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">プロンプトカテゴリ</label>
                    <select id="category_id" name="category_id">
                        <option value="">-- カテゴリを選択 --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($sample['category_id']) && $sample['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="prompt">プロンプト <span style="color: red;">*</span></label>
                    <textarea id="prompt" name="prompt" required><?php echo htmlspecialchars($sample['Prompt'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="product">生成される物</label>
                    <textarea id="product" name="product"><?php echo htmlspecialchars($sample['Product'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="description">説明</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($sample['Description'] ?? ''); ?></textarea>
                </div>
                
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_advanced" <?php echo (isset($sample['is_advanced']) && $sample['is_advanced'] == 1) ? 'checked' : ''; ?>>
                        上級者向けサンプル
                    </label>
                </div>
                
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="needs_modification" <?php echo (isset($sample['needs_modification']) && $sample['needs_modification'] == 1) ? 'checked' : ''; ?>>
                        中級者向けサンプル（修正が必要）
                    </label>
                </div>
                
                <div class="form-actions">
                    <div>
                        <button type="submit" name="update" class="btn btn-primary">更新する</button>
                        <a href="AISample_main.php?id=<?php echo $sampleId; ?>" class="btn">キャンセル</a>
                    </div>
                    <div>
                        <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('本当にこのサンプルを削除しますか？この操作は元に戻せません。');">
                            このサンプルを削除
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> 初心者向けＡＩ活用サンプル集</p>
    </div>
</body>
</html>
