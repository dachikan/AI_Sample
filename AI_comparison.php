<?php
require_once 'db_connect_extended.php';

$selectedIds = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];
$selectedIds = array_filter(array_map('intval', $selectedIds));

$services = [];
foreach ($selectedIds as $id) {
    $service = getAIServiceById($id);
    if ($service) {
        $services[] = $service;
    }
}

$pageTitle = 'AIサービス比較';
include 'includes/header.php';

$allServices = getAllAIServices();
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="fas fa-balance-scale me-2"></i>AIサービス比較</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5>比較するサービスを選択</h5>
                <form method="GET" action="">
                    <div class="row">
                        <?php foreach ($allServices as $service): ?>
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="ids[]" value="<?php echo $service['id']; ?>"
                                       id="service_<?php echo $service['id']; ?>"
                                       <?php echo in_array($service['id'], $selectedIds) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="service_<?php echo $service['id']; ?>">
                                    <?php echo htmlspecialchars($service['ai_service']); ?>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">比較する</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($services)): ?>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>項目</th>
                <?php foreach ($services as $service): ?>
                <th class="text-center" style="min-width: 200px;">
                    <img src="images/<?php echo htmlspecialchars($service['ai_icon']); ?>" 
                         alt="<?php echo htmlspecialchars($service['ai_service']); ?>" 
                         class="ai-icon-small mb-2"
                         onerror="this.src='images/default-ai-icon.png'"><br>
                    <?php echo htmlspecialchars($service['ai_service']); ?>
                </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>会社名</strong></td>
                <?php foreach ($services as $service): ?>
                <td><?php echo htmlspecialchars($service['company_name']); ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>概要</strong></td>
                <?php foreach ($services as $service): ?>
                <td><?php echo htmlspecialchars($service['description']); ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>人気度</strong></td>
                <?php foreach ($services as $service): ?>
                <td class="text-center">
                    <span class="popularity-score"><?php echo $service['popularity_score']; ?>点</span>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>料金モデル</strong></td>
                <?php foreach ($services as $service): ?>
                <td class="text-center">
                    <span class="badge bg-<?php echo $service['pricing_model'] == 'free' ? 'success' : ($service['pricing_model'] == 'freemium' ? 'info' : 'warning'); ?>">
                        <?php echo htmlspecialchars($service['pricing_model']); ?>
                    </span>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>無料プラン</strong></td>
                <?php foreach ($services as $service): ?>
                <td class="text-center"><?php echo $service['free_tier_available'] ? '✅' : '❌'; ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>API利用</strong></td>
                <?php foreach ($services as $service): ?>
                <td class="text-center"><?php echo $service['api_available'] ? '✅' : '❌'; ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>強み</strong></td>
                <?php foreach ($services as $service): ?>
                <td><?php echo htmlspecialchars($service['strengths']); ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>制限事項</strong></td>
                <?php foreach ($services as $service): ?>
                <td><?php echo htmlspecialchars($service['limitations']); ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>詳細</strong></td>
                <?php foreach ($services as $service): ?>
                <td class="text-center">
                    <a href="AI_detail.php?id=<?php echo $service['id']; ?>" class="btn btn-primary btn-sm">
                        詳細を見る
                    </a>
                </td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>比較するサービスを選択してください。
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
