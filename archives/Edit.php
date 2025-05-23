<?php
// データベース接続情報
$servername = "mysql213.phy.lolipop.lan";
$username = "LAA1337491";
$password = "kami2004";
$dbname = "LAA1337491-nsk";

// 初期化
$message = "";
$error = "";
$formData = [
    'id' => '',
    'UserId' => '',
    'noteUrl' => '',
    'Title' => '',
    'AiName' => '',
    'AiUrl' => '',
    'Product' => '',
    'Prompt' => ''
];
$isEditing = false;

// 編集モード：IDが指定された場合はデータを取得
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT * FROM AISampleInfo WHERE id = :id");
        $stmt->bindParam(':id', $_GET['edit'], PDO::PARAM_INT);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $formData = $row;
            $isEditing = true;
        } else {
            $error = "指定されたIDのデータが見つかりません。";
        }
        
        $conn = null;
    } catch(PDOException $e) {
        $error = "データ取得エラー: " . $e->getMessage();
    }
}

// フォームが送信された場合の処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // フォームデータを取得
    $formData = [
        'id' => $_POST['id'] ?? '',
        'UserId' => $_POST['UserId'] ?? '',
        'noteUrl' => $_POST['noteUrl'] ?? '',
        'Title' => $_POST['Title'] ?? '',
        'AiName' => $_POST['AiName'] ?? '',
        'AiUrl' => $_POST['AiUrl'] ?? '',
        'Product' => $_POST['Product'] ?? '',
        'Prompt' => $_POST['Prompt'] ?? '',
        'HowToUse' => $_POST['HowToUse'] ?? ''
    ];
    
    // 基本的なバリデーション
    $errors = [];
    if (empty($formData['UserId'])) {
        $errors[] = "ユーザーIDは必須です";
    }
    if (empty($formData['Title'])) {
        $errors[] = "タイトルは必須です";
    }
    if (empty($formData['AiName'])) {
        $errors[] = "AI名は必須です";
    }
    if (empty($formData['Product'])) {
        $errors[] = "成果物は必須です";
    }
    if (empty($formData['Prompt'])) {
        $errors[] = "プロンプトは必須です";
    }
    
    // エラーがなければデータベースに登録または更新
    if (empty($errors)) {
        try {
            // データベース接続
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 更新または新規登録
            if (!empty($formData['id'])) {
                // 更新処理
                $sql = "UPDATE AISampleInfo SET 
                        UserId = :UserId, 
                        noteUrl = :noteUrl, 
                        Title = :Title, 
                        AiName = :AiName, 
                        AiUrl = :AiUrl, 
                        Product = :Product, 
                        Prompt = :Prompt,
                        HowToUse = :HowToUse 
                        WHERE id = :id";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $formData['id'], PDO::PARAM_INT);
                $stmt->bindParam(':UserId', $formData['UserId']);
                $stmt->bindParam(':noteUrl', $formData['noteUrl']);
                $stmt->bindParam(':Title', $formData['Title']);
                $stmt->bindParam(':AiName', $formData['AiName']);
                $stmt->bindParam(':AiUrl', $formData['AiUrl']);
                $stmt->bindParam(':Product', $formData['Product']);
                $stmt->bindParam(':Prompt', $formData['Prompt']);
                $stmt->bindParam(':HowToUse', $formData['HowToUse']);
                
                $stmt->execute();
                
                $message = "ID: " . $formData['id'] . " のデータが正常に更新されました。";
            } else {
                // 新規登録処理
                $sql = "INSERT INTO AISampleInfo (UserId, noteUrl, Title, AiName, AiUrl, Product, Prompt, HowToUse) 
                        VALUES (:UserId, :noteUrl, :Title, :AiName, :AiUrl, :Product, :Prompt, :HowToUse)";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':UserId', $formData['UserId']);
                $stmt->bindParam(':noteUrl', $formData['noteUrl']);
                $stmt->bindParam(':Title', $formData['Title']);
                $stmt->bindParam(':AiName', $formData['AiName']);
                $stmt->bindParam(':AiUrl', $formData['AiUrl']);
                $stmt->bindParam(':Product', $formData['Product']);
                $stmt->bindParam(':Prompt', $formData['Prompt']);
                
                $stmt->execute();
                
                $message = "データが正常に登録されました。ID: " . $conn->lastInsertId();
            }
            
            // フォームをクリア
            $formData = [
                'id' => '',
                'UserId' => '',
                'noteUrl' => '',
                'Title' => '',
                'AiName' => '',
                'AiUrl' => '',
                'Product' => '',
                'Prompt' => '',
                'HowToUse' => ''
            ];
            $isEditing = false;
            
        } catch(PDOException $e) {
            $error = "エラー: " . $e->getMessage();
        }
        
        $conn = null;
    } else {
        $error = implode("<br>", $errors);
    }
}

// 登録済みデータの取得
$registeredData = [];
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->query("SELECT * FROM AISampleInfo ORDER BY id DESC LIMIT 10");
    $registeredData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "データ取得エラー: " . $e->getMessage();
}
$conn = null;
?>
<?php
// 既存のPOST処理コードの中に追加

// 画像アップロード処理関数
function handleImageUpload($fileInputName, $oldImagePath = null) {
    // 画像削除チェックボックスが選択されている場合
    $removeCheckboxName = 'remove_' . $fileInputName;
    if (isset($_POST[$removeCheckboxName]) && $_POST[$removeCheckboxName] == 'on') {
        // 古い画像が存在する場合は削除
        if ($oldImagePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldImagePath)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $oldImagePath);
        }
        return ''; // 空のパスを返す
    }
    
    // ファイルがアップロードされていない場合は古いパスを返す
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] == UPLOAD_ERR_NO_FILE) {
        return $oldImagePath;
    }
    
    // アップロードエラーチェック
    if ($_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('ファイルアップロードエラー: ' . $_FILES[$fileInputName]['error']);
    }
    
    // ファイルサイズチェック (2MB)
    if ($_FILES[$fileInputName]['size'] > 2 * 1024 * 1024) {
        throw new Exception('ファイルサイズが大きすぎます。2MB以下にしてください。');
    }
    
    // 画像タイプチェック
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $_FILES[$fileInputName]['tmp_name']);
    finfo_close($fileInfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('許可されていないファイル形式です。JPEG、PNG、GIF、WEBPのみ許可されています。');
    }
    
    // 画像の寸法チェック
    list($width, $height) = getimagesize($_FILES[$fileInputName]['tmp_name']);
    if ($width > 1200 || $height > 1200) {
        throw new Exception('画像サイズが大きすぎます。1200x1200px以下にしてください。');
    }
    
    // ファイル名の生成（一意のファイル名を作成）
    $extension = pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION);
    $newFileName = 'sample_' . uniqid() . '.' . $extension;
    
    // 保存ディレクトリの設定
    $uploadDir = '/images/samples/';
    $fullUploadDir = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
    
    // ディレクトリが存在しない場合は作成
    if (!file_exists($fullUploadDir)) {
        mkdir($fullUploadDir, 0755, true);
    }
    
    // ファイルの移動
    $uploadPath = $fullUploadDir . $newFileName;
    if (!move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $uploadPath)) {
        throw new Exception('ファイルの保存に失敗しました。');
    }
    
    // 古い画像が存在し、新しい画像と異なる場合は削除
    if ($oldImagePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldImagePath) && $uploadDir . $newFileName != $oldImagePath) {
        unlink($_SERVER['DOCUMENT_ROOT'] . $oldImagePath);
    }
    
    // 画像パスを返す（データベースに保存するパス）
    return $uploadDir . $newFileName;
}

// フォーム送信時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 既存のフォームデータ処理...
        
        // 画像アップロード処理
        $inputImagePath = isset($formData['InputImagePath']) ? $formData['InputImagePath'] : null;
        $outputImagePath = isset($formData['OutputImagePath']) ? $formData['OutputImagePath'] : null;
        
        $newInputImagePath = handleImageUpload('input_image', $inputImagePath);
        $newOutputImagePath = handleImageUpload('output_image', $outputImagePath);
        
        // データベース更新用の配列に画像パスを追加
        $updateData = [
            // 既存のフォームデータ...
            'InputImagePath' => $newInputImagePath,
            'OutputImagePath' => $newOutputImagePath
        ];
        
        // データベース更新処理...
        // 例: $stmt = $conn->prepare("UPDATE AISampleInfo SET Title = :title, ..., InputImagePath = :inputImagePath, OutputImagePath = :outputImagePath WHERE id = :id");
        // $stmt->bindParam(':inputImagePath', $updateData['InputImagePath']);
        // $stmt->bindParam(':outputImagePath', $updateData['OutputImagePath']);
        
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        // エラー処理...
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI活用サンプル登録</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .form-section {
            flex: 1;
            min-width: 300px;
        }
        .data-section {
            flex: 1;
            min-width: 300px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        button, .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .btn:hover {
            background-color: #2980b9;
        }
        .btn-edit {
            background-color: #f39c12;
            padding: 5px 10px;
            font-size: 14px;
        }
        .btn-edit:hover {
            background-color: #e67e22;
        }
        .btn-cancel {
            background-color: #95a5a6;
            margin-right: 10px;
        }
        .btn-cancel:hover {
            background-color: #7f8c8d;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .truncate {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .actions {
            white-space: nowrap;
        }
        .editing-highlight {
            background-color: #fff9c4;
        }
        /* Create.phpのstyleタグ内に以下のCSSを追加 */
        .btn-view {
            background-color: #2ecc71;
            padding: 5px 10px;
            font-size: 14px;
            margin-right: 5px;
        }
        .btn-view:hover {
            background-color: #27ae60;
        }
    </style>

<style>
.custom-file-label::after {
    content: "参照";
}
.img-thumbnail {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
    transition: transform 0.2s;
}
.img-thumbnail:hover {
    transform: scale(1.05);
}
.preview-container {
    animation: fadeIn 0.5s;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
</head>
<body>
    <h1>老人向けＡＩ活用サンプル登録</h1>
    
    <?php if (!empty($message)): ?>
        <div class="message success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2><?php echo $isEditing ? "データ編集 (ID: {$formData['id']})" : "新規登録"; ?></h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <?php if ($isEditing): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($formData['id']); ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="UserId">ユーザーID *</label>
                    <input type="text" id="UserId" name="UserId" value="<?php echo htmlspecialchars($formData['UserId']); ?>" required>
                </div>
                                <div class="form-group">
                    <label for="noteUrl">note URL</label>
                    <input type="text" id="noteUrl" name="noteUrl" value="<?php echo htmlspecialchars($formData['noteUrl']); ?>">
                </div>
                <div class="form-group">
                    <label for="Title">タイトル *</label>
                    <input type="text" id="Title" name="Title" value="<?php echo htmlspecialchars($formData['Title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="AiName">AI名 *</label>
                    <input type="text" id="AiName" name="AiName" value="<?php echo htmlspecialchars($formData['AiName']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="AiUrl">AI URL</label>
                    <input type="text" id="AiUrl" name="AiUrl" value="<?php echo htmlspecialchars($formData['AiUrl']); ?>">
                </div>
                <div class="form-group">
                    <label for="Product">成果物 *</label>
                    <input type="text" id="Product" name="Product" value="<?php echo htmlspecialchars($formData['Product']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="Prompt">プロンプト *</label>
                    <textarea id="Prompt" name="Prompt" required><?php echo htmlspecialchars($formData['Prompt']); ?></textarea>
                </div>
                <!-- Promptフィールドの後に以下を追加 -->
                <div class="form-group">
                    <label for="HowToUse">生成結果の使い方</label>
                    <textarea id="HowToUse" name="HowToUse"><?php echo htmlspecialchars($formData['HowToUse'] ?? ''); ?></textarea>
                </div>
                <!-- 既存のフォームの適切な位置に追加 -->
                <div class="form-group">
                    <label for="input_image">入力例画像:</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="input_image" name="input_image" accept="image/*">
                        <label class="custom-file-label" for="input_image">ファイルを選択...</label>
                    </div>
                    <?php if (!empty($formData['InputImagePath'])): ?>
                        <div class="mt-2">
                            <p>現在の画像:</p>
                            <img src="<?php echo htmlspecialchars($formData['InputImagePath']); ?>" alt="入力例" class="img-thumbnail" style="max-height: 200px;">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" id="remove_input_image" name="remove_input_image">
                                <label class="form-check-label" for="remove_input_image">
                                    この画像を削除
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <small class="form-text text-muted">推奨サイズ: 800x600px以下、最大ファイルサイズ: 2MB</small>
                </div>

                <div class="form-group">
                    <label for="output_image">出力例画像:</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="output_image" name="output_image" accept="image/*">
                        <label class="custom-file-label" for="output_image">ファイルを選択...</label>
                    </div>
                    <?php if (!empty($formData['OutputImagePath'])): ?>
                        <div class="mt-2">
                            <p>現在の画像:</p>
                            <img src="<?php echo htmlspecialchars($formData['OutputImagePath']); ?>" alt="出力例" class="img-thumbnail" style="max-height: 200px;">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" id="remove_output_image" name="remove_output_image">
                                <label class="form-check-label" for="remove_output_image">
                                    この画像を削除
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <small class="form-text text-muted">推奨サイズ: 800x600px以下、最大ファイルサイズ: 2MB</small>
                </div>
                <?php if ($isEditing): ?>
                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn btn-cancel">キャンセル</a>
                    <button type="submit">更新する</button>
                <?php else: ?>
                    <button type="submit">登録する</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="data-section">
            <h2>最近の登録データ</h2>
            <?php if (!empty($registeredData)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ユーザーID</th>
                            <th>タイトル</th>
                            <th>AI名</th>
                            <th>成果物</th>
                            <th>登録日時</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registeredData as $row): ?>
                            <tr<?php echo ($isEditing && $formData['id'] == $row['id']) ? ' class="editing-highlight"' : ''; ?>>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['UserId']); ?></td>
                                <td class="truncate" title="<?php echo htmlspecialchars($row['Title']); ?>">
                                    <?php echo htmlspecialchars($row['Title']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['AiName']); ?></td>
                                <td class="truncate" title="<?php echo htmlspecialchars($row['Product']); ?>">
                                    <?php echo htmlspecialchars($row['Product']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td class="actions">
                                    <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-edit">編集</a>
                                </td>
                                <td class="actions">
                                    <a href="View.php?id=<?php echo $row['id']; ?>" class="btn btn-view">詳細</a>
                                    <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-edit">編集</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>登録データがありません。</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="margin-top: 30px;">
        <h3>サンプルデータ</h3>
        <p>以下のようなAI活用事例を登録してください：</p>
        <ul>
            <li><strong>ユーザーID:</strong> gifted_panda752</li>
            <li><strong>タイトル:</strong> 生成AIで簡単なExcel表を作ろう: 初心者でもできる集金表作成</li>
            <li><strong>AI名:</strong> claude</li>
            <li><strong>成果物:</strong> 自治会の年間集金表</li>
            <li><strong>プロンプト:</strong> 1年分の集金表を作りたい。表題：2025年度自治会費、縦：a氏、b氏・・・、横：4月から翌3月</li>
        </ul>
    </div>
    <!-- ページの下部、</body>タグの前に追加 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ファイル入力フィールドのラベル更新
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'ファイルを選択...';
            var label = e.target.nextElementSibling;
            label.textContent = fileName;
            
            // プレビュー表示（オプション）
            if (e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.createElement('div');
                    preview.className = 'mt-2 preview-container';
                    preview.innerHTML = `
                        <p>プレビュー:</p>
                        <img src="${e.target.result}" class="img-thumbnail" style="max-height: 200px;">
                    `;
                    
                    // 既存のプレビューを削除
                    var existingPreview = input.parentElement.nextElementSibling;
                    if (existingPreview && existingPreview.classList.contains('preview-container')) {
                        existingPreview.remove();
                    }
                    
                    input.parentElement.after(preview);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    });
});
</script>
</body>
</html>