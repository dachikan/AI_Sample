<?php
require_once 'db_connect_extended.php';

$aiTypeId = isset($_GET['type']) ? (int)$_GET['type'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

if ($aiTypeId) {
    $services = getAIServicesByType($aiTypeId);
    $totalCount = count($services);
    $services = array_slice($services, $offset, $limit);
    $pageTitle = 'AI一覧 - タイプ別表示';
} else {
    $services = getAllAIServices($limit, $offset);
    $totalCount = getAIServiceCount();
    $pageTitle = 'AI一覧 - 全サービス';
}

$totalPages = ceil($totalCount / $limit);

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="fas fa-list me-2"></i>AIサービス一覧</h1>
        <p class="text-muted">全 <?php echo $totalCount; ?> サービス</p>
        
        <?php if ($aiTypeId): ?>
        <div class="alert alert-info">
            <i class="fas fa-filter me-2"></i>タイプ別フィルター適用中
            <a href="AI_list.php" class="btn btn-sm btn-outline-secondary ms-2">フィルターを解除</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (count($services) == 1): ?>
    <!-- 1個の場合：詳細表示 -->
    <?php $service = $services[0]; ?>
    <div class="detail-section">
        <div class="row">
            <div class="col-md-3 text-center">
                <img src="images/<?php echo htmlspecialchars($service['ai_icon']); ?>" 
                     alt="<?php echo htmlspecialchars($service['ai_service']); ?>" 
                     class="img-fluid mb-3"
                     style="max-width: 150px;"
                     onerror="this.src='images/default-ai-icon.png'">
                <h3><?php echo htmlspecialchars($service['ai_service']); ?></h3>
                <p class="text-muted"><?php echo htmlspecialchars($service['company_name']); ?></p>
            </div>
            <div class="col-md-9">
                <h4>概要</h4>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>強み</h5>
                        <p><?php echo htmlspecialchars($service['strengths']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>制限事項</h5>
                        <p><?php echo htmlspecialchars($service['limitations']); ?></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <strong>モデル名:</strong> <?php echo htmlspecialchars($service['model_name']); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>料金モデル:</strong> 
                        <span class="badge bg-info"><?php echo htmlspecialchars($service['pricing_model']); ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong>人気度:</strong> 
                        <span class="popularity-score"><?php echo $service['popularity_score']; ?>点</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif (count($services) <= 10): ?>
    <!-- 10個以内の場合：カード形式 -->
    <div class="row">
        <?php foreach ($services as $service): ?>
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
    <!-- 11個以上の場合：アイコンと名称のリスト -->
    <div class="row">
        <?php foreach ($services as $service): ?>
        <div class="col-md-6 col-lg-4 mb-2">
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center">
                        <img src="images/<?php echo htmlspecialchars($service['ai_icon']); ?>" 
                             alt="<?php echo htmlspecialchars($service['ai_service']); ?>" 
                             class="ai-icon-small me-3"
                             onerror="this.src='images/default-ai-icon.png'">
                        <div class="flex-grow-1">
                            <h6 class="mb-0"><?php echo htmlspecialchars($service['ai_service']); ?></h6>
                            <small class="text-muted"><?php echo htmlspecialchars($service['company_name']); ?></small>
                        </div>
                        <a href="AI_detail.php?id=<?php echo $service['id']; ?>" class="btn btn-outline-primary btn-sm">
                            詳細
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ページネーション -->
<?php if ($totalPages > 1): ?>
<nav aria-label="ページネーション">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $aiTypeId ? '&type=' . $aiTypeId : ''; ?>">
                <?php echo $i; ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
