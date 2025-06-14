<?php
require_once 'db_connect_extended.php';

$topServices = getTopAIServices(20);
$pageTitle = 'AI人気ランキング';
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="fas fa-trophy me-2"></i>AI人気ランキング</h1>
        <p class="text-muted">人気度スコア順にAIサービスをランキング表示</p>
    </div>
</div>

<div class="row">
    <?php foreach ($topServices as $index => $service): ?>
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-1 text-center">
                        <h2 class="mb-0">
                            <?php if ($index == 0): ?>
                                <span class="text-warning"><i class="fas fa-crown"></i></span>
                            <?php elseif ($index == 1): ?>
                                <span class="text-secondary"><i class="fas fa-medal"></i></span>
                            <?php elseif ($index == 2): ?>
                                <span class="text-warning"><i class="fas fa-medal"></i></span>
                            <?php else: ?>
                                <span class="text-muted"><?php echo $index + 1; ?></span>
                            <?php endif; ?>
                        </h2>
                    </div>
                    <div class="col-md-2 text-center">
                        <img src="images/<?php echo htmlspecialchars($service['ai_icon']); ?>" 
                             alt="<?php echo htmlspecialchars($service['ai_service']); ?>" 
                             class="ai-icon"
                             onerror="this.src='images/default-ai-icon.png'">
                    </div>
                    <div class="col-md-6">
                        <h4><?php echo htmlspecialchars($service['ai_service']); ?></h4>
                        <p class="text-muted mb-1"><?php echo htmlspecialchars($service['company_name']); ?></p>
                        <p class="mb-2"><?php echo mb_substr(htmlspecialchars($service['description']), 0, 150); ?>...</p>
                        <span class="badge bg-<?php echo $service['pricing_model'] == 'free' ? 'success' : ($service['pricing_model'] == 'freemium' ? 'info' : 'warning'); ?>">
                            <?php echo htmlspecialchars($service['pricing_model']); ?>
                        </span>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="popularity-score mb-2"><?php echo $service['popularity_score']; ?>点</div>
                        <a href="AI_detail.php?id=<?php echo $service['id']; ?>" class="btn btn-primary btn-sm">
                            詳細を見る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
