<?php
/**
 * index.phpのアイコン表示を修正
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>一覧表示アイコン修正</title></head>";
echo "<body>";
echo "<h1>🖼️ 一覧表示のアイコン修正</h1>";

// index.phpファイルの確認
$index_file = 'index.php';
$backup_file = 'index_backup_' . date('Ymd_His') . '.php';

if (file_exists($index_file)) {
    // バックアップを作成
    if (copy($index_file, $backup_file)) {
        echo "<p style='color:green'>✓ バックアップファイル作成: $backup_file</p>";
    }
    
    // ファイルの内容を読み取り
    $content = file_get_contents($index_file);
    
    // アイコン表示のためのCSSを追加
    $css_to_add = '
<style>
.ai-icon {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    margin-right: 5px;
    vertical-align: middle;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.ai-card {
    transition: transform 0.2s;
    height: 100%;
}
.ai-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.card-img-top {
    height: 120px;
    object-fit: contain;
    padding: 15px;
    background-color: #f8f9fa;
}
</style>';
    
    // CSSを追加（</head>の前に挿入）
    $content = str_replace('</head>', $css_to_add . "\n</head>", $content);
    
    // AIカードのHTMLを修正（アイコンを表示するように）
    $pattern = '/<div class="card[^>]*">\s*<div class="card-body">\s*<h5 class="card-title">(.*?)<\/h5>/s';
    $replacement = '<div class="card ai-card">
        <div class="card-body">
            <h5 class="card-title">
                <?php 
                $icon_path = "images/" . strtolower(str_replace(" ", "-", $row["ai_service"])) . "-icon.png";
                if (file_exists($icon_path)): 
                ?>
                    <img src="<?php echo $icon_path; ?>" alt="<?php echo $row["ai_service"]; ?>" class="ai-icon">
                <?php else: ?>
                    <img src="images/default-ai-icon.png" alt="AI" class="ai-icon" onerror="this.style.display=\'none\'">
                <?php endif; ?>
                $1</h5>';
    
    $content = preg_replace($pattern, $replacement, $content);
    
    // ファイルに書き込み
    if (file_put_contents($index_file, $content)) {
        echo "<p style='color:green'>✓ index.phpのアイコン表示を修正しました</p>";
        echo "<h3>修正内容:</h3>";
        echo "<ul>";
        echo "<li>アイコン表示用のCSSを追加</li>";
        echo "<li>カードタイトルにアイコンを表示</li>";
        echo "<li>アイコンが存在しない場合のフォールバック処理</li>";
        echo "<li>カードのホバーエフェクト追加</li>";
        echo "</ul>";
    } else {
        echo "<p style='color:red'>✗ ファイルの書き込みに失敗しました</p>";
    }
} else {
    echo "<p style='color:red'>✗ index.phpが見つかりません</p>";
    
    // 代替として、一覧表示用のサンプルファイルを提案
    echo "<h3>index.phpが見つからない場合の代替案:</h3>";
    echo "<p>以下のようなコードを含むindex.phpを作成してください：</p>";
    echo "<pre style='background:#f5f5f5;padding:10px;border:1px solid #ddd;overflow:auto;'>";
    echo htmlspecialchars('<?php
include "db_connect_extended.php";

// AIサービス一覧を取得
$sql = "SELECT * FROM ai_tools ORDER BY name";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI情報システム</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .ai-icon {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        margin-right: 5px;
        vertical-align: middle;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .ai-card {
        transition: transform 0.2s;
        height: 100%;
    }
    .ai-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container mt-4">
        <h1>AI情報一覧</h1>
        
        <div class="row row-cols-1 row-cols-md-3 g-4 mt-3">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card ai-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php 
                                    $icon_path = "images/" . strtolower(str_replace(" ", "-", $row["ai_service"])) . "-icon.png";
                                    if (file_exists($icon_path)): 
                                    ?>
                                        <img src="<?php echo $icon_path; ?>" alt="<?php echo $row["ai_service"]; ?>" class="ai-icon">
                                    <?php else: ?>
                                        <img src="images/default-ai-icon.png" alt="AI" class="ai-icon" onerror="this.style.display=\'none\'">
                                    <?php endif; ?>
                                    <?php echo $row["ai_service"]; ?>
                                </h5>
                                <p class="card-text"><?php echo substr($row["description"], 0, 100); ?>...</p>
                                <a href="detail.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary">詳細</a>
                                <a href="AI_comparison.php?ids[]=<?php echo $row["id"]; ?>" class="btn btn-outline-secondary">比較に追加</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        AIサービスが登録されていません。
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include "includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>');
    echo "</pre>";
}

// list.phpファイルの確認と修正
$list_file = 'list.php';
$list_backup_file = 'list_backup_' . date('Ymd_His') . '.php';

if (file_exists($list_file)) {
    // バックアップを作成
    if (copy($list_file, $list_backup_file)) {
        echo "<p style='color:green'>✓ バックアップファイル作成: $list_backup_file</p>";
    }
    
    // ファイルの内容を読み取り
    $list_content = file_get_contents($list_file);
    
    // アイコン表示のためのCSSを追加（もし存在しなければ）
    if (strpos($list_content, '.ai-icon') === false) {
        $css_to_add = '
<style>
.ai-icon {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    margin-right: 5px;
    vertical-align: middle;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
</style>';
        
        // CSSを追加（</head>の前に挿入）
        $list_content = str_replace('</head>', $css_to_add . "\n</head>", $list_content);
    }
    
    // リスト表示にアイコンを追加
    $pattern = '/<td>(.*?)<\/td>/';
    $replacement = '<td>
        <?php 
        $icon_path = "images/" . strtolower(str_replace(" ", "-", $row["ai_service"])) . "-icon.png";
        if (file_exists($icon_path)): 
        ?>
            <img src="<?php echo $icon_path; ?>" alt="<?php echo $row["ai_service"]; ?>" class="ai-icon">
        <?php else: ?>
            <img src="images/default-ai-icon.png" alt="AI" class="ai-icon" onerror="this.style.display=\'none\'">
        <?php endif; ?>
        $1</td>';
    
    // 最初の<td>タグのみを置換（サービス名の列）
    $list_content = preg_replace($pattern, $replacement, $list_content, 1);
    
    // ファイルに書き込み
    if (file_put_contents($list_file, $list_content)) {
        echo "<p style='color:green'>✓ list.phpのアイコン表示も修正しました</p>";
    }
}

echo "<h2>🧪 テスト方法</h2>";
echo "<p>以下のページにアクセスして、アイコンが表示されるか確認してください：</p>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>トップページ（index.php）</a></li>";
echo "<li><a href='list.php' target='_blank'>一覧ページ（list.php）</a></li>";
echo "<li><a href='AI_comparison.php?ids[]=1&ids[]=2' target='_blank'>比較ページ（ChatGPT vs Claude）</a></li>";
echo "</ul>";

echo "<h2>🎯 次のステップ</h2>";
echo "<ol>";
echo "<li>各ページでアイコンが正しく表示されるか確認</li>";
echo "<li>必要に応じて高品質なアイコンに置き換え</li>";
echo "<li>他のページ（detail.phpなど）にもアイコン表示を追加</li>";
echo "</ol>";

echo "</body></html>";
?>
