<?php
// エラー表示を有効化（開発時のみ）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// セッション開始
session_start();

// データベース接続
require_once 'config/db_connect.php';

// 接続エラーチェック
if (!$conn) {
    die("データベース接続エラー");
}

try {
    // AITrialResults、AIタイプ、プロンプトカテゴリを結合するクエリ
    $sql = "SELECT r.*, 
            t.name AS ai_type_name, 
            c.name AS category_name 
            FROM AITrialResults r
            LEFT JOIN AITypes t ON r.ai_type_id = t.id
            LEFT JOIN PromptCategories c ON r.category_id = c.id
            ORDER BY r.created_at DESC";

    $stmt = $conn->query($sql);
    
    // 結果の行数を取得
    $row_count = $stmt->rowCount();
} catch (PDOException $e) {
    die("クエリエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIプロンプトサンプル一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">AIプロンプトサンプル一覧</h1>
            <div class="flex space-x-2">
                <a href="AISample-FreeTry.php" class="bg-blue-100 text-blue-700 px-4 py-2 rounded-md hover:bg-blue-200">
                    AIを試す
                </a>
                <a href="AISample-AllTry.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    + 新規サンプル登録
                </a>
            </div>
        </header>

        <div class="flex flex-col md:flex-row gap-6">
            <!-- 左サイドバー -->
            <div class="w-full md:w-64 bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-semibold">フィルター</h2>
                </div>
                
                <div class="mb-4">
                    <input type="text" placeholder="プロンプトを検索..." class="w-full px-3 py-2 border rounded-md">
                </div>
                
                <div class="flex space-x-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-3 flex-1 text-center">
                        <div class="text-xl font-bold text-blue-600"><?php echo $row_count; ?></div>
                        <div class="text-xs text-gray-600">総サンプル数</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3 flex-1 text-center">
                        <div class="text-xl font-bold text-green-600">0</div>
                        <div class="text-xs text-gray-600">新着サンプル</div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h3 class="font-medium mb-2">プロンプトカテゴリ</h3>
                    <ul class="space-y-1">
                        <?php
                        try {
                            // カテゴリの取得
                            $cat_sql = "SELECT c.id, c.name, COUNT(r.id) as count 
                                        FROM PromptCategories c
                                        LEFT JOIN AITrialResults r ON c.id = r.category_id
                                        GROUP BY c.id
                                        ORDER BY c.name";
                            $cat_stmt = $conn->query($cat_sql);
                            
                            if ($cat_stmt && $cat_stmt->rowCount() > 0) {
                                while ($cat = $cat_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<li>
                                        <a href="?category_id=' . $cat['id'] . '" class="flex justify-between items-center p-2 hover:bg-gray-100 rounded">
                                            <span>' . htmlspecialchars($cat['name']) . '</span>
                                            <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">' . $cat['count'] . '</span>
                                        </a>
                                    </li>';
                                }
                            } else {
                                echo '<li class="text-gray-500">カテゴリがありません</li>';
                            }
                        } catch (PDOException $e) {
                            echo '<li class="text-red-500">カテゴリの取得中にエラーが発生しました</li>';
                        }
                        ?>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-medium mb-2">AIサービスタイプ</h3>
                    <ul class="space-y-1">
                        <?php
                        try {
                            // AIタイプの取得
                            $type_sql = "SELECT t.id, t.name, COUNT(r.id) as count 
                                        FROM AITypes t
                                        LEFT JOIN AITrialResults r ON t.id = r.ai_type_id
                                        GROUP BY t.id
                                        ORDER BY t.name";
                            $type_stmt = $conn->query($type_sql);
                            
                            if ($type_stmt && $type_stmt->rowCount() > 0) {
                                while ($type = $type_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<li>
                                        <a href="?ai_type_id=' . $type['id'] . '" class="flex justify-between items-center p-2 hover:bg-gray-100 rounded">
                                            <span>' . htmlspecialchars($type['name']) . '</span>
                                            <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">' . $type['count'] . '</span>
                                        </a>
                                    </li>';
                                }
                            } else {
                                echo '<li class="text-gray-500">AIタイプがありません</li>';
                            }
                        } catch (PDOException $e) {
                            echo '<li class="text-red-500">AIタイプの取得中にエラーが発生しました</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <!-- メインコンテンツ -->
            <div class="flex-1">
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <h2 class="font-semibold mb-4">
                        <?php 
                        if (isset($_GET['category_id'])) {
                            $cat_id = $_GET['category_id'];
                            try {
                                $cat_name_sql = "SELECT name FROM PromptCategories WHERE id = :id";
                                $cat_name_stmt = $conn->prepare($cat_name_sql);
                                $cat_name_stmt->bindParam(':id', $cat_id, PDO::PARAM_INT);
                                $cat_name_stmt->execute();
                                
                                if ($cat_name_stmt && $cat_row = $cat_name_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo htmlspecialchars($cat_row['name']) . " カテゴリのプロンプト";
                                }
                            } catch (PDOException $e) {
                                echo "カテゴリ情報の取得中にエラーが発生しました";
                            }
                        } elseif (isset($_GET['ai_type_id'])) {
                            $type_id = $_GET['ai_type_id'];
                            try {
                                $type_name_sql = "SELECT name FROM AITypes WHERE id = :id";
                                $type_name_stmt = $conn->prepare($type_name_sql);
                                $type_name_stmt->bindParam(':id', $type_id, PDO::PARAM_INT);
                                $type_name_stmt->execute();
                                
                                if ($type_name_stmt && $type_row = $type_name_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo htmlspecialchars($type_row['name']) . " のプロンプト";
                                }
                            } catch (PDOException $e) {
                                echo "AIタイプ情報の取得中にエラーが発生しました";
                            }
                        } else {
                            echo "すべてのプロンプト (" . $row_count . "件)";
                        }
                        ?>
                    </h2>
                    
                    <div class="flex justify-end mb-4">
                        <select class="border rounded-md px-3 py-1">
                            <option>並び替え</option>
                            <option>新しい順</option>
                            <option>古い順</option>
                        </select>
                    </div>
                </div>

                <!-- サンプル一覧 -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    try {
                        // フィルタリング条件の適用
                        $filtered_sql = $sql;
                        $params = [];
                        
                        if (isset($_GET['category_id'])) {
                            $cat_id = intval($_GET['category_id']);
                            $filtered_sql = "SELECT r.*, 
                                            t.name AS ai_type_name, 
                                            c.name AS category_name 
                                            FROM AITrialResults r
                                            LEFT JOIN AITypes t ON r.ai_type_id = t.id
                                            LEFT JOIN PromptCategories c ON r.category_id = c.id
                                            WHERE r.category_id = :category_id
                                            ORDER BY r.created_at DESC";
                            $params[':category_id'] = $cat_id;
                        } elseif (isset($_GET['ai_type_id'])) {
                            $type_id = intval($_GET['ai_type_id']);
                            $filtered_sql = "SELECT r.*, 
                                            t.name AS ai_type_name, 
                                            c.name AS category_name 
                                            FROM AITrialResults r
                                            LEFT JOIN AITypes t ON r.ai_type_id = t.id
                                            LEFT JOIN PromptCategories c ON r.category_id = c.id
                                            WHERE r.ai_type_id = :ai_type_id
                                            ORDER BY r.created_at DESC";
                            $params[':ai_type_id'] = $type_id;
                        }
                        
                        $filtered_stmt = $conn->prepare($filtered_sql);
                        $filtered_stmt->execute($params);
                        
                        if ($filtered_stmt && $filtered_stmt->rowCount() > 0) {
                            while ($row = $filtered_stmt->fetch(PDO::FETCH_ASSOC)) {
                                // 画像URLの取得（存在する場合）
                                $image_url = !empty($row['image_url']) ? $row['image_url'] : null;
                                
                                // 作成日のフォーマット
                                $created_date = date('Y年m月d日', strtotime($row['created_at']));
                                
                                // プロンプトの短縮表示
                                $short_prompt = mb_substr($row['prompt'], 0, 100) . (mb_strlen($row['prompt']) > 100 ? '...' : '');
                                
                                echo '<div class="bg-white rounded-lg shadow overflow-hidden">
                                    <div class="p-4">
                                        <h3 class="font-semibold text-lg mb-2">' . htmlspecialchars($row['title']) . '</h3>
                                        <div class="flex space-x-2 mb-3">
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">' . 
                                                htmlspecialchars($row['category_name'] ?? '未分類') . 
                                            '</span>
                                            <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">' . 
                                                htmlspecialchars($row['ai_type_name'] ?? '不明') . 
                                            '</span>
                                        </div>';
                                        
                                if ($image_url) {
                                    echo '<div class="mb-3">
                                        <img src="' . htmlspecialchars($image_url) . '" alt="サンプル画像" class="w-full h-40 object-cover rounded">
                                    </div>';
                                }
                                        
                                echo '<p class="text-sm text-gray-600 mb-3">' . nl2br(htmlspecialchars($short_prompt)) . '</p>
                                        
                                        <div class="flex justify-between items-center text-xs text-gray-500">
                                            <span>' . $created_date . '</span>
                                            <a href="AISample-Detail.php?id=' . $row['id'] . '" class="text-blue-600 hover:underline">詳細を見る</a>
                                        </div>
                                    </div>
                                </div>';
                            }
                        } else {
                            echo '<div class="col-span-3 text-center py-12 text-gray-500">
                                <p class="mb-2">該当するサンプルがありません</p>
                                <p>新しいサンプルを追加するか、フィルター条件を変更してください</p>
                            </div>';
                        }
                    } catch (PDOException $e) {
                        echo '<div class="col-span-3 text-center py-12 text-red-500">
                            <p class="mb-2">データの取得中にエラーが発生しました</p>
                            <p>' . htmlspecialchars($e->getMessage()) . '</p>
                        </div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-gray-800 text-white mt-12 py-6">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">AI活用サンプル集</h3>
                    <p class="text-sm text-gray-400">初心者に役立つプロンプトのサンプル集</p>
                </div>
                <div class="text-sm text-gray-400 mt-4 md:mt-0">
                    &copy; 2023-2025 AI活用サンプル集. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
<?php
// PDOの接続を閉じる（明示的に閉じる必要はないが、念のため）
$conn = null;
?>