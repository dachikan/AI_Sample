<?php
/**
 * 修正版AI比較ページ
 * - URLパラメータの適切な処理
 * - 存在しないIDの処理
 * - エラーハンドリングの追加
 */

// データベース接続
include "db_connect_extended.php";

// URLパラメータからIDを取得
$selectedIds = [];
if (isset($_GET["ids"]) && is_array($_GET["ids"])) {
    $selectedIds = array_map("intval", $_GET["ids"]);
}

// サービスデータの定義
$services = [
    [
        "id" => 1,
        "ai_service" => "ChatGPT",
        "ai_icon" => "images/chatgpt-icon.png"
    ],
    [
        "id" => 2,
        "ai_service" => "Claude",
        "ai_icon" => "images/claude-icon.png"
    ],
    [
        "id" => 3,
        "ai_service" => "Gemini",
        "ai_icon" => "images/gemini-icon.png"
    ],
    [
        "id" => 4,
        "ai_service" => "Copilot",
        "ai_icon" => "images/copilot-icon.png"
    ],
    [
        "id" => 5,
        "ai_service" => "Perplexity",
        "ai_icon" => "images/perplexity-icon.png"
    ]
];

// 有効なIDのみをフィルタリング
$validSelectedIds = [];
foreach ($selectedIds as $id) {
    $found = false;
    foreach ($services as $service) {
        if ($service["id"] == $id) {
            $found = true;
            break;
        }
    }
    if ($found) {
        $validSelectedIds[] = $id;
    }
}
$selectedIds = $validSelectedIds;

// ヘッダーを含める
include "includes/header.php";
?>

<div class="container mt-4">
    <h1>AIサービス比較</h1>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    比較するAIサービスを選択
                </div>
                <div class="card-body">
                    <form method="get" action="AI_comparison.php">
                        <div class="row">
                            <?php foreach ($services as $service): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ids[]" value="<?php echo $service["id"]; ?>" id="service_<?php echo $service["id"]; ?>" <?php echo in_array($service["id"], $selectedIds) ? "checked" : ""; ?>>
                                        <label class="form-check-label" for="service_<?php echo $service["id"]; ?>">
                                            <?php if (file_exists($service["ai_icon"])): ?>
                                                <img src="<?php echo $service["ai_icon"]; ?>" alt="<?php echo htmlspecialchars($service["ai_service"]); ?>" style="width: 24px; height: 24px; margin-right: 5px;">
                                            <?php else: ?>
                                                <img src="images/default-ai-icon.png" alt="<?php echo htmlspecialchars($service["ai_service"]); ?>" style="width: 24px; height: 24px; margin-right: 5px;">
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($service["ai_service"]); ?>
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

    <?php if (!empty($selectedIds)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>項目</th>
                                <?php foreach ($selectedIds as $serviceId): ?>
                                    <?php 
                                    $serviceInfo = null;
                                    foreach ($services as $service) {
                                        if ($service["id"] == $serviceId) {
                                            $serviceInfo = $service;
                                            break;
                                        }
                                    }
                                    if ($serviceInfo): 
                                    ?>
                                        <th class="text-center" style="min-width: 200px;">
                                            <?php if (file_exists($serviceInfo["ai_icon"])): ?>
                                                <img src="<?php echo $serviceInfo["ai_icon"]; ?>" alt="<?php echo htmlspecialchars($serviceInfo["ai_service"]); ?>" style="width: 32px; height: 32px; margin-right: 5px;">
                                            <?php else: ?>
                                                <img src="images/default-ai-icon.png" alt="<?php echo htmlspecialchars($serviceInfo["ai_service"]); ?>" style="width: 32px; height: 32px; margin-right: 5px;">
                                            <?php endif; ?>
                                            <br>
                                            <?php echo htmlspecialchars($serviceInfo["ai_service"]); ?>
                                        </th>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>基本情報</td>
                                <?php foreach ($selectedIds as $serviceId): ?>
                                    <td>
                                        サンプルデータ: サービス<?php echo $serviceId; ?>の基本情報
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>料金プラン</td>
                                <?php foreach ($selectedIds as $serviceId): ?>
                                    <td>
                                        サンプルデータ: サービス<?php echo $serviceId; ?>の料金プラン
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>特徴</td>
                                <?php foreach ($selectedIds as $serviceId): ?>
                                    <td>
                                        サンプルデータ: サービス<?php echo $serviceId; ?>の特徴
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>対応言語</td>
                                <?php foreach ($selectedIds as $serviceId): ?>
                                    <td>
                                        サンプルデータ: サービス<?php echo $serviceId; ?>の対応言語
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>利用制限</td>
                                <?php foreach ($selectedIds as $serviceId): ?>
                                    <td>
                                        サンプルデータ: サービス<?php echo $serviceId; ?>の利用制限
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif (!empty($_GET["ids"])): ?>
        <div class="alert alert-warning">
            選択されたAIサービスが見つかりませんでした。有効なサービスを選択してください。
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
