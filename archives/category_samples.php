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
$categories = [];
$samples = [];
$category = null;
$conn = null;

// カテゴリIDの取得
$categoryId = isset($_GET['id']) ? intval($_GET['id']) : null;

try {
    // データベース接続
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // カテゴリ一覧の取得
    $stmt = $conn->query("SELECT * FROM AISampleCategories ORDER BY display_order, name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 未分類のサンプル数を取得
    $stmt = $conn->query("SELECT COUNT(*) FROM AISampleInfo WHERE category_id IS NULL OR category_id = 0");
    $uncategorizedCount = $stmt->fetchColumn();
    
    // 全サンプル数を取得
    $stmt = $conn->query("SELECT COUNT(*) FROM AISampleInfo");
    $totalSamplesCount = $stmt->fetchColumn();
    
    // カテゴリ情報の取得
    if ($categoryId !== null) {
        if ($categoryId > 0) {
            // 特定のカテゴリ情報を取得
            $stmt = $conn->prepare("SELECT * FROM AISampleCategories WHERE id = :id");
            $stmt->bindParam(':id', $categoryId);
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($category) {
                // カテゴリに属するサンプル一覧の取得
                $stmt = $conn->prepare("SELECT * FROM AISampleInfo WHERE category_id = :category_id ORDER BY id DESC LIMIT 10");
                $stmt->bindParam(':category_id', $categoryId);
                $stmt->execute();
                $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            // 未分類のサンプル一覧の取得
            $stmt = $conn->query("SELECT * FROM AISampleInfo WHERE category_id IS NULL OR category_id = 0 ORDER BY id DESC LIMIT 10");
            $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $category = [
                'id' => 0,
                'name' => '未分類',
                'description' => 'カテゴリが設定されていないサンプル',
                'icon_key' => 'other'
            ];
        }
    } else {
        // すべてのサンプル一覧の取得
        $stmt = $conn->query("SELECT * FROM AISampleInfo ORDER BY id DESC LIMIT 10");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $category = [
            'id' => null,
            'name' => 'すべてのサンプル',
            'description' => '登録されているすべてのサンプル',
            'icon_key' => 'document'
        ];
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
    <title>カテゴリとサンプル（ステップ2）</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .error { color: red; padding: 10px; background-color: #ffeeee; border: 1px solid #ffcccc; margin-bottom: 20px; }
        .category-list { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .category-item { padding: 5px 10px; background-color: #f0f0f0; border-radius: 3px; text-decoration: none; color: #333; display: flex; align-items: center; }
        .category-item.active { background-color: #007bff; color: white; }
        .category-icon { margin-right: 5px; }
        .category-header { display: flex; align-items: center; margin-bottom: 20px; }
        .category-header-icon { font-size: 2em; margin-right: 15px; }
        .category-header-info h1 { margin: 0 0 5px 0; }
        .category-header-info p { margin: 0; color: #666; }
        .sample-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .sample-card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; background-color: #fff; }
        .sample-title { margin-top: 0; margin-bottom: 10px; }
        .sample-meta { display: flex; justify-content: space-between; font-size: 0.9em; color: #666; margin-bottom: 10px; }
        .sample-prompt { background-color: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 0.9em; margin-bottom: 10px; max-height: 100px; overflow-y: auto; }
        .sample-footer { text-align: right; }
        .btn { display: inline-block; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px; }
        .btn:hover { background-color: #0056b3; }
        .empty-message { text-align: center; padding: 30px; background-color: #f5f5f5; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>カテゴリとサンプル（ステップ2）</h1>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <h2>カテゴリ一覧</h2>
    <div class="category-list">
        <a href="category_step2.php" class="category-item <?php echo $categoryId === null ? 'active' : ''; ?>">
            <span class="category-icon">📋</span> すべてのサンプル (<?php echo $totalSamplesCount; ?>)
        </a>
        <a href="category_step2.php?id=0" class="category-item <?php echo $categoryId === 0 ? 'active' : ''; ?>">
            <span class="category-icon">📌</span> 未分類 (<?php echo $uncategorizedCount; ?>)
        </a>
        
        <?php foreach ($categories as $cat): ?>
            <?php 
            $catIcon = isset($cat['icon_key']) && isset($icons[$cat['icon_key']]) 
                ? $icons[$cat['icon_key']] 
                : $icons['other'];
            ?>
            <a href="category_step2.php?id=<?php echo $cat['id']; ?>" 
               class="category-item <?php echo $categoryId === (int)$cat['id'] ? 'active' : ''; ?>">
                <span class="category-icon"><?php echo $catIcon; ?></span>
                <?php echo htmlspecialchars($cat['name']); ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <?php if ($category): ?>
        <?php 
        $categoryIcon = isset($category['icon_key']) && isset($icons[$category['icon_key']]) 
            ? $icons[$category['icon_key']] 
            : $icons['other'];
        ?>
        <div class="category-header">
            <div class="category-header-icon"><?php echo $categoryIcon; ?></div>
            <div class="category-header-info">
                <h1><?php echo htmlspecialchars($category['name']); ?></h1>
                <p><?php echo htmlspecialchars($category['description']); ?></p>
            </div>
        </div>
        
        <h2>サンプル一覧 (<?php echo count($samples); ?>件)</h2>
        
        <?php if (empty($samples)): ?>
            <div class="empty-message">このカテゴリにはサンプルがありません。</div>
        <?php else: ?>
            <div class="sample-grid">
                <?php foreach ($samples as $sample): ?>
                    <div class="sample-card">
                        <h3 class="sample-title"><?php echo htmlspecialchars($sample['Title']); ?></h3>
                        <div class="sample-meta">
                            <div>AI: <?php echo htmlspecialchars($sample['AiName']); ?></div>
                            <div>製品: <?php echo htmlspecialchars($sample['Product']); ?></div>
                        </div>
                        <div class="sample-prompt">
                            <?php 
                            $prompt = htmlspecialchars($sample['Prompt']);
                            echo strlen($prompt) > 150 ? substr($prompt, 0, 150) . '...' : $prompt;
                            ?>
                        </div>
                        <div class="sample-footer">
                            <a href="/AI_Sample/View.php?id=<?php echo $sample['id']; ?>" class="btn">詳細を見る</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <p style="margin-top: 30px;">
        <a href="category_manager_updated.php">カテゴリ管理に戻る</a>
    </p>
</body>
</html>