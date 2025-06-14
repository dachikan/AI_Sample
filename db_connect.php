<?php
// データベース接続情報
$host = "mysql213.phy.lolipop.lan";
$user = "LAA1337491";
$password = "kami2004";
$database = "LAA1337491-nsk";

// 関数が既に定義されているかチェック
$functions_defined = false;

// 接続エラーをキャッチするためのtry-catch
try {
    // MySQLi接続
    $conn = new mysqli($host, $user, $password, $database);

    // 接続エラーチェック
    if ($conn->connect_error) {
        throw new Exception("データベース接続エラー: " . $conn->connect_error);
    }

    // 文字セットをUTF-8に設定
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // エラーメッセージを表示
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 4px;">';
    echo '<h3>データベース接続エラー</h3>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<p>サーバー情報: ' . $host . ', ユーザー: ' . $user . ', データベース: ' . $database . '</p>';
    echo '<p>config/db_connect.phpの接続情報を確認してください。</p>';
    echo '</div>';
    
    // エラーログに記録
    error_log("DB接続エラー: " . $e->getMessage());
    
    // ダミーデータを使用するモードに設定
    $conn = null;
}

// 以下の関数は一度だけ定義する
if (!function_exists('getCategories')) {
    /**
     * カテゴリ一覧を取得
     */
    function getCategories() {
        global $conn;
        
        // 接続がない場合はダミーデータを返す
        if ($conn === null) {
            return [['id' => 1, 'name' => 'サンプルカテゴリ']];
        }
        
        $sql = "SELECT * FROM AIPromptCategories ORDER BY name ASC";
        $result = $conn->query($sql);
        
        $categories = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        
        return $categories;
    }

    /**
     * AIタイプ一覧を取得
     */
    function getAITypes() {
        global $conn;
        
        // 接続がない場合はダミーデータを返す
        if ($conn === null) {
            return [
                ['id' => 1, 'name' => 'ChatGPT', 'group' => 'テキスト生成AI'],
                ['id' => 2, 'name' => 'DALL-E', 'group' => '画像生成AI'],
                ['id' => 3, 'name' => 'Suno', 'group' => '音声・音楽生成AI']
            ];
        }
        
        $sql = "SELECT * FROM AITypes ORDER BY name ASC";
        $result = $conn->query($sql);
        
        $aiTypes = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $aiTypes[] = $row;
            }
        }
        
        return $aiTypes;
    }

    /**
     * エスケープ処理
     */
    function escape($string) {
        global $conn;
        
        // 接続がない場合はそのまま返す
        if ($conn === null) {
            return $string;
        }
        
        return $conn->real_escape_string($string);
    }

    /**
     * SQLインジェクション対策済みのクエリ実行
     */
    function safeQuery($sql, $params = []) {
        global $conn;
        
        // 接続がない場合はnullを返す
        if ($conn === null) {
            return null;
        }
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $types = '';
            $bindParams = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                
                $bindParams[] = $param;
            }
            
            $bindValues = array_merge([$types], $bindParams);
            call_user_func_array([$stmt, 'bind_param'], $bindValues);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }
}
?>
