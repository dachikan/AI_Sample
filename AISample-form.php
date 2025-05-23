<?php
// データベース接続設定
require_once 'config/db_connect.php';

// カテゴリ情報を取得
try {
    $stmt = $conn->prepare("SELECT CategoryID, CategoryName FROM Categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "データベースエラー: " . $e->getMessage();
}

// フォームの初期値
$formData = [
    'id' => 0,
    'Title' => '',
    'AiName' => '',
    'category_id' => '',
    'Prompt' => '',
    'Description' => '',
    'Tips' => ''
];

// IDが指定されている場合はデータを取得（編集モード）
$isEditMode = false;
if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
    $id = intval($_GET['id']);
    $isEditMode = true;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM AISampleInfo WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $formData = array_merge($formData, $result);
        } else {
            $error = "指定されたIDのサンプルが見つかりませんでした。";
            $isEditMode = false;
        }
    } catch (PDOException $e) {
        $error = "データベースエラー: " . $e->getMessage();
    }
}

// フォーム送信時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームデータを取得
    $formData = [
        'id' => isset($_POST['id']) ? intval($_POST['id']) : 0,
        'Title' => isset($_POST['Title']) ? trim($_POST['Title']) : '',
        'AiName' => isset($_POST['AiName']) ? trim($_POST['AiName']) : '',
        'category_id' => isset($_POST['category_id']) ? intval($_POST['category_id']) : 0,
        'Prompt' => isset($_POST['Prompt']) ? trim($_POST['Prompt']) : '',
        'Description' => isset($_POST['Description']) ? trim($_POST['Description']) : '',
        'Tips' => isset($_POST['Tips']) ? trim($_POST['Tips']) : ''
    ];
    
    // 入力チェック
    $errors = [];
    if (empty($formData['Title'])) {
        $errors[] = "タイトルを入力してください。";
    }
    if (empty($formData['AiName'])) {
        $errors[] = "AI名を入力してください。";
    }
    if (empty($formData['category_id'])) {
        $errors[] = "カテゴリを選択してください。";
    }
    if (empty($formData['Prompt'])) {
        $errors[] = "プロンプトを入力してください。";
    }
    
    // エラーがなければデータを保存
    if (empty($errors)) {
        try {
            // 現在の日時
            $now = date('Y-m-d H:i:s');
            
            if ($formData['id'] > 0) {
                // 更新（編集モード）
                $stmt = $conn->prepare("
                    UPDATE AISampleInfo 
                    SET Title = :Title, 
                        AiName = :AiName, 
                        category_id = :category_id, 
                        Prompt = :Prompt, 
                        Description = :Description, 
                        Tips = :Tips, 
                        updated_at = :updated_at 
                    WHERE id = :id
                ");
                $stmt->bindParam(':id', $formData['id'], PDO::PARAM_INT);
                $stmt->bindParam(':updated_at', $now, PDO::PARAM_STR);
            } else {
                // 新規作成
                $stmt = $conn->prepare("
                    INSERT INTO AISampleInfo 
                    (Title, AiName, category_id, Prompt, Description, Tips, created_at) 
                    VALUES 
                    (:Title, :AiName, :category_id, :Prompt, :Description, :Tips, :created_at)
                ");
                $stmt->bindParam(':created_at', $now, PDO::PARAM_STR);
            }
            
            // 共通のバインドパラメータ
            $stmt->bindParam(':Title', $formData['Title'], PDO::PARAM_STR);
            $stmt->bindParam(':AiName', $formData['AiName'], PDO::PARAM_STR);
            $stmt->bindParam(':category_id', $formData['category_id'], PDO::PARAM_INT);
            $stmt->bindParam(':Prompt', $formData['Prompt'], PDO::PARAM_STR);
            $stmt->bindParam(':Description', $formData['Description'], PDO::PARAM_STR);
            $stmt->bindParam(':Tips', $formData['Tips'], PDO::PARAM_STR);
            
            $stmt->execute();
            
            // 新規作成の場合は挿入されたIDを取得
            if ($formData['id'] === 0) {
                $formData['id'] = $conn->lastInsertId();
            }
            
            // 一覧ページにリダイレクト
            header("Location: AISample-List.php?success=1");
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "データベースエラー: " . $e->getMessage();
        }
    }
}

// ページタイトルを設定
$pageTitle = $isEditMode ? "標準プロンプトの編集" : "新規標準プロンプト登録";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | AI活用サンプル集</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        .required-label::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <header class="bg-light py-3">
        <div class="container">
            <h1 class="text-center"><?php echo $pageTitle; ?></h1>
            <p class="text-center text-muted">AI活用サンプル集</p>
            <div class="d-flex justify-content-start mt-3">
                <a href="AISample-List.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> 標準プロンプト一覧に戻る
                </a>
            </div>
        </div>
    </header>

    <div class="container mt-4">
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="AISample-form.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $formData['id']; ?>">
            
            <div class="form-section">
                <h3>基本情報</h3>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="Title" class="form-label required-label">タイトル</label>
                        <input type="text" class="form-control" id="Title" name="Title" value="<?php echo htmlspecialchars($formData['Title']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="AiName" class="form-label required-label">AI名</label>
                        <input type="text" class="form-control" id="AiName" name="AiName" value="<?php echo htmlspecialchars($formData['AiName']); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="category_id" class="form-label required-label">カテゴリ</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">カテゴリを選択してください</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['CategoryID']; ?>" <?php echo $formData['category_id'] == $category['CategoryID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['CategoryName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-section">
                <h3>プロンプト</h3>
                <div class="mb-3">
                    <label for="Prompt" class="form-label required-label">プロンプト内容</label>
                    <textarea class="form-control" id="Prompt" name="Prompt" rows="8" required><?php echo htmlspecialchars($formData['Prompt']); ?></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h3>詳細情報</h3>
                <div class="mb-3">
                    <label for="Description" class="form-label">説明</label>
                    <textarea class="form-control" id="Description" name="Description" rows="3"><?php echo htmlspecialchars($formData['Description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="Tips" class="form-label">活用のコツ</label>
                    <textarea class="form-control" id="Tips" name="Tips" rows="3"><?php echo htmlspecialchars($formData['Tips']); ?></textarea>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mb-5">
                <a href="AISample-List.php" class="btn btn-secondary">キャンセル</a>
                <button type="submit" class="btn btn-primary">
                    <?php echo $isEditMode ? '更新する' : '登録する'; ?>
                </button>
            </div>
        </form>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>AI活用サンプル集</h5>
                    <p>初心者に役立つAIプロンプトのサンプル集</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2023-<?php echo date('Y'); ?> AI活用サンプル集. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
