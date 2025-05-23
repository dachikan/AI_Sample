<?php
// データベース接続設定
require_once 'config/db_connect.php';
// アイコン表示用の関数をインクルード
require_once 'sample-icons.php';

// セッション開始
session_start();

// サンプル一覧を取得
try {
    $stmt = $conn->prepare("
        SELECT id, Title, AiName, category_id, Prompt 
        FROM AISampleInfo 
        ORDER BY Title ASC
    ");
    $stmt->execute();
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "データベースエラー: " . $e->getMessage();
}

// カテゴリ情報を取得
try {
    $stmt = $conn->prepare("SELECT CategoryID, CategoryName FROM Categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "データベースエラー: " . $e->getMessage();
}

// 選択されたサンプルの情報を取得
$selectedSample = null;
$selectedId = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['sample_id']) ? intval($_POST['sample_id']) : 0);

if ($selectedId > 0) {
    try {
        $stmt = $conn->prepare("
            SELECT s.*, c.CategoryName 
            FROM AISampleInfo s
            LEFT JOIN Categories c ON s.category_id = c.CategoryID
            WHERE s.id = :id
        ");
        $stmt->bindParam(':id', $selectedId, PDO::PARAM_INT);
        $stmt->execute();
        $selectedSample = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "データベースエラー: " . $e->getMessage();
    }
}

// フォーム送信時の処理
$formSubmitted = false;
$trialResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'try_prompt') {
    $formSubmitted = true;
    
    // フォームデータを取得
    $trialData = [
        'sample_id' => isset($_POST['sample_id']) ? intval($_POST['sample_id']) : 0,
        'ai_service' => isset($_POST['ai_service']) ? trim($_POST['ai_service']) : '',
        'custom_prompt' => isset($_POST['custom_prompt']) ? trim($_POST['custom_prompt']) : '',
        'improvements' => isset($_POST['improvements']) ? trim($_POST['improvements']) : '',
        'result_description' => isset($_POST['result_description']) ? trim($_POST['result_description']) : '',
        'user_name' => isset($_POST['user_name']) ? trim($_POST['user_name']) : '匿名ユーザー'
    ];
    
    // 画像アップロード処理
    $inputImagePath = '';
    $outputImagePath = '';
    
    if (isset($_FILES['input_image']) && $_FILES['input_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $inputImageTmp = $_FILES['input_image']['tmp_name'];
        $inputImageName = basename($_FILES['input_image']['name']);
        $inputImageExt = pathinfo($inputImageName, PATHINFO_EXTENSION);
        $inputImageNewName = 'freetry_input_' . time() . '_' . uniqid() . '.' . $inputImageExt;
        $inputImagePath = $uploadDir . $inputImageNewName;
        
        if (!move_uploaded_file($inputImageTmp, $inputImagePath)) {
            $error = "入力画像のアップロードに失敗しました。";
            $inputImagePath = '';
        }
    }
    
    if (isset($_FILES['output_image']) && $_FILES['output_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $outputImageTmp = $_FILES['output_image']['tmp_name'];
        $outputImageName = basename($_FILES['output_image']['name']);
        $outputImageExt = pathinfo($outputImageName, PATHINFO_EXTENSION);
        $outputImageNewName = 'freetry_output_' . time() . '_' . uniqid() . '.' . $outputImageExt;
        $outputImagePath = $uploadDir . $outputImageNewName;
        
        if (!move_uploaded_file($outputImageTmp, $outputImagePath)) {
            $error = "出力画像のアップロードに失敗しました。";
            $outputImagePath = '';
        }
    }
    
    // 試行結果をデータベースに保存（オプション）
    try {
        $stmt = $conn->prepare("
            INSERT INTO AITrialResults 
            (sample_id, ai_service, custom_prompt, improvements, result_description, input_image, output_image, user_name, created_at) 
            VALUES 
            (:sample_id, :ai_service, :custom_prompt, :improvements, :result_description, :input_image, :output_image, :user_name, NOW())
        ");
        
        $stmt->bindParam(':sample_id', $trialData['sample_id'], PDO::PARAM_INT);
        $stmt->bindParam(':ai_service', $trialData['ai_service'], PDO::PARAM_STR);
        $stmt->bindParam(':custom_prompt', $trialData['custom_prompt'], PDO::PARAM_STR);
        $stmt->bindParam(':improvements', $trialData['improvements'], PDO::PARAM_STR);
        $stmt->bindParam(':result_description', $trialData['result_description'], PDO::PARAM_STR);
        $stmt->bindParam(':input_image', $inputImagePath, PDO::PARAM_STR);
        $stmt->bindParam(':output_image', $outputImagePath, PDO::PARAM_STR);
        $stmt->bindParam(':user_name', $trialData['user_name'], PDO::PARAM_STR);
        
        $stmt->execute();
        $trialId = $conn->lastInsertId();
        
        // 保存した試行結果を取得
        $stmt = $conn->prepare("
            SELECT * FROM AITrialResults WHERE id = :id
        ");
        $stmt->bindParam(':id', $trialId, PDO::PARAM_INT);
        $stmt->execute();
        $trialResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // セッションに成功メッセージを保存
        $_SESSION['success_message'] = "お試し結果を保存しました。";
        
    } catch (PDOException $e) {
        $error = "データベースエラー: " . $e->getMessage();
    }
}

// 成功メッセージの確認
$successMessage = null;
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// AIサービスの定義
$aiServices = [
    'text' => [
        'title' => 'テキスト生成AI',
        'services' => [
            ['id' => 'chatgpt', 'name' => 'ChatGPTで使用', 'icon' => 'bi-robot', 'color' => '#10a37f'],
            ['id' => 'claude', 'name' => 'Claudeで使用', 'icon' => 'bi-c-circle', 'color' => '#8e44ad'],
            ['id' => 'gemini', 'name' => 'Google Geminiで使用', 'icon' => 'bi-google', 'color' => '#4285f4'],
            ['id' => 'copilot', 'name' => 'Microsoft Copilotで使用', 'icon' => 'bi-microsoft', 'color' => '#00a4ef'],
            ['id' => 'meta', 'name' => 'Meta AIで使用', 'icon' => 'bi-meta', 'color' => '#0080fb'],
            ['id' => 'grok', 'name' => 'Grok (X AI)で使用', 'icon' => 'bi-twitter-x', 'color' => '#1DA1F2'],
            ['id' => 'perplexity', 'name' => 'Perplexity AIで使用', 'icon' => 'bi-p-circle', 'color' => '#5436DA'],
            ['id' => 'mistral', 'name' => 'Mistral AIで使用', 'icon' => 'bi-wind', 'color' => '#5E17EB'],
            ['id' => 'cohere', 'name' => 'Cohereで使用', 'icon' => 'bi-c-circle', 'color' => '#2D4EC0'],
            ['id' => 'poe', 'name' => 'Poeで使用', 'icon' => 'bi-p-circle', 'color' => '#6B46C1'],
            ['id' => 'huggingchat', 'name' => 'HuggingChatで使用', 'icon' => 'bi-chat-dots', 'color' => '#FFD21E'],
            ['id' => 'character', 'name' => 'Character AIで使用', 'icon' => 'bi-person', 'color' => '#FF5F1F'],
            ['id' => 'claude3opus', 'name' => 'Claude 3 Opusで使用', 'icon' => 'bi-c-circle', 'color' => '#8e44ad'],
            ['id' => 'claude3sonnet', 'name' => 'Claude 3 Sonnetで使用', 'icon' => 'bi-c-circle', 'color' => '#8e44ad'],
            ['id' => 'claude3haiku', 'name' => 'Claude 3 Haikuで使用', 'icon' => 'bi-c-circle', 'color' => '#8e44ad'],
            ['id' => 'llama3', 'name' => 'Llama 3で使用', 'icon' => 'bi-meta', 'color' => '#0080fb'],
            ['id' => 'v0', 'name' => 'v0 (Vercel AI)で使用', 'icon' => 'bi-lightning', 'color' => '#000000']
        ]
    ],
    'image' => [
        'title' => '画像生成AI',
        'services' => [
            ['id' => 'midjourney', 'name' => 'Midjourneyで使用', 'icon' => 'bi-image', 'color' => '#6b47ff'],
            ['id' => 'dalle', 'name' => 'DALL-E (OpenAI)で使用', 'icon' => 'bi-palette', 'color' => '#ff6b6b'],
            ['id' => 'stablediffusion', 'name' => 'Stable Diffusionで使用', 'icon' => 'bi-brush', 'color' => '#ff9f43'],
            ['id' => 'leonardo', 'name' => 'Leonardo AIで使用', 'icon' => 'bi-palette2', 'color' => '#2980b9'],
            ['id' => 'firefly', 'name' => 'Adobe Fireflyで使用', 'icon' => 'bi-fire', 'color' => '#e74c3c']
        ]
    ],
    'audio' => [
        'title' => '音声・音楽生成AI',
        'services' => [
            ['id' => 'suno', 'name' => 'Sunoで使用', 'icon' => 'bi-music-note', 'color' => '#6C5CE7'],
            ['id' => 'elevenlabs', 'name' => 'ElevenLabsで使用', 'icon' => 'bi-soundwave', 'color' => '#3498DB']
        ]
    ],
    'video' => [
        'title' => '動画生成AI',
        'services' => [
            ['id' => 'runway', 'name' => 'Runway Gen-2で使用', 'icon' => 'bi-film', 'color' => '#2C3E50'],
            ['id' => 'pika', 'name' => 'Pika Labsで使用', 'icon' => 'bi-camera-reels', 'color' => '#E74C3C']
        ]
    ],
    'japanese' => [
        'title' => '日本語特化AI',
        'services' => [
            ['id' => 'coefont', 'name' => 'CoeFontで使用', 'icon' => 'bi-translate', 'color' => '#3498DB'],
            ['id' => 'rinna', 'name' => 'rinnaで使用', 'icon' => 'bi-translate', 'color' => '#9B59B6']
        ]
    ]
];

// カテゴリ名を取得する関数
function getCategoryName($categoryId, $categories) {
    foreach ($categories as $category) {
        if ($category['CategoryID'] == $categoryId) {
            return $category['CategoryName'];
        }
    }
    return "未分類";
}

// カテゴリアイコンを取得する関数
function getCategoryIcon($categoryName) {
    $icons = [
        '文書作成' => '📝',
        '画像生成' => '🖼️',
        '会計管理' => '📊',
        'イベント企画' => '🎪',
        '情報発信' => '📢',
        '防災対策' => '🚨',
        'コミュニティ活性化' => '👥',
        '家庭内' => '🏠',
        'その他' => '🔍'
    ];
    return isset($icons[$categoryName]) ? $icons[$categoryName] : '📋';
}

// AIサービスのURLを取得する関数
function getAiServiceUrl($serviceId) {
    $urls = [
        'chatgpt' => 'https://chat.openai.com/',
        'claude' => 'https://claude.ai/',
        'gemini' => 'https://gemini.google.com/',
        'copilot' => 'https://copilot.microsoft.com/',
        'meta' => 'https://meta.ai/',
        'grok' => 'https://grok.x.ai/',
        'perplexity' => 'https://www.perplexity.ai/',
        'mistral' => 'https://mistral.ai/',
        'cohere' => 'https://cohere.com/',
        'poe' => 'https://poe.com/',
        'huggingchat' => 'https://huggingface.co/chat/',
        'character' => 'https://character.ai/',
        'claude3opus' => 'https://claude.ai/',
        'claude3sonnet' => 'https://claude.ai/',
        'claude3haiku' => 'https://claude.ai/',
        'llama3' => 'https://www.meta.ai/llama/',
        'v0' => 'https://v0.dev/',
        'midjourney' => 'https://www.midjourney.com/',
        'dalle' => 'https://openai.com/dall-e-3',
        'stablediffusion' => 'https://stability.ai/',
        'leonardo' => 'https://leonardo.ai/',
        'firefly' => 'https://www.adobe.com/products/firefly.html',
        'suno' => 'https://suno.ai/',
        'elevenlabs' => 'https://elevenlabs.io/',
        'runway' => 'https://runwayml.com/',
        'pika' => 'https://pika.art/',
        'coefont' => 'https://coefont.cloud/',
        'rinna' => 'https://rinna.co.jp/'
    ];
    
    return isset($urls[$serviceId]) ? $urls[$serviceId] : '#';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIプロンプト自由お試しページ | AI活用サンプル集</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/prompt-fix.css">
    <style>
        .sample-header {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .prompt-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            position: relative;
            margin-bottom: 30px;
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-word;
            border: 1px solid #dee2e6;
        }
        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            font-size: 0.75rem;
            padding: 0.2rem 0.4rem;
        }
        .ai-service-btn {
            margin-bottom: 10px;
            margin-right: 5px;
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 20px;
            color: white;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        .ai-service-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            color: white;
        }
        .ai-service-btn i {
            margin-right: 5px;
        }
        .ai-service-section {
            margin-bottom: 30px;
            padding: 15px;
            border-radius: 8px;
            background-color: #f0f7ff;
        }
        .ai-service-title {
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
        }
        .image-container {
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .image-container img {
            width: 100%;
            height: auto;
            display: block;
        }
        .image-title {
            background-color: rgba(0,0,0,0.7);
            color: white;
            padding: 10px;
            margin: 0;
        }
        .zoom-image {
            cursor: zoom-in;
            transition: transform 0.3s;
        }
        .zoom-image:hover {
            transform: scale(1.02);
        }
        .trial-result {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }
        .trial-result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .trial-result-content {
            margin-bottom: 20px;
        }
        .trial-result-meta {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .success-alert {
            animation: fadeOut 5s forwards;
            animation-delay: 3s;
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; visibility: hidden; }
        }
        .category-badge {
            background-color: #6c757d;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            margin-right: 10px;
        }
        .section-title {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        #customPromptContainer {
            display: none;
        }
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .form-section-title {
            margin-bottom: 15px;
            font-weight: 600;
        }
        /* 既存のスタイルはそのままで、以下を追加 */
        .scrolling-wrapper {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .scrolling-wrapper .card {
            flex: 0 0 auto;
            width: 300px;
            margin-right: 15px;
        }
        
        .scrolling-wrapper::-webkit-scrollbar {
            height: 8px;
        }
        
        .scrolling-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .scrolling-wrapper::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .scrolling-wrapper::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <header class="bg-light py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="AISampleList-unified.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> サンプル一覧に戻る
                </a>
                <h1 class="h4 mb-0">AIプロンプト自由お試し</h1>
                <div>
                    <a href="AISample-form.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 新規サンプル登録
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success success-alert">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-3">
                <!-- サイドバー -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>プロンプトカテゴリ</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($categories as $category): ?>
                                <li class="list-group-item d-flex align-items-center">
                                    <?php 
                                    $icon = getCategoryIcon($category['CategoryName']);
                                    ?>
                                    <span class="me-2"><?php echo $icon; ?></span>
                                    <a href="AISampleList-unified.php?category=<?php echo $category['CategoryID']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($category['CategoryName']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>AIサービスタイプ</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($aiServices as $type => $serviceGroup): ?>
                                <li class="list-group-item d-flex align-items-center">
                                    <?php 
                                    $icon = '';
                                    switch ($type) {
                                        case 'text': $icon = '<i class="bi bi-chat-text me-2"></i>'; break;
                                        case 'image': $icon = '<i class="bi bi-image me-2"></i>'; break;
                                        case 'audio': $icon = '<i class="bi bi-music-note-beamed me-2"></i>'; break;
                                        case 'video': $icon = '<i class="bi bi-film me-2"></i>'; break;
                                        case 'japanese': $icon = '<i class="bi bi-translate me-2"></i>'; break;
                                    }
                                    echo $icon;
                                    ?>
                                    <span><?php echo $serviceGroup['title']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9">
                <!-- メインコンテンツ -->
                <div class="sample-header">
                    <h2>AIプロンプト自由お試し</h2>
                    <p class="text-muted">既存のプロンプトを選択するか、オリジナルのプロンプトを入力して、様々なAIサービスで試してみましょう。</p>
                </div>
                
                <!-- プロンプト選択フォーム -->
                <form action="AISample-FreeTry.php" method="GET" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <label for="sampleSelect" class="form-label">既存のプロンプトを選択</label>
                            <select class="form-select" id="sampleSelect" name="id">
                                <option value="0">-- プロンプトを選択してください --</option>
                                <?php foreach ($samples as $sample): ?>
                                    <option value="<?php echo $sample['id']; ?>" <?php echo ($selectedId == $sample['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sample['Title']); ?> (<?php echo htmlspecialchars($sample['AiName']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> プロンプトを表示
                            </button>
                        </div>
                    </div>
                </form>
                
                <?php if ($selectedSample): ?>
                    <!-- 選択されたサンプルの表示 -->
                    <div class="mb-4">
                        <h3><?php echo htmlspecialchars($selectedSample['Title']); ?></h3>
                        <div class="d-flex flex-wrap align-items-center mt-2 mb-3">
                            <?php echo renderAiIconBadge($selectedSample['AiName']); ?>
                            <div class="category-badge">
                                <span class="me-1"><?php echo getCategoryIcon($selectedSample['CategoryName']); ?></span>
                                <?php echo htmlspecialchars($selectedSample['CategoryName'] ?? '未分類'); ?>
                            </div>
                        </div>
                        
                        <h4 class="section-title">プロンプト</h4>
                        <div class="prompt-container position-relative">
                            <button class="btn btn-sm btn-outline-primary copy-btn" onclick="copyPrompt()" title="このプロンプトをクリップボードにコピーしてAIツールで使用できます">
                                <i class="bi bi-clipboard"></i> コピー
                            </button>
                            <textarea id="promptText" class="form-control border-0 bg-transparent" style="min-height: 200px; font-family: monospace;"><?php echo htmlspecialchars($selectedSample['Prompt']); ?></textarea>
                        </div>
                        
                        <?php if (!empty($selectedSample['Description'])): ?>
                            <h4 class="section-title">説明</h4>
                            <div class="mb-4">
                                <?php echo nl2br(htmlspecialchars($selectedSample['Description'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($selectedSample['Tips'])): ?>
                            <h4 class="section-title">活用のコツ</h4>
                            <div class="tips-container mb-4">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                <?php echo nl2br(htmlspecialchars($selectedSample['Tips'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- AIサービス選択セクション -->
                    <h4 class="section-title">AIサービスで使用する</h4>
                    <p>以下のAIサービスでこのプロンプトを使用できます：</p>
                    
                    <?php foreach ($aiServices as $type => $serviceGroup): ?>
                        <div class="ai-service-section">
                            <h5 class="ai-service-title"><?php echo $serviceGroup['title']; ?></h5>
                            <div class="d-flex flex-wrap">
                                <?php foreach ($serviceGroup['services'] as $service): ?>
                                    <a href="<?php echo getAiServiceUrl($service['id']); ?>" target="_blank" class="ai-service-btn" style="background-color: <?php echo $service['color']; ?>;" onclick="updateAiName('<?php echo str_replace('で使用', '', $service['name']); ?>')">
                                        <i class="bi <?php echo $service['icon']; ?>"></i> <?php echo $service['name']; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- お試し結果入力フォーム -->
                    <h4 class="section-title mt-5">お試し結果を共有</h4>
                    <p>このプロンプトを使ってみた結果を共有しましょう。他のユーザーの参考になります。</p>

                    <form action="AISample-FreeTry.php" method="POST" enctype="multipart/form-data" class="mb-5">
                        <input type="hidden" name="action" value="try_prompt">
                        <input type="hidden" name="sample_id" value="<?php echo $selectedSample['id']; ?>">
                        
                        <div class="form-section">
                            <h5 class="form-section-title">実行前準備</h5>
                            
                            <div class="mb-3">
                                <label for="ai_service" class="form-label">1. 使用したAIサービス</label>
                                <select class="form-select" id="ai_service" name="ai_service" required>
                                    <option value="">-- AIサービスを選択してください --</option>
                                    <?php foreach ($aiServices as $type => $serviceGroup): ?>
                                        <optgroup label="<?php echo $serviceGroup['title']; ?>">
                                            <?php foreach ($serviceGroup['services'] as $service): ?>
                                                <option value="<?php echo $service['id']; ?>"><?php echo $service['name']; ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">2. プロンプトのカスタマイズ</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="customPromptCheck" onchange="toggleCustomPrompt()">
                                    <label class="form-check-label" for="customPromptCheck">
                                        プロンプトをカスタマイズして使用した
                                    </label>
                                </div>
                                <div id="customPromptContainer" class="mb-3">
                                    <textarea class="form-control" id="custom_prompt" name="custom_prompt" rows="5"><?php echo htmlspecialchars($selectedSample['Prompt']); ?></textarea>
                                    <div class="form-text">元のプロンプトを編集して、実際に使用したプロンプトを入力してください。</div>
                                </div>
                                <div class="mb-3">
                                    <label for="improvements" class="form-label">工夫した点・改善点</label>
                                    <textarea class="form-control" id="improvements" name="improvements" rows="3" placeholder="例：プロンプトに具体的な例を追加した、指示をより明確にした、など"></textarea>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="input_image" class="form-label">3. 入力画像（任意）</label>
                                <input type="file" class="form-control" id="input_image" name="input_image" accept="image/*">
                                <div class="form-text">画像生成AIに入力した画像がある場合はアップロードしてください。</div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h5 class="form-section-title">実行結果</h5>
                            <div class="mb-3">
                                <label for="result_description" class="form-label">実行結果の説明</label>
                                <textarea class="form-control" id="result_description" name="result_description" rows="4" placeholder="AIからの回答内容や、生成された結果について説明してください" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="output_image" class="form-label">出力画像（任意）</label>
                                <input type="file" class="form-control" id="output_image" name="output_image" accept="image/*">
                                <div class="form-text">AIが生成した画像や結果のスクリーンショットをアップロードしてください。</div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h5 class="form-section-title">ユーザー情報</h5>
                            <div class="mb-3">
                                <label for="user_name" class="form-label">ニックネーム（任意）</label>
                                <input type="text" class="form-control" id="user_name" name="user_name" placeholder="匿名で投稿する場合は空欄にしてください">
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-share"></i> お試し結果を共有する
                            </button>
                        </div>
                    </form>
                    
                <?php elseif ($formSubmitted && $trialResult): ?>
                    <!-- お試し結果の表示 -->
                    <div class="alert alert-success mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i> お試し結果を保存しました。ありがとうございます！
                    </div>
                    
                    <div class="trial-result">
                        <div class="trial-result-header">
                            <h3 class="mb-0">お試し結果</h3>
                            <span class="badge bg-primary"><?php 
                                foreach ($aiServices as $type => $serviceGroup) {
                                    foreach ($serviceGroup['services'] as $service) {
                                        if ($service['id'] === $trialResult['ai_service']) {
                                            echo $service['name'];
                                            break 2;
                                        }
                                    }
                                }
                            ?></span>
                        </div>
                        
                        <div class="trial-result-content">
                            <?php if (!empty($trialResult['improvements'])): ?>
                                <h5>工夫した点・改善点</h5>
                                <div class="mb-3">
                                    <?php echo nl2br(htmlspecialchars($trialResult['improvements'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($trialResult['custom_prompt'])): ?>
                                <h5>カスタマイズしたプロンプト</h5>
                                <div class="prompt-container mb-3">
                                    <div><?php echo nl2br(htmlspecialchars($trialResult['custom_prompt'])); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <h5>実行結果</h5>
                            <div class="mb-3">
                                <?php echo nl2br(htmlspecialchars($trialResult['result_description'])); ?>
                            </div>
                            
                            <?php if (!empty($trialResult['input_image']) && file_exists($trialResult['input_image'])): ?>
                                <div class="image-container mb-4">
                                    <h5 class="image-title">入力画像</h5>
                                    <img src="<?php echo htmlspecialchars($trialResult['input_image']); ?>" alt="入力画像" class="zoom-image" onclick="showImageModal('<?php echo htmlspecialchars($trialResult['input_image']); ?>', '入力画像')">
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($trialResult['output_image']) && file_exists($trialResult['output_image'])): ?>
                                <div class="image-container mb-4">
                                    <h5 class="image-title">出力画像</h5>
                                    <img src="<?php echo htmlspecialchars($trialResult['output_image']); ?>" alt="出力画像" class="zoom-image" onclick="showImageModal('<?php echo htmlspecialchars($trialResult['output_image']); ?>', '出力画像')">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="trial-result-meta">
                            <div>投稿者: <?php echo htmlspecialchars($trialResult['user_name'] ?: '匿名ユーザー'); ?></div>
                            <div>投稿日時: <?php echo date('Y年m月d日 H:i', strtotime($trialResult['created_at'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="AISample-FreeTry.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> 別のプロンプトを試す
                        </a>
                        <?php if ($trialResult['sample_id'] > 0): ?>
                            <a href="AISample-FreeTry.php?id=<?php echo $trialResult['sample_id']; ?>" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat"></i> 同じプロンプトでもう一度試す
                            </a>
                        <?php endif; ?>
                    </div>
                    
                <?php else: ?>
                    <!-- プロンプト選択前の初期表示 -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i> 上のドロップダウンからプロンプトを選択してください。
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title">AIプロンプト自由お試しページの使い方</h4>
                            
                            <h5 class="mt-3">【準備編】</h5>
                            <ol>
                                <li>上部のドロップダウンから試したいプロンプトを選択します。</li>
                                <li>選択したプロンプトの詳細が表示されます。</li>
                                <li>必要に応じてプロンプト内容を編集します。</li>
                                <li>「コピー」ボタンでプロンプトをクリップボードにコピーします。</li>
                                <li>お好みのAIサービスのボタンをクリックして、そのサービスを開きます。</li>
                            </ol>
                            
                            <h5 class="mt-3">【結果編】</h5>
                            <ul>
                                <li>AIサービスでプロンプトを実行し、結果を確認します。</li>
                                <li>このページに戻って「お試し結果を共有」フォームに入力します。</li>
                                <li>他のユーザーの参考になるよう、工夫した点や結果を共有しましょう。</li>
                            </ul>
                            
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-lightbulb"></i> <strong>ヒント:</strong> 特定のレシピプロンプト（例：「画像からレシピを提案！」）を選ぶと、専用のプロンプトテンプレートが自動的に入力されます。
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="section-title">おすすめのプロンプト</h4>
                    <div class="scrolling-wrapper">
                        <?php 
                        // すべてのサンプルを表示
                        foreach ($samples as $sample): 
                        ?>
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title"><?php echo htmlspecialchars($sample['Title']); ?></h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        <?php 
                                        $prompt = htmlspecialchars($sample['Prompt']);
                                        echo strlen($prompt) > 100 ? substr($prompt, 0, 100) . '...' : $prompt;
                                        ?>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <a href="AISample-FreeTry.php?id=<?php echo $sample['id']; ?>" class="btn btn-primary">
                                        このプロンプトを試す
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- 画像拡大表示モーダル -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">画像</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="modalImage" src="images/placeholder.png" alt="拡大画像" style="max-width: 100%;">
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>AI活用サンプル集</h5>
                    <p>初心者に役立つAIプロンプトのサンプル集</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2023-<?php echo date('Y'); ?> AI活用サンプル集. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // プロンプトをコピーする関数
        function copyPrompt() {
            const promptText = document.getElementById('promptText').value;
            navigator.clipboard.writeText(promptText).then(() => {
                const copyBtn = document.querySelector('.copy-btn');
                copyBtn.innerHTML = '<i class="bi bi-check"></i> コピー完了';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="bi bi-clipboard"></i> コピー';
                }, 2000);
            });
        }
        
        // 画像を拡大表示する関数
        function showImageModal(src, title) {
            const modalImage = document.getElementById('modalImage');
            const modalTitle = document.getElementById('imageModalLabel');
            
            modalImage.src = src;
            modalTitle.textContent = title;
            
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            imageModal.show();
        }
        
        // カスタムプロンプト入力欄の表示/非表示を切り替える関数
        function toggleCustomPrompt() {
    const customPromptContainer = document.getElementById('customPromptContainer');
    const customPromptCheck = document.getElementById('customPromptCheck');
    const promptTextarea = document.getElementById('promptText');
    const customPromptTextarea = document.getElementById('custom_prompt');
    
    if (customPromptCheck.checked) {
        customPromptContainer.style.display = 'block';
        // 現在のプロンプト欄の内容をカスタムプロンプト欄にコピー
        customPromptTextarea.value = promptTextarea.value;
    } else {
        customPromptContainer.style.display = 'none';
    }
}
        
        // 画像の存在確認
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.onerror = function() {
                    console.log('画像読み込みエラー:', this.src);
                    this.src = 'images/placeholder.png';
                    this.alt = '画像が見つかりません';
                };
            });
        });

// AIサービス名を更新する関数
function updateAiName(aiName) {
    const promptText = document.getElementById('promptText');
    // プロンプト内のAI名を検出して置換
    const aiPattern = /(ChatGPT|Claude|Google Gemini|Microsoft Copilot|Meta AI|Grok|Perplexity AI|Mistral AI|Cohere|Poe|HuggingChat|Character AI|Claude 3 Opus|Claude 3 Sonnet|Claude 3 Haiku|Llama 3|v0|Midjourney|DALL-E|Stable Diffusion|Leonardo AI|Adobe Firefly|Suno|ElevenLabs|Runway Gen-2|Pika Labs|CoeFont|rinna)/g;
    
    if (promptText.value.match(aiPattern)) {
        promptText.value = promptText.value.replace(aiPattern, aiName);
    }
    
    // イベントのデフォルト動作（リンクのクリック）を続行
    return true;
}

// サンプル選択時の特別なプロンプト設定
document.addEventListener('DOMContentLoaded', function() {
    const sampleSelect = document.getElementById('sampleSelect');
    if (sampleSelect) {
        sampleSelect.addEventListener('change', function() {
            // レシピ提案プロンプトの自動入力
            if (this.options[this.selectedIndex].text.includes('画像からレシピを提案')) {
                const recipePrompt = `この画像に写っている食材を使って作れる簡単なレシピを3つ提案してください。
各レシピについて以下の情報を含めてください：

1. レシピ名（日本語と英語）
2. 調理時間
3. 難易度（初心者/中級者/上級者）
4. 材料リスト（分量も含む）
5. 調理手順（ステップバイステップで）
6. 栄養情報（カロリー、タンパク質、炭水化物、脂質）
7. このレシピに合うワインや飲み物の提案

また、これらの食材を使って買い物リストも作成してください。足りない食材があれば、それも含めてください。

最後に、これらのレシピの中から、時間がない平日の夜に最適なレシピを1つ選んで、その理由を説明してください。`;
                
                // フォーム送信前にプロンプト欄を更新
                setTimeout(() => {
                    const promptText = document.getElementById('promptText');
                    if (promptText) {
                        promptText.value = recipePrompt;
                    }
                }, 100);
            }
            
            // 自治会だより用プロンプトの自動入力（英語部分を削除）
            if (this.options[this.selectedIndex].text.includes('自治会だよりが華やかに')) {
                const communityPrompt = `季節感あふれる日本の自治会だより用のヘッダー画像を作成してください。

以下の要素を含めてください：
- 現在の季節を反映した自然の風景や植物
- 温かみのある地域コミュニティの雰囲気
- 明るく読みやすいデザイン
- 日本の伝統的な要素や季節の行事を取り入れる

画像は横長（16:9）で、上部に「自治会だより」というタイトルを配置できるスペースを残してください。`;
                
                // フォーム送信前にプロンプト欄を更新
                setTimeout(() => {
                    const promptText = document.getElementById('promptText');
                    if (promptText) {
                        promptText.value = communityPrompt;
                    }
                }, 100);
            }
        });
    }
});
    </script>
</body>
</html>
