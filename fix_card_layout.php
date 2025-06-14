<?php
/**
 * カード型レイアウトの修正版
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>カード型レイアウト修正</title></head>";
echo "<body>";
echo "<h1>🔧 カード型レイアウト修正</h1>";

// list.phpの修正内容
$list_content = '<?php
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
    <h1>AI一覧</h1>
    
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
                <a href="?" class="btn btn-outline-secondary btn-sm me-2 <?php echo empty($search) && $sort === "name" ? \'active\' : \'\'; ?>">
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
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
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
</script>
';

// index.phpの修正内容
$index_content = '<?php
// データベース接続
include "db_connect.php";

// おすすめAIサービスを取得
$featured_sql = "SELECT * FROM ai_tools WHERE is_featured = 1 ORDER BY rating DESC LIMIT 6";
$featured_result = $conn->query($featured_sql);

// 最新AIサービスを取得
$latest_sql = "SELECT * FROM ai_tools ORDER BY created_at DESC LIMIT 8";
$latest_result = $conn->query($latest_sql);

include "includes/header.php";
?>

<div class="container mt-4">
    <div class="jumbotron bg-light p-5 rounded">
        <h1 class="display-4">AI情報システム</h1>
        <p class="lead">最新のAIサービスを比較・検索できるプラットフォーム</p>
        <hr class="my-4">
        <p>あなたに最適なAIツールを見つけましょう</p>
        <div class="d-flex gap-2">
            <a class="btn btn-primary btn-lg" href="list.php" role="button">一覧を見る</a>
            <a class="btn btn-secondary btn-lg" href="AI_comparison.php" role="button">比較する</a>
        </div>
    </div>

    <!-- おすすめAIサービス -->
    <?php if ($featured_result && $featured_result->num_rows > 0): ?>
    <div class="mt-5">
        <h2 class="mb-4">おすすめAIサービス</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php while ($row = $featured_result->fetch_assoc()): ?>
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
                                        <?php echo $rating; ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2">
                                <a href="detail.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">詳細を見る</a>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <?php if (isset($row["is_free"]) && $row["is_free"]): ?>
                                    <span class="badge bg-success">無料</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">有料</span>
                                <?php endif; ?>
                                
                                <span class="badge bg-warning">おすすめ</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-4">
            <a href="list.php" class="btn btn-outline-primary">すべて見る</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- 最新AIサービス -->
    <?php if ($latest_result && $latest_result->num_rows > 0): ?>
    <div class="mt-5">
        <h2 class="mb-4">最新AIサービス</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php while ($row = $latest_result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <?php 
                            $icon_path = isset($row["logo_url"]) && !empty($row["logo_url"]) ? $row["logo_url"] : "images/default-ai-icon.png";
                            ?>
                            <img src="<?php echo $icon_path; ?>" alt="<?php echo htmlspecialchars($row["ai_service"]); ?>" 
                                 class="mb-3" style="width: 48px; height: 48px; border-radius: 8px;"
                                 onerror="this.src=\'images/default-ai-icon.png\'">
                            <h5 class="card-title"><?php echo htmlspecialchars($row["ai_service"]); ?></h5>
                            <p class="card-text small text-muted">
                                <?php echo htmlspecialchars(substr($row["description"], 0, 60)) . "..."; ?>
                            </p>
                            <a href="detail.php?id=<?php echo $row["id"]; ?>" class="btn btn-outline-primary btn-sm">詳細</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-4">
            <a href="list.php?sort=newest" class="btn btn-outline-primary">もっと見る</a>
        </div>
    </div>
    <?php endif; ?>
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
</script>
';

// list.phpを更新
$list_file = 'list.php';
$list_backup = 'list_backup_' . date('Ymd_His') . '.php';

if (file_exists($list_file)) {
    if (copy($list_file, $list_backup)) {
        echo "<p style='color:green'>✓ list.phpのバックアップを作成しました: $list_backup</p>";
    }
    
    if (file_put_contents($list_file, $list_content)) {
        echo "<p style='color:green'>✓ list.phpを修正しました</p>";
    } else {
        echo "<p style='color:red'>✗ list.phpの更新に失敗しました</p>";
    }
} else {
    if (file_put_contents($list_file, $list_content)) {
        echo "<p style='color:green'>✓ list.phpを新規作成しました</p>";
    } else {
        echo "<p style='color:red'>✗ list.phpの作成に失敗しました</p>";
    }
}

// index.phpを更新
$index_file = 'index.php';
$index_backup = 'index_backup_' . date('Ymd_His') . '.php';

if (file_exists($index_file)) {
    if (copy($index_file, $index_backup)) {
        echo "<p style='color:green'>✓ index.phpのバックアップを作成しました: $index_backup</p>";
    }
    
    if (file_put_contents($index_file, $index_content)) {
        echo "<p style='color:green'>✓ index.phpを修正しました</p>";
    } else {
        echo "<p style='color:red'>✗ index.phpの更新に失敗しました</p>";
    }
} else {
    if (file_put_contents($index_file, $index_content)) {
        echo "<p style='color:green'>✓ index.phpを新規作成しました</p>";
    } else {
        echo "<p style='color:red'>✗ index.phpの作成に失敗しました</p>";
    }
}

echo "<h2>🔧 修正内容</h2>";
echo "<ul>";
echo "<li>PHPの構文エラーを修正</li>";
echo "<li>シングルクォートのエスケープ処理を追加</li>";
echo "<li>データベースクエリを簡素化</li>";
echo "<li>エラーハンドリングを強化</li>";
echo "<li>画像読み込みエラー時のフォールバック処理を追加</li>";
echo "<li>レスポンシブデザインを最適化</li>";
echo "</ul>";

echo "<h2>🧪 テスト方法</h2>";
echo "<ol>";
echo "<li><a href='list.php' target='_blank'>カード型一覧ページを確認</a></li>";
echo "<li><a href='index.php' target='_blank'>トップページを確認</a></li>";
echo "</ol>";

echo "</body></html>";
?>
