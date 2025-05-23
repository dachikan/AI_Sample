<?php
session_start();
// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 以下、既存のコード
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

// 上級者モードの確認
$advancedMode = isset($_GET['advanced']) && $_GET['advanced'] == '1';

// 表示モードの設定（デフォルトはカード表示）
$viewMode = isset($_GET['view']) ? $_GET['view'] : 'card';
if ($viewMode !== 'list' && $viewMode !== 'card') {
    $viewMode = 'card';
}

// カテゴリフィルター
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : null;
$whereClause = $categoryFilter ? "WHERE category_id = :category_id" : "";

// 検索フィルター
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($searchQuery)) {
    $whereClause = $whereClause ? $whereClause . " AND " : "WHERE ";
    $whereClause .= "(Title LIKE :search OR Prompt LIKE :search OR AiName LIKE :search)";
}

// サンプル一覧を取得
try {
    $query = "
        SELECT id, Title, AiName, category_id, Prompt, InputImagePath, OutputImagePath, created_at, updated_at 
        FROM AISampleInfo 
        $whereClause
        ORDER BY created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    
    if ($categoryFilter) {
        $stmt->bindParam(':category_id', $categoryFilter, PDO::PARAM_INT);
    }
    
    if (!empty($searchQuery)) {
        $searchParam = "%" . $searchQuery . "%";
        $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "データベースエラー: " . $e->getMessage();
}

// 表示モードを切り替えるURLを生成
function getViewModeUrl($mode) {
    $params = $_GET;
    $params['view'] = $mode;
    return '?' . http_build_query($params);
}

// カテゴリ名を取得する関数
function getCategoryName($categoryId, $categories) {
    foreach ($categories as $category) {
        if ($category['CategoryID'] == $categoryId) {
            return $category['CategoryName'];
        }
    }
    return "未分類";
}

// アイコンを取得する関数
function getCategoryIcon($categoryName) {
    $icons = [
        '文書作成' => '📝',
        '画像生成' => '🖼️',
        '会計管理' => '📊',
        'イベント企画' => '🎪',
        '情報発信' => '📢',
        '防災対策' => '🚨',
        'コミュニティ活性化' => '👥',
        '家庭内' => '🏠',
        'その他' => '🔍'
    ];
    return isset($icons[$categoryName]) ? $icons[$categoryName] : '📋';
}

// 削除メッセージの確認
//session_start();
$deleteMessage = null;
$deleteError = null;

if (isset($_SESSION['delete_success']) && $_SESSION['delete_success']) {
    $deleteMessage = isset($_SESSION['delete_message']) ? $_SESSION['delete_message'] : "サンプルを削除しました。";
    unset($_SESSION['delete_success']);
    unset($_SESSION['delete_message']);
}

if (isset($_SESSION['delete_error'])) {
    $deleteError = $_SESSION['delete_error'];
    unset($_SESSION['delete_error']);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIサンプル一覧 | AI活用サンプル集</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/prompt-fix.css">
    <style>
        .view-toggle {
            margin-bottom: 20px;
        }
        .view-toggle .btn {
            padding: 5px 15px;
        }
        .view-toggle .active {
            background-color: #0d6efd;
            color: white;
        }
        .list-view .sample-item {
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .list-view .sample-item:hover {
            background-color: #f8f9fa;
        }
        .card-view .card {
            height: 100%;
            transition: transform 0.2s;
        }
        .card-view .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .search-form {
            margin-bottom: 20px;
        }
        .sample-meta {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .sample-prompt {
            max-height: 80px;
            overflow: hidden;
        }
        .success-alert {
            animation: fadeOut 5s forwards;
            animation-delay: 3s;
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; visibility: hidden; }
        }
    </style>
</head>
<body>
    <header class="bg-light py-3">
        <div class="container">
            <h1 class="text-center">AIサンプル一覧</h1>
            <p class="text-center text-muted">初心者に役立つAIプロンプトのサンプル集</p>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="index.php<?php echo $advancedMode ? '?advanced=1' : ''; ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> トップに戻る
                </a>
                <div>
                    <a href="AISample-form.php" class="btn btn-primary">新規サンプル登録</a>
                    <?php if ($advancedMode): ?>
                        <a href="AISampleList-unified.php<?php echo $categoryFilter ? '?category=' . $categoryFilter : ''; ?>" class="btn btn-secondary">一般向けモードに切替</a>
                    <?php else: ?>
                        <a href="AISampleList-unified.php?advanced=1<?php echo $categoryFilter ? '&category=' . $categoryFilter : ''; ?>" class="btn btn-secondary">上級者モードに切替</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <?php if ($advancedMode): ?>
    <div class="container mt-3">
        <div class="alert alert-warning">
            上級者モード: 現在、v0.devやGrokなどの高度なAIツールのサンプルも含めて表示しています。これらは一般の方には難しい場合があります。
        </div>
    </div>
    <?php endif; ?>

    <div class="container mt-4">
        <?php if (isset($deleteMessage)): ?>
            <div class="alert alert-success success-alert">
                <?php echo $deleteMessage; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($deleteError)): ?>
            <div class="alert alert-danger">
                <?php echo $deleteError; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- サイドバー -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>プロンプトカテゴリ</h5>
                    </div>
                    <div class="card-body">
                        <h6>AIプロンプトの用途別分類</h6>
                        <ul class="list-group mt-3">
                            <li class="list-group-item d-flex align-items-center">
                                <span class="me-2">📋</span>
                                <a href="AISampleList-unified.php<?php echo $advancedMode ? '?advanced=1' : ''; ?><?php echo $viewMode !== 'card' ? '&view=' . $viewMode : ''; ?>" class="text-decoration-none">すべて</a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                            <li class="list-group-item d-flex align-items-center">
                                <?php 
                                $icon = getCategoryIcon($category['CategoryName']);
                                ?>
                                <span class="me-2"><?php echo $icon; ?></span>
                                <a href="AISampleList-unified.php?category=<?php echo $category['CategoryID']; ?><?php echo $advancedMode ? '&advanced=1' : ''; ?><?php echo $viewMode !== 'card' ? '&view=' . $viewMode : ''; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($category['CategoryName']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>AIサービスタイプ</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item d-flex align-items-center">
                                <span class="me-2">🤖</span>
                                <span>テキスト生成AI</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <span class="me-2">🎨</span>
                                <span>画像生成AI</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <span class="me-2">🎵</span>
                                <span>音声・音楽生成AI</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- メインコンテンツ -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <?php 
                        if ($categoryFilter) {
                            $categoryName = getCategoryName($categoryFilter, $categories);
                            echo htmlspecialchars($categoryName) . ' カテゴリのサンプル';
                        } else {
                            echo 'すべてのAIサンプル';
                        }
                        ?>
                    </h2>
                </div>

                <!-- 検索フォーム -->
                <div class="search-form">
                    <form action="AISampleList-unified.php" method="GET" class="d-flex">
                        <?php if ($advancedMode): ?>
                            <input type="hidden" name="advanced" value="1">
                        <?php endif; ?>
                        <?php if ($categoryFilter): ?>
                            <input type="hidden" name="category" value="<?php echo $categoryFilter; ?>">
                        <?php endif; ?>
                        <?php if ($viewMode !== 'card'): ?>
                            <input type="hidden" name="view" value="<?php echo $viewMode; ?>">
                        <?php endif; ?>
                        <input type="text" name="search" class="form-control me-2" placeholder="キーワードで検索..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit" class="btn btn-outline-primary">検索</button>
                    </form>
                </div>

                <!-- 表示モード切り替え -->
                <div class="view-toggle d-flex justify-content-end mb-3">
                    <div class="btn-group" role="group" aria-label="表示モード切り替え">
                        <a href="<?php echo getViewModeUrl('card'); ?>" class="btn btn-outline-secondary <?php echo $viewMode === 'card' ? 'active' : ''; ?>">
                            <i class="bi bi-grid-3x3-gap"></i> カード表示
                        </a>
                        <a href="<?php echo getViewModeUrl('list'); ?>" class="btn btn-outline-secondary <?php echo $viewMode === 'list' ? 'active' : ''; ?>">
                            <i class="bi bi-list-ul"></i> リスト表示
                        </a>
                    </div>
                </div>

                <?php if (isset($samples) && !empty($samples)): ?>
                    <?php if ($viewMode === 'card'): ?>
                        <!-- カード表示 -->
                        <div class="card-view">
                            <div class="row">
                                <?php foreach ($samples as $sample): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-header">
                                                <div class="d-flex align-items-center">
    <i class="bi bi-card-text me-2" style="width: 24px; height: 24px;"></i>
    <h5 class="card-title mb-0"><?php echo htmlspecialchars($sample['Title']); ?></h5>
</div>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-2">
                                                    <span class="badge bg-primary">AI: <?php echo htmlspecialchars($sample['AiName']); ?></span>
                                                    <span class="badge bg-secondary">カテゴリ: <?php echo getCategoryName($sample['category_id'], $categories); ?></span>
                                                </div>
                                                
                                                <div class="sample-prompt">
                                                    <?php 
                                                    if (isset($sample['Prompt']) && !empty($sample['Prompt'])) {
                                                        $prompt = htmlspecialchars($sample['Prompt']);
                                                        echo strlen($prompt) > 150 ? substr($prompt, 0, 150) . '...' : $prompt;
                                                    } else {
                                                        echo "プロンプトなし";
                                                    }
                                                    ?>
                                                </div>
                                                
                                                <div class="sample-meta mt-2">
                                                    <div>作成日: <?php echo date('Y年m月d日', strtotime($sample['created_at'])); ?></div>
                                                    <?php if ($sample['updated_at']): ?>
                                                        <div>更新日: <?php echo date('Y年m月d日', strtotime($sample['updated_at'])); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="card-footer d-flex justify-content-between">
                                                <a href="AISample-Detail.php?id=<?php echo $sample['id']; ?>" class="btn btn-sm btn-outline-primary">詳細を見る</a>
                                                <a href="AISample-form.php?id=<?php echo $sample['id']; ?>" class="btn btn-sm btn-outline-secondary">編集</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- リスト表示 -->
                        <div class="list-view">
                            <div class="list-group">
                                <?php foreach ($samples as $sample): ?>
                                    <div class="list-group-item sample-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="mb-1"><?php echo htmlspecialchars($sample['Title']); ?></h5>
                                                <div class="mb-2">
                                                    <span class="badge bg-primary">AI: <?php echo htmlspecialchars($sample['AiName']); ?></span>
                                                    <span class="badge bg-secondary">カテゴリ: <?php echo getCategoryName($sample['category_id'], $categories); ?></span>
                                                </div>
                                                <div class="sample-prompt small">
                                                    <?php 
                                                    if (isset($sample['Prompt']) && !empty($sample['Prompt'])) {
                                                        $prompt = htmlspecialchars($sample['Prompt']);
                                                        echo strlen($prompt) > 100 ? substr($prompt, 0, 100) . '...' : $prompt;
                                                    } else {
                                                        echo "プロンプトなし";
                                                    }
                                                    ?>
                                                </div>
                                                <div class="sample-meta mt-1">
                                                    作成日: <?php echo date('Y年m月d日', strtotime($sample['created_at'])); ?>
                                                    <?php if ($sample['updated_at']): ?>
                                                        | 更新日: <?php echo date('Y年m月d日', strtotime($sample['updated_at'])); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column align-items-end">
                                                <div class="btn-group mb-2">
                                                    <a href="AISample-Detail.php?id=<?php echo $sample['id']; ?>" class="btn btn-sm btn-outline-primary">詳細</a>
                                                    <a href="AISample-form.php?id=<?php echo $sample['id']; ?>" class="btn btn-sm btn-outline-secondary">編集</a>
                                                </div>
                                                <?php if ($sample['InputImagePath'] || $sample['OutputImagePath']): ?>
                                                    <div class="text-muted small">
                                                        <i class="bi bi-image"></i> 画像あり
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <?php if (!empty($searchQuery)): ?>
                            「<?php echo htmlspecialchars($searchQuery); ?>」に一致するサンプルが見つかりませんでした。
                        <?php else: ?>
                            表示できるサンプルがありません。
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
