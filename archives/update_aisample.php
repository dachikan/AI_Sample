<?php
// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 初期化
$error = "";
$success = "";

// 修正後のView.phpのコード
$newCode = <<<'EOD'
<?php
// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// メモリ制限とタイムアウトの設定
ini_set('memory_limit', '256M');
set_time_limit(300);

// データベース接続情報
$servername = "mysql213.phy.lolipop.lan";
$username = "LAA1337491";
$password = "kami2004";
$dbname = "LAA1337491-nsk";

// 初期化
$error = "";
$success = "";
$formData = [];
$samples = [];
$mode = "list"; // デフォルトはリスト表示モード

// データベース接続
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // モードの判定
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        // 詳細表示モード
        $mode = "view";
        $sampleId = $_GET['id'];
        
        // データの取得
        $stmt = $conn->prepare("SELECT * FROM AISampleInfo WHERE id = :id");
        $stmt->bindParam(':id', $sampleId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $formData = $row;
            
            // HowToUseフィールドがない場合は空文字を設定
            if (!isset($formData['HowToUse'])) {
                $formData['HowToUse'] = '';
            }
            
            // 使用統計を記録 - エラーが発生しても処理を続行
            try {
                $stmt = $conn->prepare("INSERT INTO AIUsageStats (ai_name, sample_id, user_id, action_type) VALUES (:ai_name, :sample_id, :user_id, :action_type)");
                $stmt->bindParam(':ai_name', $formData['AiName'], PDO::PARAM_STR);
                $stmt->bindParam(':sample_id', $sampleId, PDO::PARAM_INT);
                $userId = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
                $actionType = 'view';
                $stmt->bindParam(':action_type', $actionType, PDO::PARAM_STR);
                $stmt->execute();
            } catch (PDOException $e) {
                // 使用統計の記録に失敗しても、ユーザー体験に影響しないようにエラーを無視
                // ただし、開発時にはエラーを確認できるようにコメントアウト
                // $error = "使用統計記録エラー: " . $e->getMessage();
            }
        } else {
            $error = "指定されたIDのデータが見つかりません。";
            $mode = "list";
        }
    } else {
        // リスト表示モード
        $mode = "list";
        
        // 検索条件
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $aiFilter = isset($_GET['ai']) ? $_GET['ai'] : '';
        
        // ページネーション
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // WHERE句の構築
        $whereClause = "";
        $params = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE (Title LIKE :search OR AiName LIKE :search OR Product LIKE :search OR Prompt LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($aiFilter)) {
            $whereClause = empty($whereClause) ? "WHERE AiName LIKE :ai" : "$whereClause AND AiName LIKE :ai";
            $params[':ai'] = "%$aiFilter%";
        }
        
        // 総件数の取得
        $countQuery = "SELECT COUNT(*) FROM AISampleInfo $whereClause";
        $stmt = $conn->prepare($countQuery);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $totalItems = $stmt->fetchColumn();
        $totalPages = ceil($totalItems / $perPage);
        
        // データの取得
        $query = "SELECT * FROM AISampleInfo $whereClause ORDER BY updated_at DESC LIMIT :offset, :limit";
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 成功メッセージの表示
    if (isset($_GET['success'])) {
        $success = $_GET['success'];
    }
    
} catch(PDOException $e) {
    $error = "データベース接続エラー: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php 
        if ($mode == "view" && !empty($formData)) {
            echo htmlspecialchars($formData['Title']) . " - ";
        }
        ?>
        AI活用サンプル
    </title>
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
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            margin-right: 10px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .sample-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .sample-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .sample-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .sample-card-header {
            padding: 15px;
            background-color: #3498db;
            color: white;
        }
        .sample-card-body {
            padding: 15px;
        }
        .sample-card-title {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .sample-card-ai {
            margin-top: 5px;
            font-size: 14px;
            opacity: 0.8;
        }
        .sample-card-product {
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
        .sample-card-footer {
            padding: 15px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sample-card-date {
            font-size: 12px;
            color: #666;
        }
        .sample-card-link {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }
        .sample-card-link:hover {
            text-decoration: underline;
        }
        
        /* 詳細表示モード用のスタイル */
        .detail-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .detail-header {
            margin-bottom: 20px;
        }
        .detail-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .detail-ai {
            font-size: 16px;
            color: #3498db;
            margin-bottom: 20px;
        }
        .detail-section {
            margin-bottom: 30px;
        }
        .detail-section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .detail-product {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .detail-prompt {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
            font-family: monospace;
            margin-bottom: 20px;
        }
        .detail-how-to-use {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <h1>AI活用サンプル</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($mode == "list"): ?>
        <!-- リスト表示モード -->
        <div class="container">
            <h2>AI活用サンプル一覧</h2>
            
            <?php if (empty($samples)): ?>
                <p>サンプルが見つかりませんでした。</p>
            <?php else: ?>
                <div class="sample-list">
                    <?php foreach ($samples as $sample): ?>
                        <div class="sample-card">
                            <div class="sample-card-header">
                                <h3 class="sample-card-title"><?php echo htmlspecialchars($sample['Title']); ?></h3>
                                <div class="sample-card-ai"><?php echo htmlspecialchars($sample['AiName']); ?></div>
                            </div>
                            <div class="sample-card-body">
                                <div class="sample-card-product"><?php echo htmlspecialchars($sample['Product']); ?></div>
                            </div>
                            <div class="sample-card-footer">
                                <div class="sample-card-date"><?php echo date('Y-m-d', strtotime($sample['updated_at'])); ?></div>
                                <a href="View.php?id=<?php echo $sample['id']; ?>" class="sample-card-link">詳細を見る</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif ($mode == "view" && !empty($formData)): ?>
        <!-- 詳細表示モード -->
        <div class="detail-container">
            <div class="detail-header">
                <div class="detail-title"><?php echo htmlspecialchars($formData['Title']); ?></div>
                <div class="detail-ai"><?php echo htmlspecialchars($formData['AiName']); ?></div>
            </div>
            
            <div class="detail-section">
                <div class="detail-section-title">製品・サービス</div>
                <div class="detail-product"><?php echo nl2br(htmlspecialchars($formData['Product'])); ?></div>
            </div>
            
            <div class="detail-section">
                <div class="detail-section-title">プロンプト</div>
                <div class="detail-prompt"><?php echo htmlspecialchars($formData['Prompt']); ?></div>
            </div>
            
            <?php if (!empty($formData['HowToUse'])): ?>
            <div class="detail-section">
                <div class="detail-section-title">使い方</div>
                <div class="detail-how-to-use"><?php echo nl2br(htmlspecialchars($formData['HowToUse'])); ?></div>
            </div>
            <?php endif; ?>
            
            <a href="View.php" class="btn">戻る</a>
            <?php if (!empty($formData['AiUrl'])): ?>
            <a href="<?php echo htmlspecialchars($formData['AiUrl']); ?>" target="_blank" class="btn">AIを試す</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</body>
</html>
EOD;

// ファイルの更新処理
try {
    // 元のファイルのバックアップを作成
    $originalFile = 'View.php';
    $backupFile = 'AISample_backup_' . date('YmdHis') . '.php';
    
    if (file_exists($originalFile)) {
        if (copy($originalFile, $backupFile)) {
            // 新しいコードでファイルを更新
            if (file_put_contents($originalFile, $newCode)) {
                $success = "View.phpの更新が完了しました。バックアップファイル: " . $backupFile;
            } else {
                $error = "ファイルの書き込みに失敗しました。";
            }
        } else {
            $error = "バックアップファイルの作成に失敗しました。";
        }
    } else {
        // 元のファイルが存在しない場合は新規作成
        if (file_put_contents($originalFile, $newCode)) {
            $success = "View.phpを新規作成しました。";
        } else {
            $error = "ファイルの作成に失敗しました。";
        }
    }
} catch (Exception $e) {
    $error = "エラー: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View.php更新</title>
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
        .changes {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            margin-right: 10px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <h1>View.php更新</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="container">
        <h2>適用された修正内容</h2>
        
        <div class="changes">
            <h3>1. エラー表示の有効化</h3>
            <pre>
// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
            </pre>
            
            <h3>2. AIUsageStats挿入処理のエラーハンドリング改善</h3>
            <pre>
// 使用統計を記録 - エラーが発生しても処理を続行
try {
    $stmt = $conn->prepare("INSERT INTO AIUsageStats (ai_name, sample_id, user_id, action_type) VALUES (:ai_name, :sample_id, :user_id, :action_type)");
    $stmt->bindParam(':ai_name', $formData['AiName'], PDO::PARAM_STR);
    $stmt->bindParam(':sample_id', $sampleId, PDO::PARAM_INT);
    $userId = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
    $actionType = 'view';
    $stmt->bindParam(':action_type', $actionType, PDO::PARAM_STR);
    $stmt->execute();
} catch (PDOException $e) {
    // 使用統計の記録に失敗しても、ユーザー体験に影響しないようにエラーを無視
    // ただし、開発時にはエラーを確認できるようにコメントアウト
    // $error = "使用統計記録エラー: " . $e->getMessage();
}
            </pre>
            
            <h3>3. 詳細表示モード（view）のHTML追加</h3>
            <pre>
&lt;!-- 詳細表示モード --&gt;
&lt;div class="detail-container"&gt;
    &lt;div class="detail-header"&gt;
        &lt;div class="detail-title"&gt;&lt;?php echo htmlspecialchars($formData['Title']); ?&gt;&lt;/div&gt;
        &lt;div class="detail-ai"&gt;&lt;?php echo htmlspecialchars($formData['AiName']); ?&gt;&lt;/div&gt;
    &lt;/div&gt;
    
    &lt;div class="detail-section"&gt;
        &lt;div class="detail-section-title"&gt;製品・サービス&lt;/div&gt;
        &lt;div class="detail-product"&gt;&lt;?php echo nl2br(htmlspecialchars($formData['Product'])); ?&gt;&lt;/div&gt;
    &lt;/div&gt;
    
    &lt;div class="detail-section"&gt;
        &lt;div class="detail-section-title"&gt;プロンプト&lt;/div&gt;
        &lt;div class="detail-prompt"&gt;&lt;?php echo htmlspecialchars($formData['Prompt']); ?&gt;&lt;/div&gt;
    &lt;/div&gt;
    
    &lt;?php if (!empty($formData['HowToUse'])): ?&gt;
    &lt;div class="detail-section"&gt;
        &lt;div class="detail-section-title"&gt;使い方&lt;/div&gt;
        &lt;div class="detail-how-to-use"&gt;&lt;?php echo nl2br(htmlspecialchars($formData['HowToUse'])); ?&gt;&lt;/div&gt;
    &lt;/div&gt;
    &lt;?php endif; ?&gt;
    
    &lt;a href="View.php" class="btn"&gt;戻る&lt;/a&gt;
    &lt;?php if (!empty($formData['AiUrl'])): ?&gt;
    &lt;a href="&lt;?php echo htmlspecialchars($formData['AiUrl']); ?&gt;" target="_blank" class="btn"&gt;AIを試す&lt;/a&gt;
    &lt;?php endif; ?&gt;
&lt;/div&gt;
            </pre>
            
            <h3>4. リンクの修正</h3>
            <pre>
&lt;a href="View.php?id=&lt;?php echo $sample['id']; ?&gt;" class="sample-card-link"&gt;詳細を見る&lt;/a&gt;
            </pre>
        </div>
        
        <a href="View.php" class="btn">AI活用サンプル一覧に戻る</a>
        <a href="View.php?id=6" class="btn">ID=6の詳細表示をテスト</a>
    </div>
</body>
</html>
