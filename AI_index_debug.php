<?php
// エラー表示を有効にする（デバッグ用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- Debug: Starting AI_index.php -->\n";

try {
    echo "<!-- Debug: Including db_connect_extended.php -->\n";
    require_once 'db_connect_extended.php';
    echo "<!-- Debug: db_connect_extended.php loaded -->\n";

    $pageTitle = 'AI情報システム - ホーム';
    
    echo "<!-- Debug: Including header -->\n";
    include 'includes/header.php';
    echo "<!-- Debug: Header loaded -->\n";

    echo "<!-- Debug: Getting AI types -->\n";
    $aiTypes = getAITypesFromInfo();
    echo "<!-- Debug: AI types count: " . count($aiTypes) . " -->\n";

    echo "<!-- Debug: Getting top services -->\n";
    $topServices = getTopAIServices(6);
    echo "<!-- Debug: Top services count: " . count($topServices) . " -->\n";

    echo "<!-- Debug: Getting total services count -->\n";
    $totalServices = getAIServiceCount();
    echo "<!-- Debug: Total services: $totalServices -->\n";

} catch (Exception $e) {
    echo "<div style='background: red; color: white; padding: 10px;'>";
    echo "<h3>Error occurred:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "</div>";
    exit;
}
?>

<div class="row">
    <div class="col-12">
        <div class="jumbotron bg-primary text-white p-5 rounded mb-4">
            <h1 class="display-4">AI情報システム</h1>
            <p class="lead">様々なAIサービスの情報を比較・検索できるプラットフォーム</p>
            <p>現在 <strong><?php echo $totalServices; ?></strong> のAIサービスを掲載中</p>
        </div>
    </div>
</div>

<!-- 以下、元のコードと同じ -->