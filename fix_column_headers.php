<?php
/**
 * AI_comparison.phpの列ヘッダー表示を修正
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>列ヘッダー修正</title></head>";
echo "<body>";
echo "<h1>🔧 AI_comparison.php列ヘッダー修正</h1>";

// 現在のAI_comparison.phpの内容を読み取り
$original_file = 'AI_comparison.php';
$backup_file = 'AI_comparison_backup_headers_' . date('Ymd_His') . '.php';

if (file_exists($original_file)) {
    // バックアップを作成
    if (copy($original_file, $backup_file)) {
        echo "<p style='color:green'>✓ バックアップファイル作成: $backup_file</p>";
    }
    
    // 修正されたコンテンツ
    $new_content = '<?php
/**
 * 修正版AI比較ページ
 * - URLパラメータの適切な処理
 * - 存在しないIDの処理
 * - エラーハンドリングの追加
 * - 列ヘッダーの表示修正
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
                                    <td>
                                        <strong><?php echo htmlspecialchars($service["ai_service"]); ?></strong><br>
                                        高性能な対話型AIアシスタント<br>
                                        自然言語処理に特化
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>料金プラン</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td>
                                        <strong>無料プラン:</strong> 制限あり<br>
                                        <strong>有料プラン:</strong> $20/月<br>
                                        <strong>企業プラン:</strong> 要相談
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>特徴</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td>
                                        • 高精度な文章生成<br>
                                        • 多言語対応<br>
                                        • API提供<br>
                                        • リアルタイム応答
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>対応言語</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td>
                                        日本語、英語、中国語、<br>
                                        スペイン語、フランス語、<br>
                                        ドイツ語など100+言語
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td><strong>利用制限</strong></td>
                                <?php foreach ($validSelectedServices as $service): ?>
                                    <td>
                                        <strong>無料:</strong> 1日20回まで<br>
                                        <strong>有料:</strong> 無制限<br>
                                        <strong>API:</strong> レート制限あり
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
            <p>現在のURL: <code><?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?></code></p>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
';
    
    // ファイルに書き込み
    if (file_put_contents($original_file, $new_content)) {
        echo "<p style='color:green'>✓ AI_comparison.phpの列ヘッダーを修正しました</p>";
        echo "<h3>主な修正内容:</h3>";
        echo "<ul>";
        echo "<li>有効なサービス情報のみを取得するロジックを改善</li>";
        echo "<li>列ヘッダーにサービス名を正しく表示</li>";
        echo "<li>より詳細なサンプルデータを追加</li>";
        echo "<li>存在しないIDの場合の警告メッセージを改善</li>";
        echo "<li>画像読み込みエラーの処理を追加</li>";
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
echo "<li><a href='AI_comparison.php?ids[]=1&ids[]=2' target='_blank'>AI_comparison.php?ids[]=1&ids[]=2</a> - ChatGPTとClaudeの比較</li>";
echo "<li><a href='AI_comparison.php?ids[]=1&ids[]=3&ids[]=5' target='_blank'>AI_comparison.php?ids[]=1&ids[]=3&ids[]=5</a> - 3つのサービス比較</li>";
echo "<li><a href='AI_comparison.php?ids[]=25&ids[]=14&ids[]=28' target='_blank'>AI_comparison.php?ids[]=25&ids[]=14&ids[]=28</a> - 存在しないIDのテスト</li>";
echo "</ul>";

echo "<p><a href='simple_debug.php'>デバッグ情報に戻る</a></p>";

echo "</body></html>";
?>
