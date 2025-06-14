<?php
require_once 'db_connect_extended.php';

$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($keyword) {
    $results = searchAIServices($keyword);
}

$pageTitle = '検索 - AI情報システム';
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="fas fa-search me-2"></i>AIサービス検索</h1>
        
        <form method="GET" action="" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="q" 
                       value="<?php echo htmlspecialchars($keyword); ?>" 
                       placeholder="AIサービス名、会社名、説明文で検索...">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> 検索
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($keyword): ?>
<div class="row mb-3">
    <div class="col-12">
        <h3>検索結果: "<?php echo htmlspecialchars($keyword); ?>"</h3>
        <p class="text-muted"><?php echo count($results); ?> 件見つかりました</p>
    </div>
</div>

<?php if (!empty($results)): ?>
<div class="row">
    <?php foreach ($results as $service): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card ai-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="images/<?php echo htmlspecialchars($service['ai_icon']); ?>" 
                         alt="<?php echo htmlspecialchars($service['ai_service']); ?>" 
                         class="ai-icon me-3"
                         onerror="this.src='images/default-ai-icon.png'">
                    <div>
                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($service['ai_service']); ?></h5>
                        <small class="text-muted"><?php echo htmlspecialchars($service['company_name']); ?></small>
                    </div>
                </div>
                <p class="card-text"><?php echo mb_substr(htmlspecialchars($service['description']), 0, 120); ?>...</p>
                <div class="mb-2">
                    <span class="badge pricing-badge bg-<?php echo $service['free_tier_available'] ? 'success' : 'warning'; ?>">
                        <?php echo $service['pricing_model']; ?>
                    </span>
                    <span class="popularity-score ms-2"><?php echo $service['popularity_score']; ?>点</span>
                </div>
                <a href="AI_detail.php?id=<?php echo $service['id']; ?>" class="btn btn-primary btn-sm">
                    詳細を見る
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    検索条件に一致するAIサービスが見つかりませんでした。
</div>
<?php endif; ?>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
