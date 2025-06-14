<?php
/**
 * AI_comparison.phpのアイコン表示を最適化
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>アイコン表示最適化</title></head>";
echo "<body>";
echo "<h1>🖼️ AI比較ページのアイコン表示最適化</h1>";

$original_file = 'AI_comparison.php';
$backup_file = 'AI_comparison_backup_icons_' . date('Ymd_His') . '.php';

if (file_exists($original_file)) {
    // バックアップを作成
    copy($original_file, $backup_file);
    echo "<p style='color:green'>✓ バックアップ作成: $backup_file</p>";
    
    // アイコン表示を最適化したコンテンツ
    $new_content = '<?php
/**
 * 修正版AI比較ページ - アイコン表示最適化
 */

// データベース接続
include "db_connect_extended.php";

// URLパラメータからIDを取得
$selectedIds = [];
if (isset($_GET["ids"]) && is_array($_GET["ids"])) {
    $selectedIds = array_map("intval", $_GET["ids"]);
}

// サービスデータの定義（アイコン最適化）
$services = [
    [
        "id" => 1,
        "ai_service" => "ChatGPT",
        "ai_icon" => "images/chatgpt-icon.png",
        "brand_color" => "#10A37F",
        "basic_info" => "OpenAI開発の対話型AI<br>GPT-4技術を使用<br>2022年11月リリース",
        "pricing" => "<strong>無料:</strong> GPT-3.5使用<br><strong>Plus ($20/月):</strong> GPT-4使用<br><strong>Team ($25/月):</strong> チーム機能",
        "features" => "• 自然な対話<br>• コード生成<br>• 文章作成<br>• 画像生成（DALL-E）<br>• プラグイン対応",
        "languages" => "日本語、英語、中国語、<br>スペイン語、フランス語、<br>ドイツ語など50+言語",
        "limitations" => "<strong>無料:</strong> 1時間40回まで<br><strong>Plus:</strong> 3時間100回まで<br><strong>API:</strong> トークン制限"
    ],
    [
        "id" => 2,
        "ai_service" => "Claude",
        "ai_icon" => "images/claude-icon.png",
        "brand_color" => "#FF6B35",
        "basic_info" => "Anthropic開発のAI<br>Constitutional AI技術<br>2023年3月リリース",
        "pricing" => "<strong>無料:</strong> 基本機能<br><strong>Pro ($20/月):</strong> 優先アクセス<br><strong>Team ($25/月):</strong> チーム機能",
        "features" => "• 長文処理に優秀<br>• 安全性重視<br>• 文書分析<br>• コード理解<br>• ファイルアップロード",
        "languages" => "日本語、英語、中国語、<br>フランス語、スペイン語、<br>ドイツ語など40+言語",
        "limitations" => "<strong>無料:</strong> 1日数十回<br><strong>Pro:</strong> 5倍多く利用可能<br><strong>API:</strong> レート制限あり"
    ],
    [
        "id" => 3,
        "ai_service" => "Gemini",
        "ai_icon" => "images/gemini-icon.png",
        "brand_color" => "#4285F4",
        "basic_info" => "Google開発のAI<br>Gemini Pro技術<br>2023年12月リリース",
        "pricing" => "<strong>無料:</strong> Gemini Pro<br><strong>Advanced ($20/月):</strong> Ultra 1.0<br><strong>Business:</strong> 要相談",
        "features" => "• マルチモーダル<br>• Google検索連携<br>• Gmail/Docs統合<br>• リアルタイム情報<br>• 画像・動画理解",
        "languages" => "日本語、英語、中国語、<br>韓国語、ヒンディー語、<br>アラビア語など40+言語",
        "limitations" => "<strong>無料:</strong> 1分間60回<br><strong>Advanced:</strong> より高い制限<br><strong>API:</strong> クォータ制限"
    ],
    [
        "id" => 4,
        "ai_service" => "Copilot",
        "ai_icon" => "images/copilot-icon.png",
        "brand_color" => "#0078D4",
        "basic_info" => "Microsoft開発のAI<br>GPT-4 + Bing検索<br>2023年2月リリース",
        "pricing" => "<strong>無料:</strong> 基本機能<br><strong>Pro ($20/月):</strong> GPT-4 Turbo<br><strong>Enterprise:</strong> 要相談",
        "features" => "• Bing検索統合<br>• Office 365連携<br>• 画像生成（DALL-E）<br>• ウェブ情報取得<br>• Microsoft製品統合",
        "languages" => "日本語、英語、中国語、<br>フランス語、ドイツ語、<br>スペイン語など30+言語",
        "limitations" => "<strong>無料:</strong> 1日30回<br><strong>Pro:</strong> 1日300回<br><strong>API:</strong> Azure経由"
    ],
    [
        "id" => 5,
        "ai_service" => "Perplexity",
        "ai_icon" => "images/perplexity-icon.png",
        "brand_color" => "#20C997",
        "basic_info" => "Perplexity AI開発<br>検索特化型AI<br>2022年8月リリース",
        "pricing" => "<strong>無料:</strong> 基本検索<br><strong>Pro ($20/月):</strong> GPT-4使用<br><strong>Enterprise:</strong> 要相談",
        "features" => "• リアルタイム検索<br>• 情報源表示<br>• 学術論文検索<br>• ファクトチェック<br>• 引用機能",
        "languages" => "日本語、英語、中国語、<br>フランス語、ドイツ語、<br>スペイン語など25+言語",
        "limitations" => "<strong>無料:</strong> 1日5回のPro検索<br><strong>Pro:</strong> 1日300回のPro検索<br><strong>API:</strong> 制限あり"
    ]
];

// 有効なIDのみをフィルタリング
$validSelectedServices = [];
foreach ($selectedIds as $id) {
    foreach ($services as $service) {
        if ($service["id"] == $id) {
            $validSelectedServices[] = $service;
            break;
        }
    }
}

// ヘッダーを含める
include "includes/header.php";
?>

<style>
.ai-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    margin-right: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.ai-icon-large {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    margin-right: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.service-header {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    padding: 10px;
}
.brand-accent {
    border-left: 4px solid;
    padding-left: 12px;
}
</style>

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
                                        <input class="form-check-input" type="checkbox" name="ids[]" value="<?php echo $service["id"]; ?>" id="service_<?php echo $service["id"]; ?>" <?php echo in_array($service["id"], array_column($validSelectedServices, "id")) ? "checked" : ""; ?>>
                                        <label class="form-check-label" for="service_<?php echo $service["id"]; ?>">
                                            <?php if (file_exists($service["ai_icon"])): ?>
                                                <img src="<?php echo $service["ai_icon"]; ?>" alt="<?php echo htmlspecialchars($service["ai_service"]); ?>" class="ai-icon">
                                            <?php else: ?>
                                                <img src="images/default-ai-icon.png" alt="<?php echo htmlspecialchars($service["ai_service"]); ?>" class="ai-icon" onerror="this.style.display=\'none\'">
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

    <?php if (!empty($validSelectedServices)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>項目</th>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <th class="text-center" style="min-width: 200px;">
                                        <div class="service-header">
                                            <?php if (file_exists($service["ai_icon"])): ?>
                                                <img src="<?php echo $service["ai_icon"]; ?>" alt="<?php echo htmlspecialchars($service["ai_service"]); ?>" class="ai-icon-large">
                                            <?php else: ?>
                                                <img src="images/default-ai-icon.png" alt="<?php echo htmlspecialchars($service["ai_service"]); ?>" class="ai-icon-large" onerror="this.style.display=\'none\'">
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($service["ai_service"]); ?></strong>
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>基本情報</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td class="brand-accent" style="border-left-color: <?php echo $service["brand_color"]; ?>">
                                        <?php echo $service["basic_info"]; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>料金プラン</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td class="brand-accent" style="border-left-color: <?php echo $service["brand_color"]; ?>">
                                        <?php echo $service["pricing"]; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>特徴</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td class="brand-accent" style="border-left-color: <?php echo $service["brand_color"]; ?>">
                                        <?php echo $service["features"]; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>対応言語</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td class="brand-accent" style="border-left-color: <?php echo $service["brand_color"]; ?>">
                                        <?php echo $service["languages"]; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>利用制限</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td class="brand-accent" style="border-left-color: <?php echo $service["brand_color"]; ?>">
                                        <?php echo $service["limitations"]; ?>
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
            <h4>⚠️ 選択されたAIサービスが見つかりませんでした</h4>
            <p>有効なサービスを選択してください。利用可能なサービスID: 1, 2, 3, 4, 5</p>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
';
    
    if (file_put_contents($original_file, $new_content)) {
        echo "<p style='color:green'>✓ AI_comparison.phpのアイコン表示を最適化しました</p>";
        echo "<h3>最適化内容:</h3>";
        echo "<ul>";
        echo "<li>アイコンサイズの統一（32px/48px）</li>";
        echo "<li>角丸とシャドウ効果の追加</li>";
        echo "<li>ブランドカラーによるアクセント</li>";
        echo "<li>レスポンシブ対応</li>";
        echo "<li>エラーハンドリングの改善</li>";
        echo "</ul>";
    }
}

echo "<p><a href='AI_comparison.php?ids[]=1&ids[]=2'>最適化されたページをテスト</a></p>";
echo "</body></html>";
?>
