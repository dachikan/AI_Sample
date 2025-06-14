<?php
// imagesフォルダを作成し、デフォルト画像を生成するスクリプト

// imagesフォルダを作成
if (!file_exists('images')) {
    if (mkdir('images', 0755, true)) {
        echo "✅ imagesフォルダを作成しました<br>";
    } else {
        echo "❌ imagesフォルダの作成に失敗しました<br>";
    }
} else {
    echo "✅ imagesフォルダは既に存在します<br>";
}

// デフォルトアイコンのSVGを作成
$defaultIconSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect width="60" height="60" rx="8" fill="#E5E7EB"/>
<path d="M30 15C22.268 15 16 21.268 16 29C16 36.732 22.268 43 30 43C37.732 43 44 36.732 44 29C44 21.268 37.732 15 30 15ZM30 18C36.065 18 41 22.935 41 29C41 35.065 36.065 40 30 40C23.935 40 19 35.065 19 29C19 22.935 23.935 18 30 18Z" fill="#9CA3AF"/>
<path d="M30 22C28.343 22 27 23.343 27 25C27 26.657 28.343 28 30 28C31.657 28 33 26.657 33 25C33 23.343 31.657 22 30 22Z" fill="#9CA3AF"/>
<path d="M25 32C25 30.343 26.343 29 28 29H32C33.657 29 35 30.343 35 32V35H25V32Z" fill="#9CA3AF"/>
</svg>';

// SVGファイルを保存
if (file_put_contents('images/default-ai-icon.svg', $defaultIconSvg)) {
    echo "✅ default-ai-icon.svgを作成しました<br>";
} else {
    echo "❌ default-ai-icon.svgの作成に失敗しました<br>";
}

// PNGバージョンも作成（簡単な1x1ピクセルの透明画像）
$transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
if (file_put_contents('images/default-ai-icon.png', $transparentPng)) {
    echo "✅ default-ai-icon.pngを作成しました<br>";
} else {
    echo "❌ default-ai-icon.pngの作成に失敗しました<br>";
}

// 各AIサービス用のアイコンファイルも作成
$aiServices = [
    'chatgpt-icon.png', 'claude-icon.png', 'gemini-icon.png', 'copilot-icon.png',
    'dalle-icon.png', 'midjourney-icon.png', 'stablediffusion-icon.png',
    'suno-icon.png', 'elevenlabs-icon.png', 'runway-icon.png', 'github-copilot-icon.png',
    'deepl-icon.png', 'claude-opus4-icon.png', 'llama2-icon.png', 'gpt4o-icon.png',
    'claude-sonnet-icon.png', 'gemini-2-5-icon.png', 'veo3-icon.png', 'v0-icon.png',
    'perplexity-icon.png', 'mistral-icon.png', 'cohere-icon.png', 'poe-icon.png',
    'huggingchat-icon.png', 'characterai-icon.png', 'grok-icon.png', 'meta-ai-icon.png',
    'claude-haiku-icon.png', 'llama3-icon.png', 'leonardo-icon.png', 'firefly-icon.png',
    'pika-icon.png', 'coefont-icon.png', 'rinna-icon.png'
];

$created = 0;
foreach ($aiServices as $iconFile) {
    if (!file_exists('images/' . $iconFile)) {
        if (file_put_contents('images/' . $iconFile, $transparentPng)) {
            $created++;
        }
    }
}

echo "✅ {$created}個のAIサービスアイコンを作成しました<br>";

echo "<br><h3>🎉 画像ファイルの準備が完了しました！</h3>";
echo "<p>これで画像の読み込みエラーが解消され、ページが安定するはずです。</p>";
?>
