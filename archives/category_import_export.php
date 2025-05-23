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
$conn = null;

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
    
    // カテゴリ一覧の取得
    $stmt = $conn->query("SELECT * FROM AISampleCategories ORDER BY display_order, name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // CSVエクスポート処理
    if (isset($_POST['export']) && isset($_POST['csrf_token'])) {
        // CSRFトークンの検証
        if ($_POST['csrf_token'] !== $csrf_token) {
            throw new Exception("セキュリティトークンが無効です。ページを再読み込みしてください。");
        }
        
        // 出力バッファリングを開始
        ob_start();
        
        // CSVヘッダーの出力
        $output = fopen('php://output', 'w');
        fputcsv($output, ['id', 'name', 'description', 'display_order', 'icon_key']);
        
        // カテゴリデータの出力
        foreach ($categories as $category) {
            fputcsv($output, [
                $category['id'],
                $category['name'],
                $category['description'],
                $category['display_order'],
                isset($category['icon_key']) ? $category['icon_key'] : ''
            ]);
        }
        fclose($output);
        
        // 出力バッファの取得
        $csv = ob_get_clean();
        
        // CSVファイルとしてダウンロード
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="categories_' . date('Y-m-d') . '.csv"');
        echo $csv;
        exit;
    }
    
    // CSVインポート処理
    if (isset($_POST['import']) && isset($_POST['csrf_token'])) {
        // CSRFトークンの検証
        if ($_POST['csrf_token'] !== $csrf_token) {
            throw new Exception("セキュリティトークンが無効です。ページを再読み込みしてください。");
        }
        
        // ファイルのチェック
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("ファイルのアップロードに失敗しました。");
        }
        
        // CSVファイルの読み込み
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if ($handle === false) {
            throw new Exception("ファイルを開けませんでした。");
        }
        
        // ヘッダー行の読み込み
        $header = fgetcsv($handle);
        
        // ヘッダーの検証
        if (!in_array('name', $header) || !in_array('description', $header)) {
            throw new Exception("CSVファイルの形式が正しくありません。少なくとも 'name' と 'description' の列が必要です。");
        }
        
        // カラムのインデックスを取得
        $idIndex = array_search('id', $header);
        $nameIndex = array_search('name', $header);
        $descriptionIndex = array_search('description', $header);
        $displayOrderIndex = array_search('display_order', $header);
        $iconKeyIndex = array_search('icon_key', $header);
        
        // トランザクション開始
        $conn->beginTransaction();
        
        $importCount = 0;
        $updateCount = 0;
        
        // データの読み込みと処理
        while (($data = fgetcsv($handle)) !== false) {
            $id = ($idIndex !== false && isset($data[$idIndex])) ? $data[$idIndex] : null;
            $name = $data[$nameIndex];
            $description = ($descriptionIndex !== false && isset($data[$descriptionIndex])) ? $data[$descriptionIndex] : '';
            $displayOrder = ($displayOrderIndex !== false && isset($data[$displayOrderIndex])) ? $data[$displayOrderIndex] : null;
            $iconKey = ($iconKeyIndex !== false && isset($data[$iconKeyIndex])) ? $data[$iconKeyIndex] : null;
            
            if (empty($name)) {
                continue; // 名前が空の行はスキップ
            }
            
            if ($id) {
                // 既存のカテゴリを更新
                $stmt = $conn->prepare("UPDATE AISampleCategories SET name = :name, description = :description" . 
                                      ($displayOrder !== null ? ", display_order = :display_order" : "") . 
                                      ($iconKey !== null ? ", icon_key = :icon_key" : "") . 
                                      " WHERE id = :id");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                if ($displayOrder !== null) {
                    $stmt->bindParam(':display_order', $displayOrder);
                }
                if ($iconKey !== null) {
                    $stmt->bindParam(':icon_key', $iconKey);
                }
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $updateCount++;
                }
            } else {
                // 新しいカテゴリを追加
                $stmt = $conn->prepare("INSERT INTO AISampleCategories (name, description" . 
                                      ($displayOrder !== null ? ", display_order" : "") . 
                                      ($iconKey !== null ? ", icon_key" : "") . 
                                      ") VALUES (:name, :description" . 
                                      ($displayOrder !== null ? ", :display_order" : "") . 
                                      ($iconKey !== null ? ", :icon_key" : "") . 
                                      ")");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                if ($displayOrder !== null) {
                    $stmt->bindParam(':display_order', $displayOrder);
                }
                if ($iconKey !== null) {
                    $stmt->bindParam(':icon_key', $iconKey);
                }
                $stmt->execute();
                
                $importCount++;
            }
        }
        
        fclose($handle);
        
        // トランザクションをコミット
        $conn->commit();
        
        $success = "{$importCount}件のカテゴリを追加し、{$updateCount}件のカテゴリを更新しました。";
        
        // カテゴリ一覧を再取得
        $stmt = $conn->query("SELECT * FROM AISampleCategories ORDER BY display_order, name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch(PDOException $e) {
    // データベースエラーの処理
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    $error = "データベースエラー: " . $e->getMessage();
} catch(Exception $e) {
    // その他のエラーの処理
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロンプトカテゴリのインポート/エクスポート</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-box {
            border-left: 4px solid #3498db;
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
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
            padding: 8px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <h1>カテゴリのインポート/エクスポート</h1>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="container">
        <h2>プロンプトカテゴリのエクスポート</h2>
        <div class="info-box">
            <p>現在登録されているすべてのプロンプトカテゴリをCSVファイルとしてエクスポートします。</p>
            <p>エクスポートされたCSVファイルは、プロンプトカテゴリのバックアップや他のシステムへの移行に使用できます。</p>
        </div>
        
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit" name="export" class="btn">CSVファイルをエクスポート</button>
        </form>
    </div>
    
    <div class="container">
        <h2>プロンプトカテゴリのインポート</h2>
        <div class="info-box">
            <p>CSVファイルの形式:</p>
            <ul>
                <li>CSVファイルには、少なくとも「name」と「description」の列が必要です。</li>
                <li>「id」列がある場合、その値に一致するプロンプトカテゴリが更新されます。</li>
                <li>「id」列がない場合、または値が空の場合は新しいプロンプトカテゴリとして追加されます。</li>
                <li>「icon_key」と「display_order」の列はオプションです。</li>
            </ul>
        </div>
        
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="csv_file">CSVファイル</label>
                <input type="file" id="csv_file" name="csv_file" class="form-control" accept=".csv" required>
            </div>
            
            <button type="submit" name="import" class="btn">CSVファイルからインポート</button>
        </form>
    </div>
    
    <div class="container">
        <h2>現在のプロンプトカテゴリ一覧</h2>
        
        <?php if (empty($categories)): ?>
            <p>カテゴリがまだ登録されていません。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>プロンプトカテゴリ名</th>
                        <th>説明</th>
                        <th>表示順</th>
                        <th>アイコン</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                            <td><?php echo isset($category['display_order']) ? $category['display_order'] : ''; ?></td>
                            <td><?php echo isset($category['icon_key']) ? $category['icon_key'] : ''; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <a href="category_manager.php" class="btn">プロンプトカテゴリ管理に戻る</a>
</body>
</html>