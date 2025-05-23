<?php
// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース接続情報
$servername = "mysql213.phy.lolipop.lan";
$username = "LAA1337491";
$password = "kami2004";
$dbname = "LAA1337491-nsk";

// 初期化
$error = "";
$success = "";
$categories = [];
$conn = null;

// CSRFトークンの生成と検証
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

try {
    // データベース接続
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // display_orderカラムの追加（存在しない場合）
    $sql = "SHOW COLUMNS FROM AISampleCategories LIKE 'display_order'";
    $result = $conn->query($sql);
    
    if ($result->rowCount() == 0) {
        $sql = "ALTER TABLE AISampleCategories ADD COLUMN display_order INT(11) DEFAULT 0";
        $conn->exec($sql);
        $success = "display_orderカラムを追加しました。";
        
        // 既存のカテゴリにIDと同じ順序を設定
        $sql = "UPDATE AISampleCategories SET display_order = id";
        $conn->exec($sql);
    }
    
    // 並び順の更新処理
    if (isset($_POST['save_order']) && isset($_POST['order']) && isset($_POST['csrf_token'])) {
        // CSRFトークンの検証
        if ($_POST['csrf_token'] !== $csrf_token) {
            throw new Exception("セキュリティトークンが無効です。ページを再読み込みしてください。");
        }
        
        $orders = $_POST['order'];
        
        $stmt = $conn->prepare("UPDATE AISampleCategories SET display_order = :order WHERE id = :id");
        
        foreach ($orders as $id => $order) {
            $stmt->bindParam(':order', $order, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        }
        
        $success = "カテゴリの表示順序を更新しました。";
    }
    
    // カテゴリ一覧の取得（表示順でソート）
    $stmt = $conn->query("SELECT c.*, COUNT(s.id) as sample_count 
                         FROM AISampleCategories c 
                         LEFT JOIN AISampleInfo s ON c.id = s.category_id 
                         GROUP BY c.id 
                         ORDER BY c.display_order, c.name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "データベースエラー: " . $e->getMessage();
} catch(Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カテゴリ表示順序の設定</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-success {
            background-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .order-input {
            width: 60px;
            padding: 5px;
            text-align: center;
        }
        .sortable-list {
            list-style-type: none;
            padding: 0;
        }
        .sortable-item {
            padding: 10px 15px;
            margin-bottom: 5px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: move;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sortable-item:hover {
            background-color: #e9ecef;
        }
        .handle {
            cursor: move;
            padding: 5px;
            color: #6c757d;
        }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 12px;
            font-weight: bold;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 10px;
            background-color: #6c757d;
            margin-left: 10px;
        }
        .instructions {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
    </style>
</head>
<body>
    <h1>カテゴリ表示順序の設定</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="container">
        <h2>カテゴリの並び順</h2>
        
        <div class="instructions">
            <p><strong>使い方:</strong></p>
            <ol>
                <li>カテゴリをドラッグ＆ドロップで並べ替えることができます。</li>
                <li>または、数値を入力して順序を指定することもできます（小さい数字が上に表示されます）。</li>
                <li>並び順を変更したら「保存」ボタンをクリックしてください。</li>
            </ol>
        </div>
        
        <form method="post" action="" id="orderForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <ul id="sortable" class="sortable-list">
                <?php foreach ($categories as $category): ?>
                    <li class="sortable-item" data-id="<?php echo $category['id']; ?>">
                        <div>
                            <span class="handle">&#9776;</span>
                            <?php echo htmlspecialchars($category['name']); ?>
                            <?php if ($category['sample_count'] > 0): ?>
                                <span class="badge"><?php echo $category['sample_count']; ?></span>
                            <?php endif; ?>
                        </div>
                        <input type="number" name="order[<?php echo $category['id']; ?>]" value="<?php echo $category['display_order']; ?>" class="order-input" min="0">
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <button type="submit" name="save_order" class="btn btn-success">保存</button>
            <a href="category_manager.php" class="btn">カテゴリ管理に戻る</a>
        </form>
    </div>

    <script>
        // ドラッグ＆ドロップでの並べ替え機能
        document.addEventListener('DOMContentLoaded', function() {
            const sortableList = document.getElementById('sortable');
            let draggedItem = null;
            
            // ドラッグ開始時の処理
            sortableList.addEventListener('dragstart', function(e) {
                draggedItem = e.target;
                setTimeout(function() {
                    draggedItem.style.opacity = '0.5';
                }, 0);
            });
            
            // ドラッグ終了時の処理
            sortableList.addEventListener('dragend', function(e) {
                draggedItem.style.opacity = '1';
                draggedItem = null;
                
                // 順序の更新
                updateOrder();
            });
            
            // ドラッグ中の処理
            sortableList.addEventListener('dragover', function(e) {
                e.preventDefault();
                const afterElement = getDragAfterElement(sortableList, e.clientY);
                const currentElement = draggedItem;
                
                if (afterElement == null) {
                    sortableList.appendChild(currentElement);
                } else {
                    sortableList.insertBefore(currentElement, afterElement);
                }
            });
            
            // 各アイテムをドラッグ可能に設定
            const items = document.querySelectorAll('.sortable-item');
            items.forEach(item => {
                item.setAttribute('draggable', 'true');
            });
            
            // 入力フィールドの変更時に順序を更新
            const orderInputs = document.querySelectorAll('.order-input');
            orderInputs.forEach(input => {
                input.addEventListener('change', function() {
                    sortItems();
                });
            });
            
            // ドラッグ後の位置を決定する関数
            function getDragAfterElement(container, y) {
                const draggableElements = [...container.querySelectorAll('.sortable-item:not([dragging])')];
                
                return draggableElements.reduce((closest, child) => {
                    const box = child.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;
                    
                    if (offset < 0 && offset > closest.offset) {
                        return { offset: offset, element: child };
                    } else {
                        return closest;
                    }
                }, { offset: Number.NEGATIVE_INFINITY }).element;
            }
            
            // 順序を更新する関数
            function updateOrder() {
                const items = document.querySelectorAll('.sortable-item');
                items.forEach((item, index) => {
                    const id = item.getAttribute('data-id');
                    const input = item.querySelector(`.order-input[name="order[${id}]"]`);
                    input.value = index + 1;
                });
            }
            
            // 入力値に基づいてアイテムをソートする関数
            function sortItems() {
                const items = Array.from(document.querySelectorAll('.sortable-item'));
                
                items.sort((a, b) => {
                    const aId = a.getAttribute('data-id');
                    const bId = b.getAttribute('data-id');
                    const aOrder = parseInt(a.querySelector(`.order-input[name="order[${aId}]"]`).value);
                    const bOrder = parseInt(b.querySelector(`.order-input[name="order[${bId}]"]`).value);
                    
                    return aOrder - bOrder;
                });
                
                items.forEach(item => {
                    sortableList.appendChild(item);
                });
            }
        });
    </script>
</body>
</html>