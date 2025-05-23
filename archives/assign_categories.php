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
$samples = [];
$categories = [];
$conn = null;

try {
    // データベース接続
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // カテゴリの取得
    $stmt = $conn->query("SELECT * FROM AISampleCategories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // サンプルの取得
    $stmt = $conn->query("SELECT * FROM AISampleInfo ORDER BY id DESC");
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // フォームが送信された場合
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // トランザクション開始
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("UPDATE AISampleInfo SET category_id = :category_id WHERE id = :id");
        
        $updateCount = 0;
        foreach ($_POST['category'] as $sampleId => $categoryId) {
            if (!empty($categoryId)) {
                $stmt->bindParam(':category_id', $categoryId);
                $stmt->bindParam(':id', $sampleId);
                $stmt->execute();
                $updateCount++;
            }
        }
        
        // トランザクションをコミット
        $conn->commit();
        
        $success = "カテゴリの割り当てが完了しました。更新件数: " . $updateCount;
        
        // 更新後のサンプルを再取得
        $stmt = $conn->query("SELECT * FROM AISampleInfo ORDER BY id DESC");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch(PDOException $e) {
    // エラーが発生した場合はロールバック
    if ($conn && $conn->inTransaction()) {
        $conn->rollback();
    }
    $error = "エラー: " . $e->getMessage();
}

// キーワードに基づく自動カテゴリ割り当ての関数
function suggestCategory($title, $product, $categories) {
    $keywords = [
        '文書作成' => ['議事録', '報告書', 'お知らせ', '文書', 'レポート', 'メール'],
        '画像生成' => ['画像', 'ポスター', 'チラシ', 'イラスト', 'デザイン', 'バナー', 'ロゴ', 'Midjourney', 'DALL-E', 'Stable Diffusion'],
        '会計管理' => ['会費', '集金', '予算', '経費', '会計', '出納', '収支', 'Excel'],
        'イベント企画' => ['イベント', '祭り', '清掃', '防災訓練', '企画', '催し'],
        '情報発信' => ['ウェブサイト', 'SNS', 'メール', '情報発信', 'ニュースレター', '広報'],
        '防災対策' => ['防災', '避難', '安全', '災害', '緊急', 'マニュアル'],
        'コミュニティ活性化' => ['コミュニティ', '交流', '参加', '活性化', '住民', '協力']
    ];
    
    $text = mb_strtolower($title . ' ' . $product);
    $scores = [];
    
    foreach ($keywords as $category => $words) {
        $scores[$category] = 0;
        foreach ($words as $word) {
            if (mb_stripos($text, $word) !== false) {
                $scores[$category]++;
            }
        }
    }
    
    arsort($scores);
    $topCategory = key($scores);
    
    // カテゴリ名からIDを取得
    foreach ($categories as $category) {
        if ($category['name'] === $topCategory) {
            return $category['id'];
        }
    }
    
    // 該当するカテゴリがない場合は「その他」を返す
    foreach ($categories as $category) {
        if ($category['name'] === 'その他') {
            return $category['id'];
        }
    }
    
    return null;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>サンプルカテゴリ割り当て</title>
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
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        select {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
            width: 100%;
            max-width: 300px;
        }
        .auto-assign {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <h1>サンプルカテゴリ割り当て</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="container">
        <h2>カテゴリ割り当て</h2>
        
        <div class="auto-assign">
            <h3>自動カテゴリ割り当て</h3>
            <p>タイトルと製品名に基づいて、AIサンプルに自動的にカテゴリを割り当てることができます。</p>
            <button id="autoAssignBtn" class="btn btn-secondary">自動割り当て</button>
        </div>
        
        <form method="post" action="">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>AI名</th>
                        <th>製品</th>
                        <th>カテゴリ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($samples as $sample): ?>
                        <tr>
                            <td><?php echo $sample['id']; ?></td>
                            <td><?php echo htmlspecialchars($sample['Title']); ?></td>
                            <td><?php echo htmlspecialchars($sample['AiName']); ?></td>
                            <td><?php echo htmlspecialchars($sample['Product']); ?></td>
                            <td>
                                <select name="category[<?php echo $sample['id']; ?>]" class="category-select" data-title="<?php echo htmlspecialchars($sample['Title']); ?>" data-product="<?php echo htmlspecialchars($sample['Product']); ?>">
                                    <option value="">カテゴリを選択</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($sample['category_id']) && $sample['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <button type="submit" class="btn">カテゴリを保存</button>
            <a href="View.php" class="btn">AI活用サンプル一覧に戻る</a>
        </form>
    </div>

    <script>
        document.getElementById('autoAssignBtn').addEventListener('click', function() {
            const selects = document.querySelectorAll('.category-select');
            const categories = <?php echo json_encode($categories); ?>;
            
            selects.forEach(select => {
                if (!select.value) { // カテゴリが未設定の場合のみ
                    const title = select.getAttribute('data-title');
                    const product = select.getAttribute('data-product');
                    
                    // PHP関数をJavaScriptで再現
                    const suggestedCategoryId = suggestCategory(title, product, categories);
                    if (suggestedCategoryId) {
                        select.value = suggestedCategoryId;
                    }
                }
            });
            
            alert('未設定のサンプルに自動的にカテゴリを割り当てました。内容を確認して「カテゴリを保存」ボタンを押してください。');
        });
        
        function suggestCategory(title, product, categories) {
            const keywords = {
                '文書作成': ['議事録', '報告書', 'お知らせ', '文書', 'レポート', 'メール'],
                '画像生成': ['画像', 'ポスター', 'チラシ', 'イラスト', 'デザイン', 'バナー', 'ロゴ', 'Midjourney', 'DALL-E', 'Stable Diffusion'],
                '会計管理': ['会費', '集金', '予算', '経費', '会計', '出納', '収支', 'Excel'],
                'イベント企画': ['イベント', '祭り', '清掃', '防災訓練', '企画', '催し'],
                '情報発信': ['ウェブサイト', 'SNS', 'メール', '情報発信', 'ニュースレター', '広報'],
                '防災対策': ['防災', '避難', '安全', '災害', '緊急', 'マニュアル'],
                'コミュニティ活性化': ['コミュニティ', '交流', '参加', '活性化', '住民', '協力']
            };
            
            const text = (title + ' ' + product).toLowerCase();
            const scores = {};
            
            for (const category in keywords) {
                scores[category] = 0;
                for (const word of keywords[category]) {
                    if (text.indexOf(word.toLowerCase()) !== -1) {
                        scores[category]++;
                    }
                }
            }
            
            // スコアの高いカテゴリを見つける
            let topCategory = null;
            let topScore = 0;
            
            for (const category in scores) {
                if (scores[category] > topScore) {
                    topScore = scores[category];
                    topCategory = category;
                }
            }
            
            // カテゴリ名からIDを取得
            if (topCategory) {
                for (const category of categories) {
                    if (category.name === topCategory) {
                        return category.id;
                    }
                }
            }
            
            // 該当するカテゴリがない場合は「その他」を返す
            for (const category of categories) {
                if (category.name === 'その他') {
                    return category.id;
                }
            }
            
            return null;
        }
    </script>
</body>
</html>