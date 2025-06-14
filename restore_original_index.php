<?php
/**
 * 元のindex.phpを復元するためのバックアップ確認・復元ツール
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>index.php復元ツール</title>";
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

echo "<h1>🔄 index.php復元ツール</h1>";
echo "<p>実行時刻: " . date('Y-m-d H:i:s') . "</p>";

// 1. バックアップファイルの検索
echo "<div class='section'>";
echo "<h2>📁 バックアップファイル検索</h2>";

$backup_patterns = [
    'index_backup_*.php',
    'index.php.backup.*',
    '*index*backup*.php'
];

$backup_files = [];
foreach ($backup_patterns as $pattern) {
    $files = glob($pattern);
    if ($files) {
        $backup_files = array_merge($backup_files, $files);
    }
}

// 重複を除去してソート
$backup_files = array_unique($backup_files);
usort($backup_files, function($a, $b) {
    return filemtime($b) - filemtime($a); // 新しい順
});

if (!empty($backup_files)) {
    echo "<p class='success'>✓ " . count($backup_files) . " 個のバックアップファイルが見つかりました</p>";
    
    echo "<table>";
    echo "<tr><th>ファイル名</th><th>サイズ</th><th>更新日時</th><th>プレビュー</th><th>アクション</th></tr>";
    
    foreach ($backup_files as $file) {
        $size = filesize($file);
        $modified = date('Y-m-d H:i:s', filemtime($file));
        
        echo "<tr>";
        echo "<td><strong>$file</strong></td>";
        echo "<td>" . number_format($size) . " bytes</td>";
        echo "<td>$modified</td>";
        echo "<td><a href='#' onclick='showPreview(\"$file\")' class='btn'>プレビュー</a></td>";
        echo "<td>";
        echo "<form method='post' style='display:inline;'>";
        echo "<input type='hidden' name='restore_file' value='$file'>";
        echo "<button type='submit' class='btn btn-success' onclick='return confirm(\"このファイルでindex.phpを復元しますか？\")'>復元</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p class='error'>✗ バックアップファイルが見つかりませんでした</p>";
}
echo "</div>";

// 2. 復元処理
if (isset($_POST['restore_file'])) {
    $restore_file = $_POST['restore_file'];
    
    echo "<div class='section'>";
    echo "<h2>🔄 復元処理</h2>";
    
    if (file_exists($restore_file)) {
        // 現在のindex.phpをバックアップ
        $current_backup = 'index_current_backup_' . date('Ymd_His') . '.php';
        if (file_exists('index.php')) {
            copy('index.php', $current_backup);
            echo "<p class='info'>ℹ 現在のindex.phpを $current_backup にバックアップしました</p>";
        }
        
        // 復元実行
        if (copy($restore_file, 'index.php')) {
            echo "<p class='success'>✓ $restore_file からindex.phpを復元しました</p>";
            echo "<p><a href='index.php' target='_blank' class='btn btn-success'>復元されたindex.phpを確認</a></p>";
        } else {
            echo "<p class='error'>✗ 復元に失敗しました</p>";
        }
    } else {
        echo "<p class='error'>✗ 指定されたバックアップファイルが見つかりません</p>";
    }
    echo "</div>";
}

// 3. 手動復元用の元のindex.phpコード
echo "<div class='section'>";
echo "<h2>📝 元のランディングページコード（手動復元用）</h2>";
echo "<p>バックアップファイルが見つからない場合は、以下のコードを使用してindex.phpを手動で復元できます：</p>";

$original_index_code = '<?php
// データベース接続
include "db_connect.php";

// 統計情報を取得
$stats = [];
$stats["total_services"] = 0;
$stats["free_services"] = 0;
$stats["featured_services"] = 0;
$stats["avg_rating"] = 0;

if ($conn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM ai_tools");
    if ($result) {
        $stats["total_services"] = $result->fetch_assoc()["count"];
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM ai_tools WHERE is_free = 1");
    if ($result) {
        $stats["free_services"] = $result->fetch_assoc()["count"];
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM ai_tools WHERE is_featured = 1");
    if ($result) {
        $stats["featured_services"] = $result->fetch_assoc()["count"];
    }
    
    $result = $conn->query("SELECT AVG(rating) as avg_rating FROM ai_tools WHERE rating > 0");
    if ($result) {
        $stats["avg_rating"] = round($result->fetch_assoc()["avg_rating"], 1);
    }
}

include "includes/header.php";
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI情報システム - ランディングページ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5rem 0;
        }
        
        .feature-card {
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .stats-section {
            background: #f8f9fa;
            padding: 3rem 0;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <!-- ヒーローセクション -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-3 fw-bold mb-4">AI情報システム</h1>
                    <p class="lead mb-4">
                        最新のAIサービスを比較・検索できる総合プラットフォーム。
                        あなたに最適なAIツールを見つけましょう。
                    </p>
                    <div class="d-flex gap-3">
                        <a href="list.php" class="btn btn-light btn-lg">
                            <i class="fas fa-list me-2"></i>一覧を見る
                        </a>
                        <a href="AI_comparison.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-balance-scale me-2"></i>比較する
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-robot" style="font-size: 8rem; opacity: 0.8;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- 統計セクション -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="stat-number"><?php echo $stats["total_services"]; ?></div>
                    <h5>AIサービス</h5>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-number"><?php echo $stats["free_services"]; ?></div>
                    <h5>無料サービス</h5>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-number"><?php echo $stats["featured_services"]; ?></div>
                    <h5>おすすめサービス</h5>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-number"><?php echo $stats["avg_rating"]; ?></div>
                    <h5>平均評価</h5>
                </div>
            </div>
        </div>
    </section>

    <!-- 機能紹介セクション -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col">
                    <h2 class="display-5 fw-bold">主な機能</h2>
                    <p class="lead">AIサービス選びをサポートする豊富な機能</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card border-0 shadow">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-search fa-3x text-primary mb-3"></i>
                            <h4>検索・フィルター</h4>
                            <p>用途や価格帯でAIサービスを簡単検索</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card border-0 shadow">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-balance-scale fa-3x text-success mb-3"></i>
                            <h4>比較機能</h4>
                            <p>複数のAIサービスを並べて詳細比較</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card border-0 shadow">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-star fa-3x text-warning mb-3"></i>
                            <h4>評価・レビュー</h4>
                            <p>実際のユーザー評価とレビューを確認</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA セクション -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">今すぐ始めよう</h2>
            <p class="lead mb-4">あなたに最適なAIサービスを見つけて、作業効率を向上させましょう</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="list.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket me-2"></i>AIを探す
                </a>
                <a href="AI_comparison.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-chart-bar me-2"></i>比較を始める
                </a>
            </div>
        </div>
    </section>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>';

echo "<textarea readonly style='width:100%; height:300px; font-family:monospace; font-size:12px;'>";
echo htmlspecialchars($original_index_code);
echo "</textarea>";

echo "<form method='post' style='margin-top:10px;'>";
echo "<input type='hidden' name='create_original' value='1'>";
echo "<button type='submit' class='btn btn-warning' onclick='return confirm(\"元のランディングページコードでindex.phpを作成しますか？\")'>このコードでindex.phpを作成</button>";
echo "</form>";
echo "</div>";

// 4. 元のコードでindex.phpを作成
if (isset($_POST['create_original'])) {
    echo "<div class='section'>";
    echo "<h2>📝 元のindex.php作成</h2>";
    
    // 現在のindex.phpをバックアップ
    $current_backup = 'index_current_backup_' . date('Ymd_His') . '.php';
    if (file_exists('index.php')) {
        copy('index.php', $current_backup);
        echo "<p class='info'>ℹ 現在のindex.phpを $current_backup にバックアップしました</p>";
    }
    
    // 元のコードでindex.phpを作成
    if (file_put_contents('index.php', $original_index_code)) {
        echo "<p class='success'>✓ 元のランディングページコードでindex.phpを作成しました</p>";
        echo "<p><a href='index.php' target='_blank' class='btn btn-success'>復元されたindex.phpを確認</a></p>";
    } else {
        echo "<p class='error'>✗ index.phpの作成に失敗しました</p>";
    }
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>🎯 次のステップ</h2>";
echo "<ol>";
echo "<li>上記のバックアップファイルから適切なものを選んで復元</li>";
echo "<li>または手動復元用コードを使用してindex.phpを再作成</li>";
echo "<li><a href='index.php' target='_blank'>復元されたindex.phpを確認</a></li>";
echo "<li>必要に応じてAI_index.phpとの使い分けを検討</li>";
echo "</ol>";
echo "</div>";

echo "<script>";
echo "function showPreview(filename) {";
echo "  window.open('restore_original_index.php?preview=' + encodeURIComponent(filename), '_blank', 'width=800,height=600,scrollbars=yes');";
echo "}";
echo "</script>";

// プレビュー機能
if (isset($_GET['preview'])) {
    $preview_file = $_GET['preview'];
    if (file_exists($preview_file)) {
        echo "<h3>プレビュー: $preview_file</h3>";
        echo "<pre>";
        echo htmlspecialchars(file_get_contents($preview_file));
        echo "</pre>";
    }
    exit;
}

echo "</body></html>";
?>
