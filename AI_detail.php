<?php
require_once 'db_connect_extended.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = getAIServiceById($id);

if (!$service) {
    header('Location: index.php');
    exit;
}

$pageTitle = htmlspecialchars($service['ai_service']) . ' - 詳細情報';
include 'includes/header.php';

$supportedLanguages = json_decode($service['supported_languages'], true) ?? [];
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="AI_index.php">ホーム</a></li>
                <li class="breadcrumb-item"><a href="AI_list.php">一覧</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($service['ai_service']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="detail-section">
    <div class="row">
        <div class="col-md-3 text-center">
            <img src="images/<?php echo htmlspecialchars($service['ai_icon']); ?>" 
                 alt="<?php echo htmlspecialchars($service['ai_service']); ?>" 
                 class="img-fluid mb-3"
                 style="max-width: 200px;"
                 onerror="this.src='images/default-ai-icon.png'">
            
            <div class="mb-3">
                <span class="brand-color" style="background-color: <?php echo htmlspecialchars($service['brand_color']); ?>"></span>
                <strong><?php echo htmlspecialchars($service['company_name']); ?></strong>
            </div>
            
            <div class="mb-3">
                <span class="popularity-score"><?php echo $service['popularity_score']; ?>点</span>
            </div>
            
            <?php if ($service['official_url']): ?>
            <a href="<?php echo htmlspecialchars($service['official_url']); ?>" 
               target="_blank" class="btn btn-primary mb-2">
                <i class="fas fa-external-link-alt me-2"></i>公式サイト
            </a>
            <?php endif; ?>
            
            <?php if ($service['launch_url']): ?>
            <a href="<?php echo str_replace('{prompt}', 'Hello', htmlspecialchars($service['launch_url'])); ?>" 
               target="_blank" class="btn btn-success mb-2">
                <i class="fas fa-rocket me-2"></i>試してみる
            </a>
            <?php endif; ?>
        </div>
        
        <div class="col-md-9">
            <h1><?php echo htmlspecialchars($service['ai_service']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($service['description']); ?></p>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h4><i class="fas fa-thumbs-up text-success me-2"></i>強み</h4>
                    <p><?php echo nl2br(htmlspecialchars($service['strengths'])); ?></p>
                </div>
                <div class="col-md-6">
                    <h4><i class="fas fa-exclamation-triangle text-warning me-2"></i>制限事項</h4>
                    <p><?php echo nl2br(htmlspecialchars($service['limitations'])); ?></p>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>技術仕様</h5>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>モデル名</strong></td>
                            <td><?php echo htmlspecialchars($service['model_name']); ?></td>
                        </tr>
                        <?php if ($service['max_tokens']): ?>
                        <tr>
                            <td><strong>最大トークン数</strong></td>
                            <td><?php echo number_format($service['max_tokens']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong>入力タイプ</strong></td>
                            <td><?php echo htmlspecialchars($service['input_types']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>出力タイプ</strong></td>
                            <td><?php echo htmlspecialchars($service['output_types']); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h5>料金・利用条件</h5>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>料金モデル</strong></td>
                            <td>
                                <span class="badge bg-<?php echo $service['pricing_model'] == 'free' ? 'success' : ($service['pricing_model'] == 'freemium' ? 'info' : 'warning'); ?>">
                                    <?php echo htmlspecialchars($service['pricing_model']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>無料プラン</strong></td>
                            <td><?php echo $service['free_tier_available'] ? '✅ あり' : '❌ なし'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>登録必須</strong></td>
                            <td><?php echo $service['registration_required'] ? '✅ 必要' : '❌ 不要'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>API利用</strong></td>
                            <td><?php echo $service['api_available'] ? '✅ 可能' : '❌ 不可'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <?php if (!empty($supportedLanguages)): ?>
            <div class="mb-4">
                <h5>対応言語</h5>
                <div>
                    <?php foreach ($supportedLanguages as $lang): ?>
                    <span class="badge bg-secondary me-1 mb-1"><?php echo htmlspecialchars($lang); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        <strong>バージョン:</strong> <?php echo htmlspecialchars($service['version']); ?><br>
                        <strong>リリース日:</strong> <?php echo date('Y年m月d日', strtotime($service['release_date'])); ?><br>
                        <strong>最終更新:</strong> <?php echo date('Y年m月d日', strtotime($service['last_updated_info'])); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
