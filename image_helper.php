<?php
/**
 * 画像表示用のヘルパー関数
 */

/**
 * 安全な画像表示用のHTMLを生成
 */
function safeImageTag($imagePath, $altText, $className = 'ai-icon', $fallbackIcon = 'fas fa-robot') {
    $fullPath = 'images/' . $imagePath;
    
    // ファイルが存在しない場合はアイコンで代替
    if (!file_exists($fullPath)) {
        $placeholderClass = ($className === 'ai-icon-small') ? 'ai-icon-small ai-icon-small-placeholder' : 'ai-icon ai-icon-placeholder';
        return '<div class="' . $placeholderClass . '"><i class="' . $fallbackIcon . '"></i></div>';
    }
    
    return '<img src="' . htmlspecialchars($fullPath) . '" alt="' . htmlspecialchars($altText) . '" class="' . $className . '" onerror="handleImageError(this, \'' . ($className === 'ai-icon-small' ? 'small' : 'normal') . '\')">';
}

/**
 * AIサービスアイコンの表示
 */
function displayAIIcon($service, $size = 'normal') {
    $className = ($size === 'small') ? 'ai-icon-small' : 'ai-icon';
    return safeImageTag($service['ai_icon'], $service['ai_service'], $className);
}
?>
