<?php
/**
 * 各AIサービスに固有のデータを追加
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>AIサービス固有データ追加</title></head>";
echo "<body>";
echo "<h1>🔧 AIサービス固有データ追加</h1>";

// 現在のAI_comparison.phpの内容を読み取り
$original_file = 'AI_comparison.php';
$backup_file = 'AI_comparison_backup_unique_' . date('Ymd_His') . '.php';

if (file_exists($original_file)) {
    // バックアップを作成
    if (copy($original_file, $backup_file)) {
        echo "<p style='color:green'>✓ バックアップファイル作成: $backup_file</p>";
    }
    
    // 修正されたコンテンツ
    $new_content = '<?php
/**
 * 修正版AI比較ページ
 * - 各AIサービスに固有のデータを表示
 */

// データベース接続
include "db_connect_extended.php";

// URLパラメータからIDを取得
$selectedIds = [];
if (isset($_GET["ids"]) && is_array($_GET["ids"])) {
    $selectedIds = array_map("intval", $_GET["ids"]);
}

// サービスデータの定義（詳細情報付き）
$services = [
    [
        "id" => 1,
        "ai_service" => "ChatGPT",
        "ai_icon" => "images/chatgpt-icon.png",
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
        "basic_info" => "Perplexity AI開発<br>検索特化型AI<br>2022年8月リリース",
        "pricing" => "<strong>無料:</strong> 基本検索<br><strong>Pro ($20/月):</strong> GPT-4使用<br><strong>Enterprise:</strong> 要相談",
        "features" => "• リアルタイム検索<br>• 情報源表示<br>• 学術論文検索<br>• ファクトチェック<br>• 引用機能",
        "languages" => "日本語、英語、中国語、<br>フランス語、ドイツ語、<br>スペイン語など25+言語",
        "limitations" => "<strong>無料:</strong> 1日5回のPro検索<br><strong>Pro:</strong> 1日300回のPro検索<br><strong>API:</strong> 制限あり"
    ]
];

// 有効なIDのみをフィルタリングし、対応するサービス情報を取得
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
                                                <img src="<?php echo $service["ai_icon"]; ?>" alt="<?php echo htmlspecialchars($service["ai_service"]); ?>" style="width: 24px; height: 24px; margin-right: 5px;">
                                            <?php else: ?>
                                                <img src="images/default-ai-icon.png" alt="<?php echo htmlspecialchars($service["ai_service"]); ?>" style="width: 24px; height: 24px; margin-right: 5px;" onerror="this.style.display=\'none\'">
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
                                        <?php if (file_exists($service["ai_icon"])): ?>
                                            <img src="<?php echo $service["ai_icon"]; ?>" alt="<?php echo htmlspecialchars($service["ai_service"]); ?>" style="width: 32px; height: 32px; margin-right: 5px;">
                                        <?php else: ?>
                                            <img src="images/default-ai-icon.png" alt="<?php echo htmlspecialchars($service["ai_service"]); ?>" style="width: 32px; height: 32px; margin-right: 5px;" onerror="this.style.display=\'none\'">
                                        <?php endif; ?>
                                        <br>
                                        <?php echo htmlspecialchars($service["ai_service"]); ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>基本情報</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td><?php echo $service["basic_info"]; ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>料金プラン</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td><?php echo $service["pricing"]; ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>特徴</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td><?php echo $service["features"]; ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>対応言語</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td><?php echo $service["languages"]; ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>利用制限</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td><?php echo $service["limitations"]; ?></td>
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
            <p>現在のURL: <code><?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?></code></p>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
';
    
    // ファイルに書き込み
    if (file_put_contents($original_file, $new_content)) {
        echo "<p style='color:green'>✓ AI_comparison.phpに各サービス固有のデータを追加しました</p>";
        echo "<h3>追加された固有データ:</h3>";
        echo "<ul>";
        echo "<li><strong>ChatGPT:</strong> OpenAI、GPT-4技術、プラグイン対応</li>";
        echo "<li><strong>Claude:</strong> Anthropic、Constitutional AI、長文処理</li>";
        echo "<li><strong>Gemini:</strong> Google、マルチモーダル、検索連携</li>";
        echo "<li><strong>Copilot:</strong> Microsoft、Bing統合、Office連携</li>";
        echo "<li><strong>Perplexity:</strong> 検索特化、情報源表示、学術論文</li>";
        echo "</ul>";
    } else {
        echo "<p style='color:red'>✗ ファイルの書き込みに失敗しました</p>";
    }
} else {
    echo "<p style='color:red'>✗ AI_comparison.phpが見つかりません</p>";
}

echo "<h2>🧪 修正後のテスト</h2>";
echo "<p>以下のURLでテストしてください：</p>";
echo "<ul>";
echo "<li><a href='AI_comparison.php?ids[]=1&ids[]=2' target='_blank'>ChatGPT vs Claude</a></li>";
echo "<li><a href='AI_comparison.php?ids[]=1&ids[]=3&ids[]=5' target='_blank'>ChatGPT vs Gemini vs Perplexity</a></li>";
echo "<li><a href='AI_comparison.php?ids[]=2&ids[]=4' target='_blank'>Claude vs Copilot</a></li>";
echo "</ul>";

echo "<p><a href='simple_debug.php'>デバッグ情報に戻る</a></p>";

echo "</body></html>";
?>
