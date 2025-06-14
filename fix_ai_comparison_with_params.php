<?php
/**
 * AI_comparison.phpのパラメータ処理を修正するスクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>AI_comparison.php詳細修正</title></head>";
echo "<body>";
echo "<h1>🔧 AI_comparison.php詳細修正</h1>";

// 現在のAI_comparison.phpの内容を読み取り
$original_file = 'AI_comparison.php';
$backup_file = 'AI_comparison_backup_' . date('Ymd_His') . '.php';

if (file_exists($original_file)) {
    // バックアップを作成
    if (copy($original_file, $backup_file)) {
        echo "<p style='color:green'>✓ バックアップファイル作成: $backup_file</p>";
    }
    
    // 新しいコンテンツを作成
    $new_content = '<?php
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
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
';
    
    // ファイルに書き込み
    if (file_put_contents($original_file, $new_content)) {
        echo "<p style='color:green'>✓ AI_comparison.phpを完全に書き換えました</p>";
        echo "<h3>主な修正内容:</h3>";
        echo "<ul>";
        echo "<li>URLパラメータ（ids[]）の適切な処理</li>";
        echo "<li>サービスIDを数値型（1〜5）に変更</li>";
        echo "<li>画像ファイルの存在チェックを追加</li>";
        echo "<li>完全なHTML構造の実装</li>";
        echo "<li>比較テーブルの実装</li>";
        echo "</ul>";
    } else {
        echo "<p style='color:red'>✗ ファイルの書き込みに失敗しました</p>";
    }
} else {
    echo "<p style='color:red'>✗ AI_comparison.phpが見つかりません</p>";
}

echo "<h2>🧪 修正後のテスト</h2>";
echo "<p>修正されたAI_comparison.phpをテストしてみます...</p>";

echo "<p>以下のURLでテストしてください：</p>";
echo "<ul>";
echo "<li><a href='AI_comparison.php' target='_blank'>AI_comparison.php</a> - 選択なし</li>";
echo "<li><a href='AI_comparison.php?ids[]=1&ids[]=2' target='_blank'>AI_comparison.php?ids[]=1&ids[]=2</a> - ChatGPTとClaudeの比較</li>";
echo "<li><a href='AI_comparison.php?ids[]=1&ids[]=3&ids[]=5' target='_blank'>AI_comparison.php?ids[]=1&ids[]=3&ids[]=5</a> - 3つのサービス比較</li>";
echo "</ul>";

echo "<h2>🎯 次のステップ</h2>";
echo "<ol>";
echo "<li>修正したAI_comparison.phpにアクセスして500エラーが解消されているか確認</li>";
echo "<li>必要に応じて画像ファイルを実際のAIサービスのロゴに置き換え</li>";
echo "<li>実際のデータベースからデータを取得するように拡張</li>";
echo "</ol>";

echo "<p><a href='simple_debug.php'>デバッグ情報に戻る</a></p>";

echo "</body></html>";
?>
