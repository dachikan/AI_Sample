<?php
/**
 * 実際のリストページを特定して修正するスクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>リストページ修正</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }";
echo ".section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".error { color: #dc3545; font-weight: bold; }";
echo ".warning { color: #ffc107; font-weight: bold; }";
echo ".info { color: #17a2b8; font-weight: bold; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background-color: #f8f9fa; }";
echo ".btn { display: inline-block; padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; background-color: #007bff; border: none; cursor: pointer; }";
echo ".btn-success { background-color: #28a745; }";
echo ".btn-warning { background-color: #ffc107; color: #000; }";
echo ".btn-danger { background-color: #dc3545; }";
echo "pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; max-height: 300px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔧 リストページ修正</h1>";
echo "<p>実行時刻: " . date('Y-m-d H:i:s') . "</p>";

// 1. 候補ファイルの検索
$list_candidates = [
    'AI_list.php',
    'list.php',
    'ai_list.php',
    'AIlist.php'
];

$found_candidates = [];
foreach ($list_candidates as $file) {
    if (file_exists($file)) {
        $found_candidates[$file] = [
            'size' => filesize($file),
            'modified' => filemtime($file)
        ];
    }
}

echo "<div class='section'>";
echo "<h2>📁 候補ファイルの検索</h2>";

if (!empty($found_candidates)) {
    echo "<p class='success'>✓ " . count($found_candidates) . " 個の候補ファイルが見つかりました</p>";
    
    echo "<table>";
    echo "<tr><th>ファイル名</th><th>サイズ</th><th>更新日時</th><th>アクション</th></tr>";
    
    foreach ($found_candidates as $file => $info) {
        echo "<tr>";
        echo "<td><strong>$file</strong></td>";
        echo "<td>" . number_format($info['size']) . " bytes</td>";
        echo "<td>" . date('Y-m-d H:i:s', $info['modified']) . "</td>";
        echo "<td>";
        echo "<form method='post' style='display:inline;'>";
        echo "<input type='hidden' name='target_file' value='$file'>";
        echo "<button type='submit' class='btn btn-success'>このファイルを修正</button>";
        echo "</form>";
        echo " <a href='$file' target='_blank' class='btn'>確認</a>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p class='error'>✗ 候補ファイルが見つかりませんでした</p>";
}
echo "</div>";

// 2. ファイル修正処理
if (isset($_POST['target_file'])) {
    $target_file = $_POST['target_file'];
    
    echo "<div class='section'>";
    echo "<h2>🔄 ファイル修正処理</h2>";
    
    if (file_exists($target_file)) {
        // バックアップを作成
        $backup_file = $target_file . '.backup.' . date('Ymd_His');
        if (copy($target_file, $backup_file)) {
            echo "<p class='success'>✓ バックアップを作成しました: $backup_file</p>";
        } else {
            echo "<p class='error'>✗ バックアップの作成に失敗しました</p>";
        }
        
        // カード型レイアウトのコード
        $card_layout_code = '<?php
// データベース接続
include "db_connect.php";

// 検索・フィルタリング
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$sort = isset($_GET["sort"]) ? $_GET["sort"] : "name";

// SQLクエリ構築
$sql = "SELECT * FROM ai_tools WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (ai_service LIKE \'%$search%\' OR description LIKE \'%$search%\')";
}

// ソート
switch ($sort) {
    case "rating":
        $sql .= " ORDER BY rating DESC";
        break;
    case "newest":
        $sql .= " ORDER BY created_at DESC";
        break;
    case "popular":
        $sql .= " ORDER BY review_count DESC";
        break;
    default:
        $sql .= " ORDER BY ai_service ASC";
}

// クエリ実行
$result = $conn->query($sql);

include "includes/header.php";
?>

<div class="container mt-4">
    <h1>AIサービス一覧</h1>
    <p>全 <?php echo $result ? $result->num_rows : 0; ?> サービス</p>
    
    <!-- 検索・フィルター -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="AIサービス名や説明で検索..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="sort">
                        <option value="name" <?php echo $sort === "name" ? "selected" : ""; ?>>名前順</option>
                        <option value="rating" <?php echo $sort === "rating" ? "selected" : ""; ?>>評価順</option>
                        <option value="newest" <?php echo $sort === "newest" ? "selected" : ""; ?>>新着順</option>
                        <option value="popular" <?php echo $sort === "popular" ? "selected" : ""; ?>>人気順</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">検索</button>
                </div>
            </form>
            
            <!-- クイックフィルター -->
            <div class="mt-3">
                <a href="?sort=<?php echo $sort; ?>" class="btn btn-outline-secondary btn-sm me-2 <?php echo empty($search) ? \'active\' : \'\'; ?>">
                    すべて
                </a>
                <a href="?search=無料&sort=<?php echo $sort; ?>" class="btn btn-outline-success btn-sm me-2">
                    無料
                </a>
                <a href="?search=おすすめ&sort=<?php echo $sort; ?>" class="btn btn-outline-warning btn-sm me-2">
                    おすすめ
                </a>
                <a href="?search=画像&sort=<?php echo $sort; ?>" class="btn btn-outline-info btn-sm me-2">
                    画像生成
                </a>
                <a href="?search=チャット&sort=<?php echo $sort; ?>" class="btn btn-outline-primary btn-sm">
                    チャット
                </a>
            </div>
        </div>
    </div>

    <!-- AIサービスカード一覧 -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-light d-flex align-items-center">
                            <?php 
                            $icon_path = isset($row["logo_url"]) && !empty($row["logo_url"]) ? $row["logo_url"] : "images/default-ai-icon.png";
                            ?>
                            <img src="<?php echo $icon_path; ?>" alt="<?php echo htmlspecialchars($row["ai_service"]); ?>" 
                                 class="me-2" style="width: 32px; height: 32px; border-radius: 4px;"
                                 onerror="this.src=\'images/default-ai-icon.png\'">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($row["ai_service"]); ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text" style="height: 4.5em; overflow: hidden;">
                                <?php echo htmlspecialchars(substr($row["description"], 0, 100)) . "..."; ?>
                            </p>
                            
                            <?php if (isset($row["rating"]) && $row["rating"] > 0): ?>
                                <div class="mb-2">
                                    <?php 
                                    $rating = floatval($row["rating"]);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo \'<i class="fas fa-star text-warning"></i>\';
                                        } elseif ($i - 0.5 <= $rating) {
                                            echo \'<i class="fas fa-star-half-alt text-warning"></i>\';
                                        } else {
                                            echo \'<i class="far fa-star text-warning"></i>\';
                                        }
                                    }
                                    ?>
                                    <small class="text-muted ms-1">
                                        <?php echo $rating; ?> (<?php echo isset($row["review_count"]) ? number_format($row["review_count"]) : 0; ?>件)
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2">
                                <a href="detail.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">詳細を見る</a>
                                <a href="AI_comparison.php?ids[]=<?php echo $row["id"]; ?>" class="btn btn-outline-secondary btn-sm">比較に追加</a>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <?php if (isset($row["is_free"]) && $row["is_free"]): ?>
                                    <span class="badge bg-success">無料</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">有料</span>
                                <?php endif; ?>
                                
                                <?php if (isset($row["is_featured"]) && $row["is_featured"]): ?>
                                    <span class="badge bg-warning">おすすめ</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    該当するAIサービスが見つかりませんでした。検索条件を変更してお試しください。
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script>
document.addEventListener("DOMContentLoaded", function() {
    // カードのホバーエフェクト
    const cards = document.querySelectorAll(".card");
    cards.forEach(card => {
        card.addEventListener("mouseenter", function() {
            this.style.transform = "translateY(-5px)";
            this.style.transition = "transform 0.3s ease";
            this.style.boxShadow = "0 10px 20px rgba(0,0,0,0.1)";
        });
        
        card.addEventListener("mouseleave", function() {
            this.style.transform = "translateY(0)";
            this.style.boxShadow = "0 2px 5px rgba(0,0,0,0.1)";
        });
    });
});
</script>';
        
        // ファイルを更新
        if (file_put_contents($target_file, $card_layout_code)) {
            echo "<p class='success'>✓ $target_file をカード型レイアウトに更新しました</p>";
            echo "<h3>更新内容:</h3>";
            echo "<ul>";
            echo "<li>ページネーションを削除してスクロール型に変更</li>";
            echo "<li>カード型レイアウトを適用</li>";
            echo "<li>検索・フィルター機能を追加</li>";
            echo "<li>レスポンシブデザインを適用</li>";
            echo "</ul>";
            
            echo "<p><a href='$target_file' target='_blank' class='btn btn-success'>更新されたページを確認</a></p>";
        } else {
            echo "<p class='error'>✗ ファイルの更新に失敗しました</p>";
        }
    } else {
        echo "<p class='error'>✗ 指定されたファイルが見つかりません: $target_file</p>";
    }
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>🔍 ファイル名の確認方法</h2>";
echo "<p>実際に使用されているファイル名を確認するには、以下の方法があります：</p>";
echo "<ol>";
echo "<li>ブラウザのURLを確認する（例：https://example.com/AI_list.php）</li>";
echo "<li>ページのソースコードでリンク先を確認する</li>";
echo "<li>サーバーのアクセスログを確認する</li>";
echo "</ol>";

echo "<p>画像のURLが <code>ai.nsk.org/AI_list.php</code> となっていることから、<strong>AI_list.php</strong> が正しいファイル名である可能性が高いです。</p>";
echo "</div>";

echo "</body></html>";
?>
