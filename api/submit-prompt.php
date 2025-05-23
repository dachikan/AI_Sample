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

// POSTデータを取得
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

// 必須パラメータの確認
if (!isset($data['aiTypeId']) || !isset($data['categoryId']) || !isset($data['prompt'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$aiTypeId = (int)$data['aiTypeId'];
$categoryId = (int)$data['categoryId'];
$prompt = $data['prompt'];
$temperature = isset($data['temperature']) ? (float)$data['temperature'] : 0.7;

// 実際のAI APIを呼び出す処理をここに実装
// 今回はサンプルレスポンスを返す
$aiTypes = getAITypes();
$aiTypeName = '';

foreach ($aiTypes as $type) {
    if ($type['id'] == $aiTypeId) {
        $aiTypeName = $type['name'];
        break;
    }
}

if (strpos($aiTypeName, 'テキスト') !== false) {
    $output = "これはテキスト生成AIによる応答のサンプルです。実際のAPIと連携することで、ここに生成されたテキストが表示されます。プロンプトの内容や設定によって、出力結果は変わります。";
} elseif (strpos($aiTypeName, '画像') !== false) {
    $output = "画像生成AIの場合、ここに生成された画像が表示されます。実際のAPIと連携することで、プロンプトに基づいた画像が生成されます。";
} else {
    $output = "選択されたAIタイプ「{$aiTypeName}」に基づいた出力がここに表示されます。実際のAPIと連携することで、リアルタイムな応答が得られます。";
}

// 試行結果を保存
saveTrialResult(null, $aiTypeId, $prompt, $output);

// レスポンスの返却
echo json_encode([
    'success' => true,
    'aiType' => $aiTypeName,
    'output' => $output
]);
?>
