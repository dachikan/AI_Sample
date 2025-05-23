<?php
// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// メモリ制限とタイムアウトの設定
ini_set('memory_limit', '256M');
set_time_limit(300);

// データベース接続情報
$servername = "mysql213.phy.lolipop.lan";
$username = "LAA1337491";
$password = "kami2004";
$dbname = "LAA1337491-nsk";

// 初期化
$error = "";
$success = "";
$formData = [];
$samples = [];
$mode = "list"; // デフォルトはリスト表示モード

// データベース接続
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!-- データベース接続成功 -->";
    
    // モードの判定
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        // 詳細表示モード
        $mode = "view";
        $sampleId = $_GET['id'];
        
        // データの取得
        $stmt = $conn->prepare("SELECT * FROM AISampleInfo WHERE id = :id");
        $stmt->bindParam(':id', $sampleId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $formData = $row;
            
            // HowToUseフィールドがない場合は空文字を設定
            if (!isset($formData['HowToUse'])) {
                $formData['HowToUse'] = '';
            }
            
            // 使用統計を記録
            $stmt = $conn->prepare("INSERT INTO AIUsageStats (ai_name, sample_id, user_id, action_type) VALUES (:ai_name, :sample_id, :user_id, :action_type)");
            $stmt->bindParam(':ai_name', $formData['AiName'], PDO::PARAM_STR);
            $stmt->bindParam(':sample_id', $sampleId, PDO::PARAM_INT);
            $userId = $_SERVER['REMOTE_ADDR']; // IPアドレスをユーザーIDとして使用
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
            $actionType = 'view';
            $stmt->bindParam(':action_type', $actionType, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $error = "指定されたIDのデータが見つかりません。";
            $mode = "list";
        }
    } elseif (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        // 編集モード
        $mode = "edit";
        $sampleId = $_GET['edit'];
        
        // データの取得
        $stmt = $conn->prepare("SELECT * FROM AISampleInfo WHERE id = :id");
        $stmt->bindParam(':id', $sampleId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $formData = $row;
        } else {
            $error = "指定されたIDのデータが見つかりません。";
            $mode = "list";
        }
    } elseif (isset($_GET['new'])) {
        // 新規作成モード
        $mode = "new";
    } else {
        // リスト表示モード
        $mode = "list";
        
        // 検索条件
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $aiFilter = isset($_GET['ai']) ? $_GET['ai'] : '';
        
        // ページネーション
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // WHERE句の構築
        $whereClause = "";
        $params = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE (Title LIKE :search OR AiName LIKE :search OR Product LIKE :search OR Prompt LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($aiFilter)) {
            $whereClause = empty($whereClause) ? "WHERE AiName LIKE :ai" : "$whereClause AND AiName LIKE :ai";
            $params[':ai'] = "%$aiFilter%";
        }
        
        // 総件数の取得
        $countQuery = "SELECT COUNT(*) FROM AISampleInfo $whereClause";
        $stmt = $conn->prepare($countQuery);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $totalItems = $stmt->fetchColumn();
        $totalPages = ceil($totalItems / $perPage);
        
        // データの取得
        $query = "SELECT * FROM AISampleInfo $whereClause ORDER BY updated_at DESC LIMIT :offset, :limit";
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 利用可能なAI一覧を取得
        $stmt = $conn->prepare("SELECT DISTINCT AiName FROM AISampleInfo ORDER BY AiName");
        $stmt->execute();
        $aiOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // 成功メッセージの表示
    if (isset($_GET['success'])) {
        $success = $_GET['success'];
    }
    
} catch(PDOException $e) {
    $error = "データベース接続エラー: " . $e->getMessage();
}

$conn = null;

// AIの種類を判定
$isClaudeAI = false;
$isChatGPTAI = false;
$isGeminiAI = false;
$isMistralAI = false;
$isLlamaAI = false;

if (!empty($formData) && isset($formData['AiName'])) {
    $aiNameLower = strtolower($formData['AiName']);
    $isClaudeAI = (strpos($aiNameLower, 'claude') !== false);
    $isChatGPTAI = (strpos($aiNameLower, 'chatgpt') !== false || strpos($aiNameLower, 'gpt') !== false);
    $isGeminiAI = (strpos($aiNameLower, 'gemini') !== false || strpos($aiNameLower, 'bard') !== false);
    $isMistralAI = (strpos($aiNameLower, 'mistral') !== false);
    $isLlamaAI = (strpos($aiNameLower, 'llama') !== false);
}

// AIの情報を定義
$aiInfo = [
    'claude' => [
        'name' => 'Claude',
        'developer' => 'Anthropic',
        'url' => 'https://claude.ai/',
        'color' => '#9b59b6',
        'hover_color' => '#8e44ad',
        'description' => 'Claudeは、Anthropicが開発した会話型AIアシスタントです。自然な対話能力と複雑な指示への対応力に優れています。',
        'features' => [
            '自然な会話と長文理解能力',
            '複雑な指示への対応力',
            '安全性と倫理的配慮',
            '創造的な文章生成',
            '長いコンテキスト処理能力'
        ],
        'prompt_url' => 'https://claude.ai/new',
        'prompt_tip' => 'Claudeは詳細なプロンプトに対して特に優れた応答を返します。具体的な指示や例を含めるとより良い結果が得られます。',
        'result_processing' => [
            'Excel形式でエクスポート',
            'PDFとして保存',
            'テキストファイルとして保存',
            'メールで共有'
        ],
        'post_processing_tips' => 'Claude AIの結果は、表形式のデータを含む場合があります。Excel形式でエクスポートすると、データの整理や分析が容易になります。'
    ],
    'chatgpt' => [
        'name' => 'ChatGPT',
        'developer' => 'OpenAI',
        'url' => 'https://chat.openai.com/',
        'color' => '#10a37f',
        'hover_color' => '#0d8a6c',
        'description' => 'ChatGPTは、OpenAIが開発した会話型AIモデルです。幅広い知識と柔軟な応答能力を持っています。',
        'features' => [
            '広範な知識ベース',
            '自然な会話能力',
            '多様なタスクへの対応',
            'コード生成や文章作成の支援',
            'プラグインによる拡張機能'
        ],
        'prompt_url' => 'https://chat.openai.com/',
        'prompt_tip' => 'ChatGPTは明確で具体的な指示に対して最適な結果を返します。目的や必要な詳細情報を含めるとより良い回答が得られます。',
        'result_processing' => [
            'Excel形式でエクスポート',
            'マークダウン形式で保存',
            'コードをIDEで開く',
            'テキストファイルとして保存'
        ],
        'post_processing_tips' => 'ChatGPTの結果は、コードブロックを含むことが多いです。コードをIDEで開くと、すぐに実行や編集ができます。'
    ],
    'gemini' => [
        'name' => 'Gemini',
        'developer' => 'Google',
        'url' => 'https://gemini.google.com/',
        'color' => '#4285f4',
        'hover_color' => '#3367d6',
        'description' => 'Gemini（旧Bard）は、Googleが開発したマルチモーダルAIモデルです。テキスト、画像、音声などの複数の入力形式を理解できます。',
        'features' => [
            'マルチモーダル理解能力',
            'Googleの検索情報へのアクセス',
            'リアルタイムの情報提供',
            '複数言語のサポート',
            'Googleサービスとの連携'
        ],
        'prompt_url' => 'https://gemini.google.com/',
        'prompt_tip' => 'Geminiは画像と組み合わせたプロンプトに強みがあります。また、最新の情報が必要な場合に特に有用です。',
        'result_processing' => [
            'Googleドキュメントに保存',
            'Googleスプレッドシートに転送',
            'テキストファイルとして保存',
            'メールで共有'
        ],
        'post_processing_tips' => 'Geminiの結果は、Googleのサービスと連携させると最も効果的です。Googleドキュメントやスプレッドシートに直接保存すると便利です。'
    ]
];

// 現在のAI情報を取得
$currentAiInfo = null;
if (!empty($formData) && isset($formData['AiName'])) {
    $aiNameLower = strtolower($formData['AiName']);
    foreach ($aiInfo as $key => $info) {
        if (strpos($aiNameLower, $key) !== false) {
            $currentAiInfo = $info;
            break;
        }
    }
}

// AIの色を取得する関数
function getAiColor($aiName) {
    global $aiInfo;
    $aiNameLower = strtolower($aiName);

    foreach ($aiInfo as $key => $info) {
        if (strpos($aiNameLower, $key) !== false) {
            return $info['color'];
        }
    }

    return '#3498db'; // デフォルト色
}

// AIのホバー色を取得する関数
function getAiHoverColor($aiName) {
    global $aiInfo;
    $aiNameLower = strtolower($aiName);

    foreach ($aiInfo as $key => $info) {
        if (strpos($aiNameLower, $key) !== false) {
            return $info['hover_color'];
        }
    }

    return '#2980b9'; // デフォルトホバー色
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php 
        if ($mode == "view" && !empty($formData)) {
            echo htmlspecialchars($formData['Title']) . " - ";
        } elseif ($mode == "edit") {
            echo "編集 - ";
        } elseif ($mode == "new") {
            echo "新規登録 - ";
        }
        ?>
        老人向けＡＩ活用サンプル
    </title>
    <style>
        :root {
            --primary-color: <?php 
                if ($mode == "view" || $mode == "edit") {
                    if (!empty($currentAiInfo)) {
                        echo $currentAiInfo['color'];
                    } else {
                        echo '#3498db';
                    }
                } else {
                    echo '#3498db';
                }
            ?>;
            --primary-hover: <?php 
                if ($mode == "view" || $mode == "edit") {
                    if (!empty($currentAiInfo)) {
                        echo $currentAiInfo['hover_color'];
                    } else {
                        echo '#2980b9';
                    }
                } else {
                    echo '#2980b9';
                }
            ?>;
            --bg-color: <?php 
                if ($mode == "view" || $mode == "edit") {
                    if ($isClaudeAI) echo '#f9f5ff';
                    else if ($isChatGPTAI) echo '#f2f9ff';
                    else if ($isGeminiAI) echo '#f0f7ff';
                    else echo '#f9f9f9';
                } else {
                    echo '#f9f9f9';
                }
            ?>;
        }
        
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: var(--bg-color);
        }
        header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-right: 20px;
        }
        .header-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border-top: 5px solid var(--primary-color);
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            margin-right: 10px;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: var(--primary-hover);
        }
        .btn-back {
            background-color: #95a5a6;
        }
        .btn-back:hover {
            background-color: #7f8c8d;
        }
        .btn-new {
            background-color: #2ecc71;
        }
        .btn-new:hover {
            background-color: #27ae60;
        }
        .sample-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .sample-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .sample-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .sample-card-header {
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
        }
        .sample-card-body {
            padding: 15px;
        }
        .sample-card-title {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .sample-card-ai {
            margin-top: 5px;
            font-size: 14px;
            opacity: 0.8;
        }
        .sample-card-product {
            margin-top: 10px;
            font-size: 14px;
            color: #666;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sample-card-footer {
            padding: 15px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sample-card-date {
            font-size: 12px;
            color: #666;
        }
        .sample-card-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }
        .sample-card-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>老人向けＡＩ活用サンプル</h1>
        <div class="header-actions">
            <a href="AISample_fixed.php" class="btn">サンプル一覧</a>
            <a href="AISample_fixed.php?new=1" class="btn btn-new">新規登録</a>
        </div>
    </header>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($mode == "list"): ?>
        <!-- リスト表示モード -->
        <div class="container">
            <h2>老人向けＡＩ活用サンプル一覧</h2>
            
            <?php if (empty($samples)): ?>
                <p>サンプルが見つかりませんでした。</p>
            <?php else: ?>
                <div class="sample-list">
                    <?php foreach ($samples as $sample): ?>
                        <div class="sample-card">
                            <div class="sample-card-header" style="background-color: <?php echo getAiColor($sample['AiName']); ?>;">
                                <h3 class="sample-card-title"><?php echo htmlspecialchars($sample['Title']); ?></h3>
                                <div class="sample-card-ai"><?php echo htmlspecialchars($sample['AiName']); ?></div>
                            </div>
                            <div class="sample-card-body">
                                <div class="sample-card-product"><?php echo htmlspecialchars($sample['Product']); ?></div>
                            </div>
                            <div class="sample-card-footer">
                                <div class="sample-card-date"><?php echo date('Y-m-d', strtotime($sample['updated_at'])); ?></div>
                                <a href="AISample_fixed.php?id=<?php echo $sample['id']; ?>" class="sample-card-link">詳細を見る</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</body>
</html>