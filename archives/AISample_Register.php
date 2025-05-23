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
    
    // フォーム送信処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        // CSRFトークンの検証
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
            throw new Exception("セキュリティトークンが無効です。ページを再読み込みしてください。");
        }
        
        // 入力値の取得と検証
        $title = trim($_POST['title'] ?? '');
        $prompt = trim($_POST['prompt'] ?? '');
        $aiName = trim($_POST['ai_name'] ?? '');
        $product = trim($_POST['product'] ?? '');
        $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $description = trim($_POST['description'] ?? '');
        
        // 必須項目の検証
        if (empty($title)) {
            throw new Exception("タイトルは必須項目です。");
        }
        if (empty($prompt)) {
            throw new Exception("プロンプトは必須項目です。");
        }
        if (empty($aiName)) {
            throw new Exception("AI名は必須項目です。");
        }
        
        // データベースに登録
        $stmt = $conn->prepare("INSERT INTO AISampleInfo (Title, Prompt, AiName, Product, category_id, Description, RegisterDate) 
                               VALUES (:title, :prompt, :aiName, :product, :categoryId, :description, NOW())");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':prompt', $prompt);
        $stmt->bindParam(':aiName', $aiName);
        $stmt->bindParam(':product', $product);
        $stmt->bindParam(':categoryId', $categoryId);
        $stmt->bindParam(':description', $description);
        $stmt->execute();
        
        $newId = $conn->lastInsertId();
        $success = "AIサンプルが正常に登録されました。";
        
        // 登録後、詳細ページにリダイレクト
        header("Location: ViewView.php?id=" . $newId);
        exit;
    }
    
} catch(PDOException $e) {
    $error = "データベースエラー: " . $e->getMessage();
} catch(Exception $e) {
    $error = $e->getMessage();
}

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
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIサンプル新規登録</title>
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        textarea.form-control {
            min-height: 150px;
            font-family: monospace;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .category-select {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .category-option {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background-color: #f0f0f0;
            border-radius: 4px;
            cursor: pointer;
        }
        .category-option:hover {
            background-color: #e0e0e0;
        }
        .category-option.selected {
            background-color: #3498db;
            color: white;
        }
        .category-icon {
            margin-right: 8px;
            font-size: 1.2em;
        }
        .required {
            color: #e74c3c;
            margin-left: 5px;
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .help-text {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h1>AIサンプル新規登録</h1>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="container">
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="title">タイトル<span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required>
                <div class="help-text">サンプルの内容を簡潔に表すタイトルを入力してください。</div>
            </div>
            
            <div class="form-group">
                <label for="prompt">プロンプト<span class="required">*</span></label>
                <textarea id="prompt" name="prompt" class="form-control" required></textarea>
                <div class="help-text">AIに入力したプロンプトを正確に入力してください。</div>
            </div>
            
            <div class="form-group">
                <label for="product">生成された作品</label>
                <textarea id="product" name="product" class="form-control"></textarea>
                <div class="help-text">プロンプトを実行して生成された作品やテキストを入力してください。</div>
            </div>
            
            <div class="form-group">
                <label for="ai_name">AI名<span class="required">*</span></label>
                <input type="text" id="ai_name" name="ai_name" class="form-control" required>
                <div class="help-text">使用したAIの名前を入力してください（例：ChatGPT、Gemini、Claude）。</div>
            </div>
            
            <div class="form-group">
                <label>プロンプトカテゴリ</label>
                <div class="category-select">
                    <label class="category-option">
                        <input type="radio" name="category_id" value="0" style="display: none;" checked>
                        <span class="category-icon"><?php echo $icons['other']; ?></span>
                        未分類
                    </label>
                    
                    <?php foreach ($categories as $category): ?>
                        <?php 
                        $catIcon = isset($category['icon_key']) && isset($icons[$category['icon_key']]) 
                            ? $icons[$category['icon_key']] 
                            : $icons['other'];
                        ?>
                        <label class="category-option">
                            <input type="radio" name="category_id" value="<?php echo $category['id']; ?>" style="display: none;">
                            <span class="category-icon"><?php echo $catIcon; ?></span>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="help-text">サンプルに最も適したプロンプトカテゴリを選択してください。</div>
            </div>
            
            <div class="form-group">
                <label for="description">説明</label>
                <textarea id="description" name="description" class="form-control"></textarea>
                <div class="help-text">サンプルの詳細な説明や使用方法、注意点などを入力してください。</div>
            </div>
            
            <div class="form-actions">
                <a href="AISampleList_with_advanced.php" class="btn btn-secondary">キャンセル</a>
                <button type="submit" name="submit" class="btn">登録する</button>
            </div>
        </form>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // カテゴリ選択のスタイル制御
        const categoryOptions = document.querySelectorAll('.category-option');
        
        categoryOptions.forEach(option => {
            const radio = option.querySelector('input[type="radio"]');
            
            // 初期状態の設定
            if (radio.checked) {
                option.classList.add('selected');
            }
            
            // クリックイベントの設定
            option.addEventListener('click', function() {
                // すべての選択状態をリセット
                categoryOptions.forEach(opt => {
                    opt.classList.remove('selected');
                    opt.querySelector('input[type="radio"]').checked = false;
                });
                
                // クリックされた要素を選択状態に
                radio.checked = true;
                option.classList.add('selected');
            });
        });
    });
    </script>
</body>
</html>
