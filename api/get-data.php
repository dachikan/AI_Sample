<?php
// 正しいパスでdb_connect.phpを読み込む
require_once '../config/db_connect.php';

header('Content-Type: application/json');

// CORSヘッダー設定
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONSリクエストの場合は、ここで終了
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// データを取得して返す
$response = [
    'aiTypes' => getAITypes(),
    'categories' => getCategories(),
    'promptCount' => getPromptCount(),
    'promptCountByCategory' => getPromptCountByCategory(),
    'promptCountByAIType' => getPromptCountByAIType()
];

echo json_encode($response);
?>
