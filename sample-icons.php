<?php
// japanese-community-icons.pngを活用するためのアイコン表示用ファイル
// このファイルは他のPHPファイルからインクルードして使用します

/**
 * コミュニティアイコンを取得する関数
 * japanese-community-icons.pngの画像を使用してアイコンを表示します
 * 
 * @param int $iconIndex アイコンのインデックス（0〜8）
 * @param string $size アイコンのサイズ（small, medium, large）
 * @return string アイコンを表示するHTMLコード
 */
function getCommunityIcon($iconIndex, $size = 'medium') {
    // サイズに応じたスタイルを設定
    $dimensions = [
        'small' => 'width: 24px; height: 24px;',
        'medium' => 'width: 32px; height: 32px;',
        'large' => 'width: 48px; height: 48px;'
    ];
    
    // アイコンの位置を計算（japanese-community-icons.pngの中の位置）
    // この値は実際の画像に合わせて調整する必要があります
    $positions = [
        0 => '0% 0%',    // 左上
        1 => '25% 0%',   // 上中央左
        2 => '50% 0%',   // 上中央右
        3 => '75% 0%',   // 右上
        4 => '0% 50%',   // 左中央
        5 => '25% 50%',  // 中央
        6 => '50% 50%',  // 右中央
        7 => '0% 100%',  // 左下
        8 => '25% 100%'  // 下中央
    ];
    
    // 有効なインデックスかチェック
    if (!isset($positions[$iconIndex])) {
        $iconIndex = 0; // デフォルトアイコン
    }
    
    // 有効なサイズかチェック
    if (!isset($dimensions[$size])) {
        $size = 'medium'; // デフォルトサイズ
    }
    
    // アイコンのHTMLを生成
    $html = '<div class="community-icon" style="' . $dimensions[$size] . ' background-image: url(\'images/japanese-community-icons.png\'); background-position: ' . $positions[$iconIndex] . '; background-size: 400%; background-repeat: no-repeat;"></div>';
    
    return $html;
}

/**
 * AIサービスに応じたアイコンを取得する関数
 * 
 * @param string $aiName AIサービス名
 * @return array アイコン情報（icon, color）
 */
function getAiServiceIcon($aiName) {
    // AIサービス情報
    $aiServices = [
        'GPT-4' => ['icon' => 'gpt4-logo.png', 'color' => '#10a37f'],
        'GPT-3.5' => ['icon' => 'gpt-logo.jpg', 'color' => '#10a37f'],
        'Gemini' => ['icon' => 'gemini.png', 'color' => '#4285f4'],
        'Claude' => ['icon' => 'claude-logo.jpg', 'color' => '#8e44ad'],
        'DALL-E' => ['icon' => 'dalle-logo.jpg', 'color' => '#ff6b6b'],
        'Midjourney' => ['icon' => 'midjourney-logo.png', 'color' => '#6b47ff'],
        'Stable Diffusion' => ['icon' => 'stable-diffusion-logo.png', 'color' => '#ff9f43'],
        'Leonardo AI' => ['icon' => 'leonardo-logo.jpg', 'color' => '#2980b9'],
        'Firefly' => ['icon' => 'firefly-logo.jpg', 'color' => '#e74c3c']
    ];
    
    // デフォルト値
    $result = ['icon' => 'ai-icon.png', 'color' => '#6c757d'];
    
    // AIサービス名に基づいてアイコンと色を設定
    foreach ($aiServices as $service => $info) {
        if (stripos($aiName, $service) !== false) {
            $result = $info;
            break;
        }
    }
    
    return $result;
}

/**
 * アイコンバッジを表示する関数
 * 
 * @param string $aiName AIサービス名
 * @return string アイコンバッジのHTML
 */
function renderAiIconBadge($aiName) {
    $iconInfo = getAiServiceIcon($aiName);
    
    $html = '<div class="ai-badge" style="display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 20px; color: white; font-weight: 500; background-color: ' . $iconInfo['color'] . ';">';
    
    // 画像ファイルが存在するか確認
    if (file_exists('images/' . $iconInfo['icon'])) {
        $html .= '<img src="images/' . $iconInfo['icon'] . '" alt="' . htmlspecialchars($aiName) . '" style="width: 20px; height: 20px; margin-right: 5px; border-radius: 50%; background-color: white; padding: 2px;" onerror="this.src=\'images/ai-icon.png\'">';
    } else {
        // 画像がない場合はBootstrap Iconsを使用
        $html .= '<i class="bi bi-robot me-1"></i>';
    }
    
    $html .= htmlspecialchars($aiName) . '</div>';
    
    return $html;
}
?>
