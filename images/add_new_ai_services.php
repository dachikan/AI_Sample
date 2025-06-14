<?php
/**
 * 新しいAIサービスをデータベースに追加するスクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>新AIサービス追加</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }";
echo ".section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".error { color: #dc3545; font-weight: bold; }";
echo ".warning { color: #ffc107; font-weight: bold; }";
echo ".info { color: #17a2b8; font-weight: bold; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background-color: #f8f9fa; }";
echo ".btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; background-color: #007bff; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🚀 新しいAIサービスの追加</h1>";
echo "<p>実行時刻: " . date('Y-m-d H:i:s') . "</p>";

// データベース接続
include "db_connect_extended.php";

// 新しいAIサービスのデータ
$new_ai_services = [
    [
        'ai_type_id' => 2,
        'ai_service' => 'Leap Me',
        'company_name' => 'Leap AI',
        'ai_icon' => 'leap-me-icon.png',
        'brand_color' => '#FF6B6B',
        'description' => 'AIを使って自分の写真から様々なスタイルの画像を生成できるアプリ。セルフィーから芸術作品、アニメ風、プロフィール写真まで幅広く対応。',
        'strengths' => '高品質な画像生成、多様なスタイル、簡単操作、個人向け特化',
        'limitations' => '人物画像に特化、商用利用制限あり、クレジット制',
        'official_url' => 'https://www.tryleap.ai/',
        'pricing_model' => 'freemium',
        'popularity_score' => 78
    ],
    [
        'ai_type_id' => 1,
        'ai_service' => 'Goat Chat',
        'company_name' => 'Goat Technologies',
        'ai_icon' => 'goat-chat-icon.png',
        'brand_color' => '#8B5CF6',
        'description' => '高度な対話機能を持つAIチャットボット。自然な会話、質問応答、創作支援など多機能なAIアシスタント。',
        'strengths' => '自然な対話、多言語対応、創作支援、学習機能',
        'limitations' => '無料版は制限あり、最新情報の制限',
        'official_url' => 'https://goatchat.ai/',
        'pricing_model' => 'freemium',
        'popularity_score' => 72
    ],
    [
        'ai_type_id' => 2,
        'ai_service' => 'SELF',
        'company_name' => 'SELF Inc.',
        'ai_icon' => 'self-icon.png',
        'brand_color' => '#FF9500',
        'description' => 'AIを活用したセルフィー加工アプリ。自然な美肌効果、表情調整、背景変更など高度な画像編集機能を提供。',
        'strengths' => '自然な加工、リアルタイム処理、多様なフィルター、使いやすいUI',
        'limitations' => 'セルフィーに特化、プライバシー懸念、有料機能多数',
        'official_url' => 'https://self.app/',
        'pricing_model' => 'freemium',
        'popularity_score' => 85
    ],
    [
        'ai_type_id' => 2,
        'ai_service' => 'Meitu',
        'company_name' => 'Meitu Inc.',
        'ai_icon' => 'meitu-icon.png',
        'brand_color' => '#FF69B4',
        'description' => '中国発の人気画像加工アプリ。AI美顔機能、アニメ風変換、背景除去など豊富な画像編集機能を搭載。',
        'strengths' => '高度な美顔機能、アニメ風変換、豊富なフィルター、アジア系に最適化',
        'limitations' => '中国企業、プライバシー懸念、一部機能有料',
        'official_url' => 'https://www.meitu.com/',
        'pricing_model' => 'freemium',
        'popularity_score' => 88
    ],
    [
        'ai_type_id' => 4,
        'ai_service' => 'Runway ML',
        'company_name' => 'Runway AI Inc.',
        'ai_icon' => 'runway-ml-icon.png',
        'brand_color' => '#000000',
        'description' => 'AI動画生成・編集プラットフォーム。テキストから動画生成、動画編集、特殊効果、背景除去など包括的な動画制作ツール。',
        'strengths' => '動画生成、高度な編集機能、クリエイター向け、商用利用可能',
        'limitations' => '高価格、学習コスト、計算資源必要、処理時間長い',
        'official_url' => 'https://runwayml.com/',
        'pricing_model' => 'freemium',
        'popularity_score' => 89
    ],
    [
        'ai_type_id' => 4,
        'ai_service' => 'Synthesia',
        'company_name' => 'Synthesia Ltd.',
        'ai_icon' => 'synthesia-icon.png',
        'brand_color' => '#6366F1',
        'description' => 'AIアバターを使った動画生成プラットフォーム。テキストから自然な音声とリップシンクで動画を自動生成。',
        'strengths' => '多言語対応、リアルなアバター、企業向け、スケーラブル',
        'limitations' => '高価格、アバター制限、表情の制約、カスタマイズ限定',
        'official_url' => 'https://www.synthesia.io/',
        'pricing_model' => 'subscription',
        'popularity_score' => 86
    ],
    [
        'ai_type_id' => 2,
        'ai_service' => 'Luma AI',
        'company_name' => 'Luma AI Inc.',
        'ai_icon' => 'luma-ai-icon.png',
        'brand_color' => '#FF4081',
        'description' => 'スマートフォンで3Dオブジェクトをキャプチャし、高品質な3Dモデルを生成するAI。NeRF技術を活用。',
        'strengths' => '高品質3D生成、モバイル対応、リアルタイム処理、商用利用可能',
        'limitations' => '特殊技術必要、処理時間、ファイルサイズ大、専門知識必要',
        'official_url' => 'https://lumalabs.ai/',
        'pricing_model' => 'freemium',
        'popularity_score' => 75
    ],
    [
        'ai_type_id' => 4,
        'ai_service' => 'Pika Labs',
        'company_name' => 'Pika Labs Inc.',
        'ai_icon' => 'pika-labs-icon.png',
        'brand_color' => '#FFD700',
        'description' => 'テキストプロンプトから短い動画を生成するAI。Discord経由でアクセス可能な動画生成サービス。',
        'strengths' => '簡単操作、Discord統合、クリエイティブ、コミュニティ活発',
        'limitations' => '短時間動画のみ、Discord必要、品質制限、ベータ版',
        'official_url' => 'https://pika.art/',
        'pricing_model' => 'freemium',
        'popularity_score' => 73
    ]
];

echo "<div class='section'>";
echo "<h2>📊 追加予定のAIサービス</h2>";
echo "<table>";
echo "<tr><th>サービス名</th><th>会社</th><th>タイプ</th><th>説明</th><th>人気度</th></tr>";

foreach ($new_ai_services as $service) {
    $type_name = '';
    switch ($service['ai_type_id']) {
        case 1: $type_name = 'テキスト生成'; break;
        case 2: $type_name = '画像生成'; break;
        case 4: $type_name = '動画生成'; break;
        default: $type_name = 'その他'; break;
    }
    
    echo "<tr>";
    echo "<td><strong>" . $service['ai_service'] . "</strong></td>";
    echo "<td>" . $service['company_name'] . "</td>";
    echo "<td>" . $type_name . "</td>";
    echo "<td>" . substr($service['description'], 0, 80) . "...</td>";
    echo "<td>" . $service['popularity_score'] . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

// データベースに追加
echo "<div class='section'>";
echo "<h2>💾 データベースへの追加</h2>";

$success_count = 0;
$error_count = 0;

foreach ($new_ai_services as $service) {
    // 既存チェック
    $check_sql = "SELECT COUNT(*) as count FROM ai_tools WHERE ai_service = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $service['ai_service']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $exists = $check_result->fetch_assoc()['count'] > 0;
    $check_stmt->close();
    
    if ($exists) {
        echo "<p class='warning'>⚠ " . $service['ai_service'] . " は既に存在します</p>";
        continue;
    }
    
    // 挿入
    $insert_sql = "INSERT INTO ai_tools (ai_service, description, website_url, logo_url, rating, review_count, is_free, is_featured, created_at) 
                   VALUES (?, ?, ?, ?, 0.0, 0, 1, 0, NOW())";
    
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ssss", 
        $service['ai_service'],
        $service['description'],
        $service['official_url'],
        $service['ai_icon']
    );
    
    if ($stmt->execute()) {
        $success_count++;
        echo "<p class='success'>✓ " . $service['ai_service'] . " を追加しました</p>";
    } else {
        $error_count++;
        echo "<p class='error'>✗ " . $service['ai_service'] . " の追加に失敗: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

echo "<h3>📈 追加結果</h3>";
echo "<p><strong>成功:</strong> $success_count 件</p>";
echo "<p><strong>エラー:</strong> $error_count 件</p>";
echo "</div>";

// アイコン作成
echo "<div class='section'>";
echo "<h2>🎨 新しいアイコンの作成</h2>";

function createServiceIcon($filename, $text, $bg_color, $text_color, $size = 64) {
    if (!extension_loaded('gd')) {
        return false;
    }
    
    $img = imagecreatetruecolor($size, $size);
    
    // 背景色
    $bg = imagecolorallocate($img, $bg_color[0], $bg_color[1], $bg_color[2]);
    imagefill($img, 0, 0, $bg);
    
    // テキスト色
    $text_col = imagecolorallocate($img, $text_color[0], $text_color[1], $text_color[2]);
    
    // テキストを中央に配置
    $font_size = 4;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    
    $x = ($size - $text_width) / 2;
    $y = ($size - $text_height) / 2;
    
    imagestring($img, $font_size, $x, $y, $text, $text_col);
    
    // PNGとして保存
    $result = imagepng($img, 'images/' . $filename);
    imagedestroy($img);
    
    return $result;
}

$icon_configs = [
    'leap-me-icon.png' => ['text' => 'LEAP', 'bg' => [255, 107, 107], 'text' => [255, 255, 255]],
    'goat-chat-icon.png' => ['text' => 'GOAT', 'bg' => [139, 92, 246], 'text' => [255, 255, 255]],
    'self-icon.png' => ['text' => 'SELF', 'bg' => [255, 149, 0], 'text' => [255, 255, 255]],
    'meitu-icon.png' => ['text' => 'MEIT', 'bg' => [255, 105, 180], 'text' => [255, 255, 255]],
    'runway-ml-icon.png' => ['text' => 'RUN', 'bg' => [0, 0, 0], 'text' => [255, 255, 255]],
    'synthesia-icon.png' => ['text' => 'SYN', 'bg' => [99, 102, 241], 'text' => [255, 255, 255]],
    'luma-ai-icon.png' => ['text' => 'LUMA', 'bg' => [255, 64, 129], 'text' => [255, 255, 255]],
    'pika-labs-icon.png' => ['text' => 'PIKA', 'bg' => [255, 215, 0], 'text' => [0, 0, 0]]
];

$icon_created = 0;
foreach ($icon_configs as $filename => $config) {
    if (!file_exists('images/' . $filename)) {
        if (createServiceIcon($filename, $config['text'], $config['bg'], $config['text'], 64)) {
            $icon_created++;
            echo "<p class='success'>✓ $filename を作成しました</p>";
        } else {
            echo "<p class='error'>✗ $filename の作成に失敗しました</p>";
        }
    } else {
        echo "<p class='info'>ℹ $filename は既に存在します</p>";
    }
}

echo "<p><strong>作成されたアイコン:</strong> $icon_created 個</p>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🎯 次のステップ</h2>";
echo "<ol>";
echo "<li><a href='index.php' target='_blank'>トップページで新しいサービスを確認</a></li>";
echo "<li><a href='AI_list.php' target='_blank'>一覧ページで新しいサービスを確認</a></li>";
echo "<li><a href='AI_comparison.php' target='_blank'>比較ページで新しいサービスをテスト</a></li>";
echo "<li>必要に応じて高品質なアイコンに置き換え</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
