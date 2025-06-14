<?php
/**
 * AI_index.phpのカード型レイアウト修正
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>AI_index.php修正</title></head>";
echo "<body>";
echo "<h1>🔧 AI_index.phpのカード型レイアウト修正</h1>";

// AI_index.phpの修正内容
$ai_index_content = '<?php
// データベース接続
include "db_connect.php";

// おすすめAIサービスを取得
$featured_sql = "SELECT * FROM ai_tools WHERE is_featured = 1 ORDER BY rating DESC LIMIT 6";
$featured_result = $conn->query($featured_sql);

// 最新AIサービスを取得
$latest_sql = "SELECT * FROM ai_tools ORDER BY created_at DESC LIMIT 8";
$latest_result = $conn->query($latest_sql);

// 全サービス数を取得
$total_sql = "SELECT COUNT(*) as total FROM ai_tools";
$total_result = $conn->query($total_sql);
$total_count = $total_result ? $total_result->fetch_assoc()["total"] : 0;

// 無料サービス数を取得
$free_sql = "SELECT COUNT(*) as free_count FROM ai_tools WHERE is_free = 1";
$free_result = $conn->query($free_sql);
$free_count = $free_result ? $free_result->fetch_assoc()["free_count"] : 0;

include "includes/header.php";
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI情報システム - 最新のAIサービス情報</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
        }
        
        .ai-card {
            transition: all 0.3s ease;
            height: 100%;
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            border-radius: 15px;
            overflow: hidden;
        }
        
        .ai-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        
        .ai-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .section-title {
            position: relative;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }
        
        .section-title::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }
        
        .rating-stars {
            color: #ffc107;
        }
        
        .badge-custom {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        
        .stats-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 20px;
            padding: 3rem 2rem;
            margin: 3rem 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            display: block;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            margin: 3rem 0;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 0;
            }
            
            .ai-icon {
                width: 40px;
                height: 40px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- ヒーローセクション -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">
                        最新のAI情報を<br>
                        <span class="text-warning">一箇所で</span>
                    </h1>
                    <p class="lead mb-4">
                        <?php echo $total_count; ?>以上のAIサービスを比較・検索できる総合情報プラットフォーム。
                        あなたに最適なAIツールを見つけましょう。
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="list.php" class="btn btn-warning btn-lg px-4">
                            <i class="fas fa-list me-2"></i>すべて見る
                        </a>
                        <a href="AI_comparison.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-balance-scale me-2"></i>比較する
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="display-1 mb-3">
                        <i class="fas fa-robot"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- 統計セクション -->
        <div class="stats-section">
            <div class="row">
                <div class="col-md-3 stat-item">
                    <span class="stat-number"><?php echo $total_count; ?></span>
                    <span class="text-muted">AIサービス</span>
                </div>
                <div class="col-md-3 stat-item">
                    <span class="stat-number"><?php echo $free_count; ?></span>
                    <span class="text-muted">無料サービス</span>
                </div>
                <div class="col-md-3 stat-item">
                    <span class="stat-number">
                        <?php 
                        $featured_count_sql = "SELECT COUNT(*) as count FROM ai_tools WHERE is_featured = 1";
                        $featured_count_result = $conn->query($featured_count_sql);
                        echo $featured_count_result ? $featured_count_result->fetch_assoc()["count"] : 0;
                        ?>
                    </span>
                    <span class="text-muted">おすすめ</span>
                </div>
                <div class="col-md-3 stat-item">
                    <span class="stat-number">
                        <?php 
                        $avg_sql = "SELECT AVG(rating) as avg_rating FROM ai_tools WHERE rating > 0";
                        $avg_result = $conn->query($avg_sql);
                        echo $avg_result ? round($avg_result->fetch_assoc()["avg_rating"], 1) : 0;
                        ?>
                    </span>
                    <span class="text-muted">平均評価</span>
                </div>
            </div>
        </div>

        <!-- おすすめAIサービス -->
        <?php if ($featured_result && $featured_result->num_rows > 0): ?>
        <section class="mb-5">
            <h2 class="section-title">
                <i class="fas fa-star text-warning me-2"></i>おすすめAIサービス
            </h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php while ($row = $featured_result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card ai-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <?php 
                                    $icon_path = isset($row["logo_url"]) && !empty($row["logo_url"]) ? $row["logo_url"] : "images/default-ai-icon.png";
                                    ?>
                                    <img src="<?php echo $icon_path; ?>" alt="<?php echo htmlspecialchars($row["ai_service"]); ?>" 
                                         class="ai-icon me-3" onerror="this.src=\'images/default-ai-icon.png\'">
                                    <div>
                                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($row["ai_service"]); ?></h5>
                                        <div>
                                            <span class="badge bg-warning badge-custom">
                                                <i class="fas fa-star me-1"></i>おすすめ
                                            </span>
                                            <?php if (isset($row["is_free"]) && $row["is_free"]): ?>
                                                <span class="badge bg-success badge-custom">
                                                    <i class="fas fa-gift me-1"></i>無料
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <p class="card-text text-muted mb-3">
                                    <?php echo mb_substr(htmlspecialchars($row["description"]), 0, 80) . "..."; ?>
                                </p>
                                
                                <?php if (isset($row["rating"]) && $row["rating"] > 0): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rating-stars me-2">
                                            <?php 
                                            $rating = floatval($row["rating"]);
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $rating ? \'<i class="fas fa-star"></i>\' : \'<i class="far fa-star"></i>\';
                                            }
                                            ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo $rating; ?> (<?php echo isset($row["review_count"]) ? number_format($row["review_count"]) : 0; ?>件)
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-grid gap-2">
                                    <a href="detail.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-info-circle me-1"></i>詳細を見る
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center mt-4">
                <a href="list.php?featured=1" class="btn btn-outline-primary">
                    <i class="fas fa-star me-2"></i>おすすめをもっと見る
                </a>
            </div>
        </section>
        <?php endif; ?>

        <!-- 最新AIサービス -->
        <?php if ($latest_result && $latest_result->num_rows > 0): ?>
        <section class="mb-5">
            <h2 class="section-title">
                <i class="fas fa-clock text-info me-2"></i>最新AIサービス
            </h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php while ($row = $latest_result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card ai-card">
                            <div class="card-body text-center">
                                <?php 
                                $icon_path = isset($row["logo_url"]) && !empty($row["logo_url"]) ? $row["logo_url"] : "images/default-ai-icon.png";
                                ?>
                                <img src="<?php echo $icon_path; ?>" alt="<?php echo htmlspecialchars($row["ai_service"]); ?>" 
                                     class="ai-icon mx-auto mb-3" onerror="this.src=\'images/default-ai-icon.png\'">
                                
                                <h6 class="card-title"><?php echo htmlspecialchars($row["ai_service"]); ?></h6>
                                <p class="card-text text-muted small">
                                    <?php echo mb_substr(htmlspecialchars($row["description"]), 0, 60) . "..."; ?>
                                </p>
                                
                                <a href="detail.php?id=<?php echo $row["id"]; ?>" class="btn btn-outline-info btn-sm">
                                    詳細
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center mt-4">
                <a href="list.php?sort=newest" class="btn btn-outline-info">
                    <i class="fas fa-clock me-2"></i>最新をもっと見る
                </a>
            </div>
        </section>
        <?php endif; ?>

        <!-- CTA セクション -->
        <div class="cta-section">
            <h3 class="mb-3">あなたに最適なAIを見つけよう</h3>
            <p class="mb-4"><?php echo $total_count; ?>以上のAIサービスから、用途や予算に合わせて最適なツールを比較・検索できます。</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="list.php" class="btn btn-warning btn-lg">
                    <i class="fas fa-search me-2"></i>AIを探す
                </a>
                <a href="AI_comparison.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-balance-scale me-2"></i>比較する
                </a>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
        document.querySelectorAll(".ai-card").forEach((card, index) => {
            card.style.opacity = "0";
            card.style.transform = "translateY(30px)";
            card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });
    </script>
</body>
</html>';

// AI_index.phpを更新
$ai_index_file = 'AI_index.php';
$ai_index_backup = 'AI_index_backup_' . date('Ymd_His') . '.php';

if (file_exists($ai_index_file)) {
    if (copy($ai_index_file, $ai_index_backup)) {
        echo "<p style='color:green'>✓ AI_index.phpのバックアップを作成しました: $ai_index_backup</p>";
    }
    
    if (file_put_contents($ai_index_file, $ai_index_content)) {
        echo "<p style='color:green'>✓ AI_index.phpを修正しました</p>";
    } else {
        echo "<p style='color:red'>✗ AI_index.phpの更新に失敗しました</p>";
    }
} else {
    if (file_put_contents($ai_index_file, $ai_index_content)) {
        echo "<p style='color:green'>✓ AI_index.phpを新規作成しました</p>";
    } else {
        echo "<p style='color:red'>✗ AI_index.phpの作成に失敗しました</p>";
    }
}

echo "<h2>🔧 AI_index.phpの修正内容</h2>";
echo "<ul>";
echo "<li>✨ <strong>モダンなヒーローセクション</strong> - グラデーション背景とリアルタイム統計</li>";
echo "<li>📊 <strong>動的統計表示</strong> - データベースから実際の数値を取得</li>";
echo "<li>⭐ <strong>おすすめセクション</strong> - featured フラグのサービスを表示</li>";
echo "<li>🆕 <strong>最新サービス</strong> - 作成日順の新着サービス</li>";
echo "<li>🎭 <strong>スクロールアニメーション</strong> - 段階的なカード表示</li>";
echo "<li>📱 <strong>完全レスポンシブ</strong> - モバイルファースト設計</li>";
echo "<li>🔗 <strong>適切なリンク</strong> - list.phpとAI_comparison.phpへの正しいリンク</li>";
echo "</ul>";

echo "<h2>🧪 テスト方法</h2>";
echo "<ol>";
echo "<li><a href='AI_index.php' target='_blank'>修正されたAI_index.phpを確認</a></li>";
echo "<li><a href='list.php' target='_blank'>カード型一覧ページを確認</a></li>";
echo "<li>レスポンシブデザインをテスト（画面サイズ変更）</li>";
echo "<li>スクロールアニメーションを確認</li>";
echo "</ol>";

echo "<h2>📝 注意事項</h2>";
echo "<ul>";
echo "<li>AI_index.phpがメインのトップページとして機能します</li>";
echo "<li>統計情報はデータベースからリアルタイムで取得されます</li>";
echo "<li>アイコンが見つからない場合はdefault-ai-icon.pngにフォールバックします</li>";
echo "<li>すべてのリンクが正しいファイル名を参照しています</li>";
echo "</ul>";

echo "</body></html>";
?>
