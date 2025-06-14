<?php
/**
 * AI_list.phpをカード型レイアウトに更新
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>AI_list.php カード型更新</title></head>";
echo "<body>";
echo "<h1>🎴 AI_list.php カード型レイアウト更新</h1>";

// AI_list.phpの新しい内容（カード型、ページネーションなし）
$ai_list_content = '<?php
// データベース接続
include "db_connect.php";

// 検索・フィルタリング
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$category = isset($_GET["category"]) ? $_GET["category"] : "";
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

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIサービス一覧 - AI情報システム</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .ai-card {
            transition: all 0.3s ease;
            height: 100%;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 15px;
            overflow: hidden;
        }
        
        .ai-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .ai-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 0.375rem 0.375rem 0 0 !important;
        }
        
        .rating-stars {
            color: #ffc107;
        }
        
        .badge-custom {
            font-size: 0.75rem;
        }
        
        .search-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .filter-btn {
            border-radius: 20px;
            margin: 0.25rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: none;
            z-index: 1000;
        }
        
        .card-description {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.5;
            height: 4.5em;
        }
        
        @media (max-width: 768px) {
            .ai-icon {
                width: 40px;
                height: 40px;
            }
            
            .search-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid px-4 py-3">
        <!-- ヘッダー統計 -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?php echo $result ? $result->num_rows : 0; ?></h3>
                        <small>AIサービス総数</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="mb-0">
                            <?php 
                            $free_count = 0;
                            if ($result) {
                                $temp_result = $conn->query("SELECT COUNT(*) as count FROM ai_tools WHERE is_free = 1");
                                if ($temp_result) {
                                    $free_count = $temp_result->fetch_assoc()["count"];
                                }
                            }
                            echo $free_count;
                            ?>
                        </h3>
                        <small>無料サービス</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="mb-0">
                            <?php 
                            $featured_count = 0;
                            if ($result) {
                                $temp_result = $conn->query("SELECT COUNT(*) as count FROM ai_tools WHERE is_featured = 1");
                                if ($temp_result) {
                                    $featured_count = $temp_result->fetch_assoc()["count"];
                                }
                            }
                            echo $featured_count;
                            ?>
                        </h3>
                        <small>おすすめサービス</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="mb-0">
                            <?php 
                            $avg_rating = 0;
                            if ($result) {
                                $temp_result = $conn->query("SELECT AVG(rating) as avg_rating FROM ai_tools WHERE rating > 0");
                                if ($temp_result) {
                                    $avg_rating = round($temp_result->fetch_assoc()["avg_rating"], 1);
                                }
                            }
                            echo $avg_rating;
                            ?>
                        </h3>
                        <small>平均評価</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 検索・フィルター -->
        <div class="search-section">
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
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>検索
                    </button>
                </div>
            </form>
            
            <!-- クイックフィルター -->
            <div class="mt-3">
                <a href="?sort=<?php echo $sort; ?>" class="btn btn-outline-secondary filter-btn <?php echo empty($search) ? \'active\' : \'\'; ?>">
                    <i class="fas fa-list me-1"></i>すべて
                </a>
                <a href="?search=無料&sort=<?php echo $sort; ?>" class="btn btn-outline-success filter-btn">
                    <i class="fas fa-gift me-1"></i>無料
                </a>
                <a href="?search=おすすめ&sort=<?php echo $sort; ?>" class="btn btn-outline-warning filter-btn">
                    <i class="fas fa-star me-1"></i>おすすめ
                </a>
                <a href="?search=画像&sort=<?php echo $sort; ?>" class="btn btn-outline-info filter-btn">
                    <i class="fas fa-image me-1"></i>画像生成
                </a>
                <a href="?search=チャット&sort=<?php echo $sort; ?>" class="btn btn-outline-primary filter-btn">
                    <i class="fas fa-comments me-1"></i>チャット
                </a>
            </div>
        </div>

        <!-- AIサービスカード一覧 -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" id="ai-cards">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card ai-card h-100">
                            <div class="card-header card-header-custom d-flex align-items-center">
                                <?php 
                                $icon_path = isset($row["logo_url"]) && !empty($row["logo_url"]) ? $row["logo_url"] : "images/default-ai-icon.png";
                                ?>
                                <img src="<?php echo $icon_path; ?>" alt="<?php echo htmlspecialchars($row["ai_service"]); ?>" 
                                     class="ai-icon me-3" onerror="this.src=\'images/default-ai-icon.png\'">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($row["ai_service"]); ?></h6>
                                    <?php if (isset($row["is_featured"]) && $row["is_featured"]): ?>
                                        <span class="badge bg-warning badge-custom">
                                            <i class="fas fa-star me-1"></i>おすすめ
                                        </span>
                                    <?php endif; ?>
                                    <?php if (isset($row["is_free"]) && $row["is_free"]): ?>
                                        <span class="badge bg-success badge-custom">
                                            <i class="fas fa-gift me-1"></i>無料
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <p class="card-text card-description text-muted">
                                    <?php echo htmlspecialchars($row["description"]); ?>
                                </p>
                                
                                <div class="mt-auto">
                                    <?php if (isset($row["rating"]) && $row["rating"] > 0): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="rating-stars me-2">
                                                <?php 
                                                $rating = floatval($row["rating"]);
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $rating) {
                                                        echo \'<i class="fas fa-star"></i>\';
                                                    } elseif ($i - 0.5 <= $rating) {
                                                        echo \'<i class="fas fa-star-half-alt"></i>\';
                                                    } else {
                                                        echo \'<i class="far fa-star"></i>\';
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo $rating; ?> (<?php echo isset($row["review_count"]) ? number_format($row["review_count"]) : 0; ?>件)
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="detail.php?id=<?php echo $row["id"]; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-info-circle me-1"></i>詳細を見る
                                        </a>
                                        <a href="AI_comparison.php?ids[]=<?php echo $row["id"]; ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-balance-scale me-1"></i>比較に追加
                                        </a>
                                        <?php if (isset($row["website_url"]) && !empty($row["website_url"])): ?>
                                            <a href="<?php echo htmlspecialchars($row["website_url"]); ?>" target="_blank" class="btn btn-success btn-sm">
                                                <i class="fas fa-external-link-alt me-1"></i>公式サイト
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">該当するAIサービスが見つかりませんでした</h4>
                        <p class="text-muted">検索条件を変更してお試しください。</p>
                        <a href="?" class="btn btn-primary">
                            <i class="fas fa-refresh me-1"></i>すべて表示
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- トップに戻るボタン -->
    <button class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-chevron-up"></i>
    </button>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // トップに戻るボタン
        window.addEventListener("scroll", function() {
            const scrollToTop = document.getElementById("scrollToTop");
            if (window.pageYOffset > 300) {
                scrollToTop.style.display = "block";
            } else {
                scrollToTop.style.display = "none";
            }
        });

        document.getElementById("scrollToTop").addEventListener("click", function() {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });

        // カードのアニメーション
        const observerOptions = {
            threshold: 0.1,
            rootMargin: "0px 0px -50px 0px"
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = "1";
                    entry.target.style.transform = "translateY(0)";
                }
            });
        }, observerOptions);

        // 初期状態でカードを非表示にして、スクロール時にアニメーション
        document.querySelectorAll(".ai-card").forEach(card => {
            card.style.opacity = "0";
            card.style.transform = "translateY(20px)";
            card.style.transition = "opacity 0.6s ease, transform 0.6s ease";
            observer.observe(card);
        });

        // 検索フォームのエンターキー対応
        document.querySelector("input[name=\'search\']").addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                this.closest("form").submit();
            }
        });
    </script>
</body>
</html>';

// AI_list.phpをバックアップ
$ai_list_file = 'AI_list.php';
$backup_file = 'AI_list_backup_' . date('Ymd_His') . '.php';

if (file_exists($ai_list_file)) {
    if (copy($ai_list_file, $backup_file)) {
        echo "<p style='color:green'>✓ 既存のAI_list.phpをバックアップしました: $backup_file</p>";
    }
}

// 新しいAI_list.phpを作成
if (file_put_contents($ai_list_file, $ai_list_content)) {
    echo "<p style='color:green'>✓ AI_list.phpをカード型レイアウトに更新しました</p>";
    
    echo "<h3>✨ 更新内容:</h3>";
    echo "<ul>";
    echo "<li>📱 <strong>カード型レイアウト</strong> - ページネーションを廃止してスクロール型に</li>";
    echo "<li>🔍 <strong>リアルタイム検索</strong> - サービス名・説明での検索</li>";
    echo "<li>🏷️ <strong>クイックフィルター</strong> - 無料、おすすめ、カテゴリ別</li>";
    echo "<li>⭐ <strong>評価表示</strong> - 星評価とレビュー数</li>";
    echo "<li>📊 <strong>統計情報</strong> - サービス数、無料数、平均評価</li>";
    echo "<li>🎨 <strong>ホバーエフェクト</strong> - カードの浮き上がりアニメーション</li>";
    echo "<li>🔝 <strong>トップに戻るボタン</strong> - スムーズスクロール</li>";
    echo "<li>⚡ <strong>スクロールアニメーション</strong> - カードの段階的表示</li>";
    echo "</ul>";
    
} else {
    echo "<p style='color:red'>✗ AI_list.phpの更新に失敗しました</p>";
}

echo "<h2>🧪 テスト方法</h2>";
echo "<ol>";
echo "<li><a href='AI_list.php' target='_blank'>更新されたAI_list.phpを確認</a></li>";
echo "<li>検索機能をテスト（例：「ChatGPT」で検索）</li>";
echo "<li>フィルター機能をテスト（無料、おすすめなど）</li>";
echo "<li>ソート機能をテスト（評価順、新着順など）</li>";
echo "<li>レスポンシブデザインをテスト（画面サイズ変更）</li>";
echo "</ol>";

echo "<h2>📝 ファイル整理状況</h2>";
echo "<ul>";
echo "<li><strong>AI_list.php</strong> - ✅ カード型レイアウト（メイン一覧ページ）</li>";
echo "<li><strong>list.php</strong> - ✅ カード型レイアウト（サブ一覧ページ）</li>";
echo "<li><strong>AI_index.php</strong> - ✅ カード型ランディングページ</li>";
echo "<li><strong>index.php</strong> - ⚠️ 復元済み（元の形式）</li>";
echo "</ul>";

echo "</body></html>";
?>
