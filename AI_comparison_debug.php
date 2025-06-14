<?php
// エラー表示を有効化
error_reporting(E_ALL);
ini_set('display_errors', 1);

// デバッグ情報を表示
echo "<!-- デバッグ開始 -->\n";
echo "<!-- URLパラメータ: " . htmlspecialchars(print_r($_GET, true)) . " -->\n";

try {
    // データベース接続ファイルの読み込み
    echo "<!-- db_connect_extended.php 読み込み開始 -->\n";
    require_once 'db_connect_extended.php';
    echo "<!-- db_connect_extended.php 読み込み完了 -->\n";

    // IDパラメータの処理
    $selectedIds = isset($_GET['ids']) ? $_GET['ids'] : [];
    echo "<!-- 選択されたID: " . htmlspecialchars(print_r($selectedIds, true)) . " -->\n";
    
    // 配列でない場合は配列に変換
    if (!is_array($selectedIds)) {
        $selectedIds = explode(',', $selectedIds);
    }
    
    // 整数値に変換してフィルタリング
    $selectedIds = array_filter(array_map('intval', $selectedIds));
    echo "<!-- 処理後のID: " . htmlspecialchars(print_r($selectedIds, true)) . " -->\n";

    // サービス情報の取得
    $services = [];
    foreach ($selectedIds as $id) {
        echo "<!-- ID $id の情報取得開始 -->\n";
        $service = getAIServiceById($id);
        if ($service) {
            $services[] = $service;
            echo "<!-- ID $id の情報取得成功 -->\n";
        } else {
            echo "<!-- ID $id の情報取得失敗 -->\n";
        }
    }

    $pageTitle = 'AIサービス比較（デバッグ版）';
    include 'includes/header.php';

    // 全サービス取得
    echo "<!-- 全サービス取得開始 -->\n";
    $allServices = getAllAIServices();
    echo "<!-- 全サービス取得完了: " . count($allServices) . "件 -->\n";

} catch (Exception $e) {
    // エラー情報を表示
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px;'>";
    echo "<h3>エラーが発生しました</h3>";
    echo "<p><strong>メッセージ:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>ファイル:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>行番号:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>スタックトレース:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
    exit;
}
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="fas fa-balance-scale me-2"></i>AIサービス比較（デバッグ版）</h1>
            
            <div class="alert alert-info">
                <h4>デバッグ情報</h4>
                <p>選択されたID: <?php echo implode(', ', $selectedIds); ?></p>
                <p>取得されたサービス数: <?php echo count($services); ?></p>
                <p>全サービス数: <?php echo count($allServices); ?></p>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5>比較するサービスを選択</h5>
                    <form method="GET" action="">
                        <div class="row">
                            <?php 
                            if (!empty($allServices)):
                                foreach ($allServices as $service): 
                            ?>
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
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    サービス情報が取得できませんでした。データベース接続を確認してください。
                                </div>
                            </div>
                            <?php endif; ?>
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
                        <?php if (isset($service['ai_icon']) && !empty($service['ai_icon'])): ?>
                        <div style="width: 32px; height: 32px; margin: 0 auto 8px; background-color: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-robot"></i>
                        </div>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($service['ai_service'] ?? 'Unknown'); ?>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>会社名</strong></td>
                    <?php foreach ($services as $service): ?>
                    <td><?php echo htmlspecialchars($service['company_name'] ?? 'N/A'); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>概要</strong></td>
                    <?php foreach ($services as $service): ?>
                    <td><?php echo htmlspecialchars($service['description'] ?? 'N/A'); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>人気度</strong></td>
                    <?php foreach ($services as $service): ?>
                    <td class="text-center">
                        <?php if (isset($service['popularity_score'])): ?>
                        <span class="badge bg-primary"><?php echo $service['popularity_score']; ?>点</span>
                        <?php else: ?>
                        N/A
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>料金モデル</strong></td>
                    <?php foreach ($services as $service): ?>
                    <td class="text-center">
                        <?php if (isset($service['pricing_model'])): ?>
                        <span class="badge bg-<?php echo $service['pricing_model'] == 'free' ? 'success' : ($service['pricing_model'] == 'freemium' ? 'info' : 'warning'); ?>">
                            <?php echo htmlspecialchars($service['pricing_model']); ?>
                        </span>
                        <?php else: ?>
                        N/A
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><strong>詳細</strong></td>
                    <?php foreach ($services as $service): ?>
                    <td class="text-center">
                        <?php if (isset($service['id'])): ?>
                        <a href="AI_detail.php?id=<?php echo $service['id']; ?>" class="btn btn-primary btn-sm">
                            詳細を見る
                        </a>
                        <?php else: ?>
                        N/A
                        <?php endif; ?>
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
</div>

<?php include 'includes/footer.php'; ?>
