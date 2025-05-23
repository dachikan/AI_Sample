﻿<?php
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
/**
* カテゴリごとの試行結果数を取得
*/
function getCategoryTrialCounts() {
    global $conn;
    
    $sql = "SELECT c.id as category_id, COUNT(r.id) as count 
            FROM AIPromptCategories c
            JOIN AIPromptTemplates t ON c.id = t.category_id
            JOIN AITrialResults r ON t.id = r.template_id
            GROUP BY c.id";
    
    $result = $conn->query($sql);
    $counts = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $counts[] = $row;
        }
    }
    
    return $counts;
}

/**
* AIタイプごとの試行結果数を取得
*/
function getAITypeTrialCounts() {
    global $conn;
    
    $sql = "SELECT a.id as ai_type_id, COUNT(r.id) as count 
            FROM AITypes a
            JOIN AITrialResults r ON a.id = r.ai_type_id
            GROUP BY a.id";
    
    $result = $conn->query($sql);
    $counts = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $counts[] = $row;
        }
    }
    
    return $counts;
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
     * プロンプト数を取得
     */
    function getPromptCount() {
        global $conn;
        
        // 接続がない場合は0を返す
        if ($conn === null) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as count FROM AIPromptTemplates";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['count'];
        }
        
        return 0;
    }

    /**
     * カテゴリごとのプロンプト数を取得
     */
    function getPromptCountByCategory() {
        global $conn;
        
        // 接続がない場合はダミーデータを返す
        if ($conn === null) {
            return [['id' => 1, 'name' => 'サンプルカテゴリ', 'count' => 0]];
        }
        
        $sql = "SELECT c.id, c.name, COUNT(t.id) as count 
                FROM AIPromptCategories c
                LEFT JOIN AIPromptTemplates t ON c.id = t.category_id
                GROUP BY c.id
                ORDER BY c.name ASC";
        
        $result = $conn->query($sql);
        
        $counts = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $counts[] = $row;
            }
        }
        
        return $counts;
    }

    /**
     * AIタイプごとのプロンプト数を取得
     */
    function getPromptCountByAIType() {
        global $conn;
        
        // 接続がない場合はダミーデータを返す
        if ($conn === null) {
            return [
                ['id' => 1, 'name' => 'ChatGPT', 'count' => 0],
                ['id' => 2, 'name' => 'DALL-E', 'count' => 0],
                ['id' => 3, 'name' => 'Suno', 'count' => 0]
            ];
        }
        
        $sql = "SELECT a.id, a.name, COUNT(t.id) as count 
                FROM AITypes a
                LEFT JOIN AIPromptTemplates t ON a.id = t.ai_type_id
                GROUP BY a.id
                ORDER BY a.name ASC";
        
        $result = $conn->query($sql);
        
        $counts = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $counts[] = $row;
            }
        }
        
        return $counts;
    }

    /**
     * 指定されたカテゴリとAIタイプに該当するテンプレート数を取得
     */
    function getTemplateCount($categoryId, $aiTypeId) {
        global $conn;
        
        // 接続がない場合は0を返す
        if ($conn === null) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as count FROM AIPromptTemplates 
                WHERE category_id = ? AND ai_type_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $categoryId, $aiTypeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] ?? 0;
    }

    /**
     * 指定されたカテゴリとAIタイプに該当するテンプレート一覧を取得
     */
    function getTemplates($categoryId, $aiTypeId) {
        global $conn;
        
        // 接続がない場合は空の配列を返す
        if ($conn === null) {
            return [];
        }
        
        $sql = "SELECT * FROM AIPromptTemplates 
                WHERE category_id = ? AND ai_type_id = ? 
                ORDER BY name ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $categoryId, $aiTypeId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $templates = [];
        while ($row = $result->fetch_assoc()) {
            $templates[] = $row;
        }
        
        return $templates;
    }

    /**
     * 指定されたIDのテンプレートを取得
     */
    function getTemplateById($templateId) {
        global $conn;
        
        // 接続がない場合はnullを返す
        if ($conn === null) {
            return null;
        }
        
        $sql = "SELECT * FROM AIPromptTemplates WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $templateId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * テンプレートから変数を抽出
     */
    function extractTemplateVariables($content) {
        preg_match_all('/\{([^}]+)\}/', $content, $matches);
        return $matches[1] ?? [];
    }

    /**
     * テンプレートを保存
     */
    function saveTemplate($name, $content, $categoryId, $aiTypeId, $templateId = null) {
        global $conn;
        
        // 接続がない場合はfalseを返す
        if ($conn === null) {
            return false;
        }
        
        if ($templateId) {
            // 更新
            $sql = "UPDATE AIPromptTemplates 
                    SET name = ?, content = ?, category_id = ?, ai_type_id = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiii", $name, $content, $categoryId, $aiTypeId, $templateId);
        } else {
            // 新規作成
            $sql = "INSERT INTO AIPromptTemplates (name, content, category_id, ai_type_id, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $name, $content, $categoryId, $aiTypeId);
        }
        
        return $stmt->execute();
    }

    /**
     * テンプレートを削除
     */
    function deleteTemplate($templateId) {
        global $conn;
        
        // 接続がない場合はfalseを返す
        if ($conn === null) {
            return false;
        }
        
        $sql = "DELETE FROM AIPromptTemplates WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $templateId);
        
        return $stmt->execute();
    }

    /**
     * 試行結果を保存
     */
    function saveTrialResult($templateId, $aiTypeId, $prompt, $result, $userId = null) {
        global $conn;
        
        // 接続がない場合はfalseを返す
        if ($conn === null) {
            return false;
        }
        
        $sql = "INSERT INTO AITrialResults (template_id, ai_type_id, prompt, result, user_id, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissi", $templateId, $aiTypeId, $prompt, $result, $userId);
        
        return $stmt->execute();
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