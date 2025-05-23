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
$samples = [];
$categories = [];
$conn = null;

// 上級者モードの設定
$showAdvanced = isset($_GET['advanced']) && $_GET['advanced'] == '1';

// カテゴリIDの取得
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : null;

try {
    // データベース接続
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // カテゴリ一覧の取得
    $stmt = $conn->query("SELECT * FROM AISampleCategories ORDER BY display_order, name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // サンプル一覧の取得（上級者モードとカテゴリに応じて）
    $query = "SELECT s.*, c.name as category_name, c.icon_key 
              FROM AISampleInfo s 
              LEFT JOIN AISampleCategories c ON s.category_id = c.id";
    
    $whereConditions = [];
    
    // 上級者モードでない場合は、上級者向けサンプルを除外
    if (!$showAdvanced) {
        $whereConditions[] = "s.is_advanced = 0";
    }
    
    // カテゴリが指定されている場合は、そのカテゴリのサンプルのみ表示
    if ($categoryId) {
        $whereConditions[] = "s.category_id = " . $categoryId;
    }
    
    // WHERE句の構築
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $query .= " ORDER BY s.id DESC";
    
    $stmt = $conn->query($query);
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    <title>老人向けＡＩ活用サンプル一覧</title>
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
        
        .error {
            padding: 15px;
            margin: 20px 0;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 4px;
            border: 1px solid #f5c6cb;
        }
        
        .warning {
            padding: 15px;
            margin: 20px 0;
            background-color: #fff3cd;
            color: #856404;
            border-radius: 4px;
            border: 1px solid #ffeeba;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--secondary-color);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
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
        
        .btn-warning {
            background-color: var(--warning-color);
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
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
        
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: var(--light-color);
            border-radius: 8px;
        }
        
        .filter-label {
            font-weight: bold;
            margin-right: 10px;
        }
        
        .filter-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-category {
            display: flex;
            align-items: center;
        }
        
        .filter-category select {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            margin-left: 10px;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background-color: var(--light-color);
            font-weight: bold;
        }
        
        tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .category-badge, .advanced-badge, .intermediate-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            margin-right: 10px;
        }
        
        .category-badge {
            background-color: var(--light-color);
            color: var(--text-color);
        }
        
        .advanced-badge {
            background-color: var(--danger-color);
            color: white;
        }
        
        .intermediate-badge {
            background-color: var(--warning-color);
            color: white;
        }
        
        .category-icon {
            margin-right: 5px;
            font-size: 1.2em;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--text-muted);
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
            
            .filter-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-options {
                margin-top: 10px;
                width: 100%;
            }
            
            .filter-category {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .filter-category select {
                width: 100%;
                margin-left: 0;
                margin-top: 5px;
            }
            
            .btn {
                margin-bottom: 10px;
            }
            
            th, td {
                padding: 8px 10px;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">
            <h1>老人向けＡＩ活用サンプル一覧</h1>
            <div class="header-description">老人向けＡＩプロンプトのサンプル集</div>
        </div>
        <div class="header-actions">
            <a href="AISample_Register.php" class="btn btn-primary">新規サンプル登録</a>
            <a href="AISample_main.php" class="btn btn-success">メイン画面に戻る</a>
            <?php if ($showAdvanced): ?>
                <a href="?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?>advanced=0" class="btn">一般向けモードに切替</a>
            <?php else: ?>
                <a href="?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?>advanced=1" class="btn btn-warning">上級者モードに切替</a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($showAdvanced): ?>
        <div class="warning">
            <strong>上級者モード:</strong> 現在、v0.devやGrokなどの高度なAIツールのサンプルも含めて表示しています。これらは一般の方には難しい場合があります。
        </div>
    <?php endif; ?>
    
    <div class="filter-bar">
        <div class="filter-mode">
            <span class="filter-label">表示モード:</span>
            <?php if ($showAdvanced): ?>
                上級者向けサンプルを含む
            <?php else: ?>
                一般向けサンプルのみ表示しています
            <?php endif; ?>
        </div>
        <div class="filter-category">
            <span class="filter-label">プロンプトカテゴリ:</span>
            <select onchange="location = this.value;">
                <option value="?<?php echo $showAdvanced ? 'advanced=1' : ''; ?>">すべて</option>
                <?php foreach ($categories as $category): ?>
                    <option value="?category=<?php echo $category['id']; ?><?php echo $showAdvanced ? '&advanced=1' : ''; ?>" <?php echo $categoryId == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div class="container">
        <a href="AISample_Register.php" class="btn btn-primary" style="margin-bottom: 20px;">+ 新規登録</a>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>AI名</th>
                        <th>カテゴリ</th>
                        <th>登録日</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($samples)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">表示できるサンプルはありません。</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($samples as $sample): ?>
                            <?php 
                            $isAdvancedSample = isset($sample['is_advanced']) && $sample['is_advanced'] == 1;
                            $isIntermediateSample = isset($sample['needs_modification']) && $sample['needs_modification'] == 1;
                            $categoryIcon = '📌'; // デフォルトアイコン
                            if (isset($sample['icon_key']) && isset($icons[$sample['icon_key']])) {
                                $categoryIcon = $icons[$sample['icon_key']];
                            } elseif (isset($sample['category_id']) && $sample['category_id'] > 0) {
                                $categoryIcon = $icons['other'];
                            }
                            ?>
                            <tr>
                                <td><?php echo $sample['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($sample['Title']); ?>
                                    <?php if ($isAdvancedSample): ?>
                                        <span class="advanced-badge">上級</span>
                                    <?php elseif ($isIntermediateSample): ?>
                                        <span class="intermediate-badge">中級</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($sample['AiName']); ?></td>
                                <td>
                                    <?php if (isset($sample['category_id']) && $sample['category_id'] > 0 && isset($sample['category_name'])): ?>
                                        <span class="category-badge">
                                            <span class="category-icon"><?php echo $categoryIcon; ?></span>
                                            <?php echo htmlspecialchars($sample['category_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="category-badge">
                                            <span class="category-icon">📌</span>
                                            未分類
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    // RegisterDateが存在するか確認してから処理
                                    if (isset($sample['RegisterDate']) && $sample['RegisterDate']) {
                                        echo date('Y-m-d', strtotime($sample['RegisterDate']));
                                    } else {
                                        echo '不明';
                                    }
                                    ?>
                                </td>
                                <td class="actions">
                                    <a href="AISample_main.php?id=<?php echo $sample['id']; ?><?php echo $showAdvanced ? '&advanced=1' : ''; ?>" class="btn btn-primary">詳細</a>
                                    <a href="AISample_Edit.php?id=<?php echo $sample['id']; ?>" class="btn btn-success">編集</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> 初心者向けAI活用サンプル集</p>
    </div>
</body>
</html>
