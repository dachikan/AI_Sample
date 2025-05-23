<?php
// セッション開始（必ずファイルの先頭で行う）
session_start();

// データベース接続設定
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "ai_samples_db";

// データベース接続
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// サンプルIDの取得と検証
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // IDが指定されていないか無効な場合はエラーメッセージを表示
    $error_message = "有効なサンプルIDが指定されていません。";
} else {
    $sample_id = $_GET['id'];
    
    // サンプルの詳細情報を取得
    $sql = "SELECT s.id, s.title, s.prompt, s.result, s.created_at, s.image_url,
                  c.name as category_name, c.id as category_id,
                  a.name as ai_type_name, a.id as ai_type_id
           FROM samples s
           JOIN prompt_categories c ON s.category_id = c.id
           JOIN ai_types a ON s.ai_type_id = a.id
           WHERE s.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sample_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $sample = $result->fetch_assoc();
        
        // 関連サンプルを取得（同じカテゴリの他のサンプル）
        $sql_related = "SELECT id, title, image_url 
                       FROM samples 
                       WHERE category_id = ? AND id != ? 
                       ORDER BY created_at DESC 
                       LIMIT 3";
        
        $stmt_related = $conn->prepare($sql_related);
        $stmt_related->bind_param("ii", $sample['category_id'], $sample_id);
        $stmt_related->execute();
        $related_samples = $stmt_related->get_result();
        
        // 閲覧履歴を更新（実際のアプリケーションでは必要に応じて実装）
        // ここでは簡略化のため省略
    } else {
        $error_message = "指定されたIDのサンプルは見つかりませんでした。";
    }
}

// 前のページに戻るURLを設定
$back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

// 共有機能のフラグ（URLパラメータから取得）
$shared = isset($_GET['shared']) && $_GET['shared'] == 1;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($sample) ? htmlspecialchars($sample['title']) . " - AI生成結果" : "サンプル詳細"; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <!-- ヘッダー部分 -->
        <div class="flex justify-between items-center mb-6">
            <a href="<?php echo $back_url; ?>" class="flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                戻る
            </a>
            <h1 class="text-2xl font-bold text-center">サンプル詳細</h1>
            <div class="w-24"></div> <!-- スペーサー -->
        </div>
        
        <?php if (isset($error_message)): ?>
        <!-- エラーメッセージ -->
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <p><?php echo $error_message; ?></p>
            <p class="mt-2">
                <a href="index.php" class="text-red-700 underline">サンプル一覧に戻る</a>
            </p>
        </div>
        <?php elseif (isset($sample)): ?>
        
        <?php if ($shared): ?>
        <!-- 共有成功メッセージ -->
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <p>このサンプルが正常に共有されました。共有リンクをコピーして他の人と共有できます。</p>
        </div>
        <?php endif; ?>
        
        <!-- サンプル詳細 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- サンプルヘッダー -->
            <div class="p-6 border-b">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($sample['title']); ?></h2>
                        <div class="flex flex-wrap gap-2">
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                <?php echo htmlspecialchars($sample['category_name']); ?>
                            </span>
                            <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">
                                <?php echo htmlspecialchars($sample['ai_type_name']); ?>
                            </span>
                            <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">
                                作成日: <?php echo date('Y年m月d日', strtotime($sample['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="edit.php?id=<?php echo $sample['id']; ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            編集
                        </a>
                        <a href="share.php?id=<?php echo $sample['id']; ?>" class="px-4 py-2 bg-blue-600 rounded-md text-sm font-medium text-white hover:bg-blue-700">
                            共有
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- サンプル画像（存在する場合） -->
            <?php if (!empty($sample['image_url'])): ?>
            <div class="border-b">
                <img src="<?php echo htmlspecialchars($sample['image_url']); ?>" alt="<?php echo htmlspecialchars($sample['title']); ?>" class="w-full h-auto max-h-96 object-contain">
            </div>
            <?php endif; ?>
            
            <!-- サンプル内容 -->
            <div class="p-6">
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">プロンプト</h3>
                    <div class="bg-gray-50 p-4 rounded-md whitespace-pre-line">
                        <?php echo nl2br(htmlspecialchars($sample['prompt'])); ?>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-3">生成結果</h3>
                    <div class="bg-gray-50 p-4 rounded-md whitespace-pre-line">
                        <?php echo nl2br(htmlspecialchars($sample['result'])); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 関連サンプル -->
        <?php if ($related_samples->num_rows > 0): ?>
        <div class="mt-10">
            <h3 class="text-xl font-bold mb-4">関連サンプル</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php while($related = $related_samples->fetch_assoc()): ?>
                <a href="AISample-Detail.php?id=<?php echo $related['id']; ?>" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <?php if (!empty($related['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="w-full h-40 object-cover">
                    <?php else: ?>
                    <div class="w-full h-40 bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-500">画像なし</span>
                    </div>
                    <?php endif; ?>
                    <div class="p-4">
                        <h4 class="font-medium text-gray-900 line-clamp-2"><?php echo htmlspecialchars($related['title']); ?></h4>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
    
    <?php if (isset($conn)) $conn->close(); ?>
</body>
</html>