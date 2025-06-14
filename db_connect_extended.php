<?php
// 元のdb_connect.phpの内容をインクルード
require_once 'db_connect.php';

/**
 * AIInfo関連の汎用関数
 */

/**
 * 全てのAIサービス情報を取得
 */
function getAllAIServices($limit = null, $offset = 0) {
    global $conn;
    
    if ($conn === null) {
        return [];
    }
    
    $sql = "SELECT * FROM AIInfo WHERE is_active = 1 ORDER BY popularity_score DESC, sort_order ASC";
    
    if ($limit) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    
    $result = $conn->query($sql);
    $services = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
    }
    
    return $services;
}

/**
 * AIタイプ別のサービス一覧を取得
 */
function getAIServicesByType($aiTypeId) {
    global $conn;
    
    if ($conn === null) {
        return [];
    }
    
    $sql = "SELECT * FROM AIInfo WHERE ai_type_id = ? AND is_active = 1 ORDER BY popularity_score DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $aiTypeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    
    return $services;
}

/**
 * 特定のAIサービス詳細を取得
 */
function getAIServiceById($id) {
    global $conn;
    
    if ($conn === null) {
        return null;
    }
    
    $sql = "SELECT * FROM AIInfo WHERE id = ? AND is_active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * AIサービス数を取得
 */
function getAIServiceCount($aiTypeId = null) {
    global $conn;
    
    if ($conn === null) {
        return 0;
    }
    
    if ($aiTypeId) {
        $sql = "SELECT COUNT(*) as count FROM AIInfo WHERE ai_type_id = ? AND is_active = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $aiTypeId);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT COUNT(*) as count FROM AIInfo WHERE is_active = 1";
        $result = $conn->query($sql);
    }
    
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

/**
 * AIタイプ一覧を取得（AIInfoテーブルから）
 */
function getAITypesFromInfo() {
    global $conn;
    
    if ($conn === null) {
        return [];
    }
    
    $sql = "SELECT ai_type_id, 
                   CASE ai_type_id 
                       WHEN 1 THEN 'テキスト生成AI'
                       WHEN 2 THEN '画像生成AI'
                       WHEN 3 THEN '音声・音楽生成AI'
                       WHEN 4 THEN '動画生成AI'
                       WHEN 5 THEN '日本語特化AI'
                       WHEN 6 THEN 'コード生成AI'
                       WHEN 7 THEN '翻訳AI'
                       ELSE 'その他'
                   END as type_name,
                   COUNT(*) as service_count
            FROM AIInfo 
            WHERE is_active = 1 
            GROUP BY ai_type_id 
            ORDER BY ai_type_id";
    
    $result = $conn->query($sql);
    $types = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $types[] = $row;
        }
    }
    
    return $types;
}

/**
 * 人気度順でトップAIサービスを取得
 */
function getTopAIServices($limit = 10) {
    global $conn;
    
    if ($conn === null) {
        return [];
    }
    
    $sql = "SELECT * FROM AIInfo WHERE is_active = 1 ORDER BY popularity_score DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    
    return $services;
}

/**
 * 検索機能
 */
function searchAIServices($keyword) {
    global $conn;
    
    if ($conn === null) {
        return [];
    }
    
    $keyword = "%$keyword%";
    $sql = "SELECT * FROM AIInfo 
            WHERE is_active = 1 
            AND (ai_service LIKE ? OR company_name LIKE ? OR description LIKE ?)
            ORDER BY popularity_score DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    
    return $services;
}
?>
