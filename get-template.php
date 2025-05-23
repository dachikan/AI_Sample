<?php
// 正しいパスでdb_connect.phpを読み込む
require_once 'config/db_connect.php';

header('Content-Type: application/json');

// テンプレートIDの取得
$templateId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($templateId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid template ID']);
    exit;
}

// テンプレートの取得
$template = getTemplateById($templateId);

if ($template) {
    // テンプレートの説明を取得（もしあれば）
    $description = '';
    if (isset($template['description'])) {
        $description = $template['description'];
    } else {
        // 説明がない場合は、テンプレートの種類に基づいて説明を生成
        if (strpos(strtolower($template['name']), 'ビジネス') !== false) {
            $description = '変数の部分（{目的}、{相手}など）を具体的な内容に置き換えてください。';
        } elseif (strpos(strtolower($template['name']), 'レシピ') !== false) {
            $description = '変数の部分（{材料リスト}、{時間}など）を具体的な内容に置き換えてください。';
        } elseif (strpos(strtolower($template['name']), 'キャラクター') !== false) {
            $description = '変数の部分（{名前}、{性格}など）を具体的な内容に置き換えてください。画像生成AIに適したプロンプトです。';
        }
    }
    
    echo json_encode([
        'success' => true,
        'id' => $template['id'],
        'name' => $template['name'],
        'content' => $template['content'],
        'description' => $description,
        'category_id' => $template['category_id'],
        'ai_type_id' => $template['ai_type_id']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Template not found']);
}
?>
