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
    
    // カテゴリの削除処理
    if (isset($_POST['delete']) && isset($_POST['id']) && isset($_POST['csrf_token'])) {
        // CSRFトークンの検証
        if ($_POST['csrf_token'] !== $csrf_token) {
            throw new Exception("セキュリティトークンが無効です。ページを再読み込みしてください。");
        }
        
        $id = $_POST['id'];
        
        // このカテゴリを使用しているサンプル数を確認
        $stmt = $conn->prepare("SELECT COUNT(*) FROM AISampleInfo WHERE category_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            // 使用中のカテゴリは「その他」カテゴリに移動
            // まず「その他」カテゴリのIDを取得
            $stmt = $conn->prepare("SELECT id FROM AISampleCategories WHERE name = 'その他' LIMIT 1");
            $stmt->execute();
            $otherCategoryId = $stmt->fetchColumn();
            
            if (!$otherCategoryId) {
                // 「その他」カテゴリがない場合は作成
                $stmt = $conn->prepare("INSERT INTO AISampleCategories (name, description) VALUES ('その他', 'その他の目的に関するサンプル')");
                $stmt->execute();
                $otherCategoryId = $conn->lastInsertId();
            }
            
            // サンプルのカテゴリを「その他」に変更
            $stmt = $conn->prepare("UPDATE AISampleInfo SET category_id = :other_id WHERE category_id = :id");
            $stmt->bindParam(':other_id', $otherCategoryId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $success = "カテゴリを削除しました。このカテゴリを使用していた " . $count . " 件のサンプルは「その他」カテゴリに移動しました。";
        } else {
            $success = "カテゴリを削除しました。";
        }
        
        // カテゴリの削除
        $stmt = $conn->prepare("DELETE FROM AISampleCategories WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    // カテゴリの追加処理
    if (isset($_POST['add']) && isset($_POST['name']) && isset($_POST['description']) && isset($_POST['csrf_token'])) {
        // CSRFトークンの検証
        if ($_POST['csrf_token'] !== $csrf_token) {
            throw new Exception("セキュリティトークンが無効です。ページを再読み込みしてください。");
        }
        
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (empty($name)) {
            $error = "カテゴリ名を入力してください。";
        } else {
            // 同じ名前のカテゴリが存在するか確認
            $stmt = $conn->prepare("SELECT COUNT(*) FROM AISampleCategories WHERE name = :name");
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                $error = "同じ名前のカテゴリが既に存在します。";
            } else {
                $stmt = $conn->prepare("INSERT INTO AISampleCategories (name, description) VALUES (:name, :description)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->execute();
                
                $success = "新しいカテゴリ「" . htmlspecialchars($name) . "」を追加しました。";
            }
        }
    }
    
    // カテゴリの編集処理
    if (isset($_POST['update']) && isset($_POST['id']) && isset($_POST['name']) && isset($_POST['description']) && isset($_POST['csrf_token'])) {
        // CSRFトークンの検証
        if ($_POST['csrf_token'] !== $csrf_token) {
            throw new Exception("セキュリティトークンが無効です。ページを再読み込みしてください。");
        }
        
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (empty($name)) {
            $error = "カテゴリ名を入力してください。";
        } else {
            // 同じ名前の別のカテゴリが存在するか確認
            $stmt = $conn->prepare("SELECT COUNT(*) FROM AISampleCategories WHERE name = :name AND id != :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                $error = "同じ名前のカテゴリが既に存在します。";
            } else {
                $stmt = $conn->prepare("UPDATE AISampleCategories SET name = :name, description = :description WHERE id = :id");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                $success = "カテゴリ「" . htmlspecialchars($name) . "」を更新しました。";
            }
        }
    }
    
    // 編集モード
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $id = $_GET['edit'];
        $stmt = $conn->prepare("SELECT * FROM AISampleCategories WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $editCategory = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$editCategory) {
            $error = "指定されたカテゴリが見つかりません。";
        }
    }
    
    // カテゴリ一覧の取得
    $stmt = $conn->query("SELECT c.*, COUNT(s.id) as sample_count 
                         FROM AISampleCategories c 
                         LEFT JOIN AISampleInfo s ON c.id = s.category_id 
                         GROUP BY c.id 
                         ORDER BY c.name");
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
    <title>カテゴリ管理</title>
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
        h1, h2, h3 {
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
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn-success {
            background-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .btn-secondary {
            background-color: #95a5a6;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
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
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea.form-control {
            min-height: 100px;
        }
        .actions {
            white-space: nowrap;
        }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 12px;
            font-weight: bold;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 10px;
            background-color: #6c757d;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            max-width: 500px;
        }
        .modal-title {
            margin-top: 0;
        }
        .modal-footer {
            margin-top: 20px;
            text-align: right;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
    </style>
</head>
<body>
    <h1>カテゴリ管理</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="container">
        <h2><?php echo $editCategory ? 'カテゴリの編集' : '新しいカテゴリの追加'; ?></h2>
        
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">カテゴリ名</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">説明</label>
                <textarea id="description" name="description" class="form-control"><?php echo $editCategory ? htmlspecialchars($editCategory['description']) : ''; ?></textarea>
            </div>
            
            <?php if ($editCategory): ?>
                <button type="submit" name="update" class="btn btn-success">更新</button>
                <a href="category_manager.php" class="btn btn-secondary">キャンセル</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn btn-success">追加</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="container">
        <h2>カテゴリ一覧</h2>
        
        <?php if (empty($categories)): ?>
            <p>カテゴリがありません。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>カテゴリ名</th>
                        <th>説明</th>
                        <th>サンプル数</th>
                        <th>作成日</th>
                        <th>更新日</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                            <td>
                                <?php if ($category['sample_count'] > 0): ?>
                                    <span class="badge"><?php echo $category['sample_count']; ?></span>
                                <?php else: ?>
                                    0
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($category['created_at'])); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($category['updated_at'])); ?></td>
                            <td class="actions">
                                <a href="category_manager.php?edit=<?php echo $category['id']; ?>" class="btn">編集</a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>', <?php echo $category['sample_count']; ?>)">削除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <a href="/AI_Sample/View.php" class="btn">AI活用サンプル一覧に戻る</a>
    </div>

    <!-- 削除確認モーダル -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 class="modal-title">カテゴリの削除</h3>
            <p id="deleteMessage"></p>
            <div class="modal-footer">
                <form method="post" action="" id="deleteForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">キャンセル</button>
                    <button type="submit" name="delete" class="btn btn-danger">削除</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // 削除確認モーダル
        function confirmDelete(id, name, sampleCount) {
            document.getElementById('deleteId').value = id;
            
            let message = `カテゴリ「${name}」を削除してもよろしいですか？`;
            
            if (sampleCount > 0) {
                message += `<br><br><strong>注意:</strong> このカテゴリは現在 ${sampleCount} 件のサンプルで使用されています。削除すると、これらのサンプルは「その他」カテゴリに移動されます。`;
            }
            
            document.getElementById('deleteMessage').innerHTML = message;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // モーダルの外側をクリックしたときにモーダルを閉じる
        window.onclick = function(event) {
            let modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>