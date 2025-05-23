<?php
// 出力バッファリングを開始
ob_start();

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース接続情報
$servername = "mysql213.phy.lolipop.lan";
$username = "LAA1337491";
$password = "kami2004";
$dbname = "LAA1337491-nsk";

// 初期化
$error = "";
$sample = null;
$category = null;
$conn = null;
$isAdvanced = false;
$categories = [];
$recentSamples = [];
$popularSamples = [];

// 上級者モードの設定
$showAdvanced = isset($_GET['advanced']) && $_GET['advanced'] == '1';

// サンプルIDの取得
$sampleId = isset($_GET['id']) ? intval($_GET['id']) : null;

// カテゴリIDの取得
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : null;

// サービスタイプの取得
$serviceType = isset($_GET['service_type']) ? $_GET['service_type'] : null;

// AIサービスの設定
$aiServices = [
// テキスト生成AI
'text_generation' => [
    'title' => 'テキスト生成AI',
    'icon' => '🤖',
    'description' => 'テキストを生成するAIサービスです。文章作成、質問応答、翻訳などに使用できます。',
    'services' => [
        'chatgpt' => [
            'name' => 'ChatGPT',
            'url' => 'https://chat.openai.com/',
            'can_pass_prompt' => false,
            'reason' => 'ChatGPTはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'ChatGPTのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🤖'
        ],
        'claude' => [
            'name' => 'Claude',
            'url' => 'https://claude.ai/',
            'can_pass_prompt' => false,
            'reason' => 'Claudeはプロンプトをパラメータとして渡す機能をサポートしていません。',
            'instructions' => 'Claudeのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🧠'
        ],
        'gemini' => [
            'name' => 'Google Gemini',
            'url' => 'https://gemini.google.com/',
            'can_pass_prompt' => false,
            'reason' => 'Google Gemini（旧Bard）はURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Google Geminiのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🌐'
        ],
        'copilot' => [
            'name' => 'Microsoft Copilot',
            'url' => 'https://copilot.microsoft.com/',
            'can_pass_prompt' => false,
            'reason' => 'Microsoft Copilot（旧Bing AI）はURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Microsoft Copilotのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🔍'
        ],
        'meta_ai' => [
            'name' => 'Meta AI',
            'url' => 'https://meta.ai/',
            'can_pass_prompt' => false,
            'reason' => 'Meta AIはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Meta AIのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '📱'
        ],
        'grok' => [
            'name' => 'Grok (X AI)',
            'url' => 'https://grok.x.ai/',
            'can_pass_prompt' => false,
            'reason' => 'GrokはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Grokのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🔮'
        ],
        'perplexity' => [
            'name' => 'Perplexity AI',
            'url' => 'https://www.perplexity.ai/',
            'can_pass_prompt' => false,
            'reason' => 'Perplexity AIはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Perplexity AIのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🔎'
        ],
        'mistral' => [
            'name' => 'Mistral AI',
            'url' => 'https://chat.mistral.ai/',
            'can_pass_prompt' => false,
            'reason' => 'Mistral AIはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Mistral AIのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🌪️'
        ],
        'cohere' => [
            'name' => 'Cohere',
            'url' => 'https://cohere.com/',
            'can_pass_prompt' => false,
            'reason' => 'CohereはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Cohereのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🔄'
        ],
        'poe' => [
            'name' => 'Poe',
            'url' => 'https://poe.com/',
            'can_pass_prompt' => false,
            'reason' => 'PoeはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Poeのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。複数のAIモデル（Claude、GPT-4など）にアクセスできます。',
            'icon' => '📝'
        ],
        'huggingchat' => [
            'name' => 'HuggingChat',
            'url' => 'https://huggingface.co/chat/',
            'can_pass_prompt' => false,
            'reason' => 'HuggingChatはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'HuggingChatのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🤗'
        ],
        'anthropic_opus' => [
            'name' => 'Claude 3 Opus',
            'url' => 'https://claude.ai/',
            'can_pass_prompt' => false,
            'reason' => 'Claude 3 OpusはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Claude AIのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。モデル選択からOpusを選んでください。',
            'icon' => '🧠'
        ],
        'anthropic_sonnet' => [
            'name' => 'Claude 3 Sonnet',
            'url' => 'https://claude.ai/',
            'can_pass_prompt' => false,
            'reason' => 'Claude 3 SonnetはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Claude AIのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。モデル選択からSonnetを選んでください。',
            'icon' => '🧠'
        ],
        'anthropic_haiku' => [
            'name' => 'Claude 3 Haiku',
            'url' => 'https://claude.ai/',
            'can_pass_prompt' => false,
            'reason' => 'Claude 3 HaikuはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Claude AIのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。モデル選択からHaikuを選んでください。',
            'icon' => '🧠'
        ],
        'llama3' => [
            'name' => 'Llama 3',
            'url' => 'https://llama.meta.ai/',
            'can_pass_prompt' => false,
            'reason' => 'Llama 3はURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Llama 3のウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🦙'
        ],
        'v0' => [
            'name' => 'v0 (Vercel AI)',
            'url' => 'https://v0.dev/',
            'can_pass_prompt' => false,
            'reason' => 'v0はURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'v0のウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。主にUIデザインやコード生成に特化しています。',
            'icon' => '⚡'
        ],
    ]
],
// 画像生成AI
'image_generation' => [
    'title' => '画像生成AI',
    'icon' => '🎨',
    'description' => 'テキストプロンプトから画像を生成するAIサービスです。イラスト、写真風画像、アートワークなどを作成できます。',
    'services' => [
        'midjourney' => [
            'name' => 'Midjourney',
            'url' => 'https://www.midjourney.com/',
            'can_pass_prompt' => false,
            'reason' => 'MidjourneyはDiscordを通じて使用するため、URLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Midjourneyの公式Discordサーバーに参加し、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🎨'
        ],
        'dalle' => [
            'name' => 'DALL-E (OpenAI)',
            'url' => 'https://labs.openai.com/',
            'can_pass_prompt' => false,
            'reason' => 'DALL-EはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'DALL-Eのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🖼️'
        ],
        'stable_diffusion' => [
            'name' => 'Stable Diffusion',
            'url' => 'https://stablediffusionweb.com/',
            'can_pass_prompt' => false,
            'reason' => 'Stable DiffusionはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Stable Diffusionのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🖌️'
        ],
        'leonardo' => [
            'name' => 'Leonardo.AI',
            'url' => 'https://leonardo.ai/',
            'can_pass_prompt' => false,
            'reason' => 'Leonardo.AIはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Leonardo.AIのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🎭'
        ],
        'firefly' => [
            'name' => 'Adobe Firefly',
            'url' => 'https://firefly.adobe.com/',
            'can_pass_prompt' => false,
            'reason' => 'Adobe FireflyはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Adobe Fireflyのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。',
            'icon' => '🔥'
        ],
        'character_ai' => [
            'name' => 'Character.AI',
            'url' => 'https://character.ai/',
            'can_pass_prompt' => false,
            'reason' => 'Character.AIはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Character.AIのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。画像生成機能も提供しています。',
            'icon' => '👤'
        ],
    ]
],
// 音声・音楽生成AI
'audio_generation' => [
    'title' => '音声・音楽生成AI',
    'icon' => '🎵',
    'description' => 'テキストから音声や音楽を生成するAIサービスです。ナレーション、歌声、楽曲などを作成できます。',
    'services' => [
        'suno' => [
            'name' => 'Suno',
            'url' => 'https://suno.ai/',
            'can_pass_prompt' => false,
            'reason' => 'SunoはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Sunoのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。音楽生成AIです。',
            'icon' => '🎵'
        ],
        'elevenlabs' => [
            'name' => 'ElevenLabs',
            'url' => 'https://elevenlabs.io/',
            'can_pass_prompt' => false,
            'reason' => 'ElevenLabsはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'ElevenLabsのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。音声合成AIです。',
            'icon' => '🗣️'
        ],
    ]
],
// 動画生成AI
'video_generation' => [
    'title' => '動画生成AI',
    'icon' => '🎬',
    'description' => 'テキストプロンプトから動画を生成するAIサービスです。短いクリップやアニメーションを作成できます。',
    'services' => [
        'runway' => [
            'name' => 'Runway Gen-2',
            'url' => 'https://runwayml.com/',
            'can_pass_prompt' => false,
            'reason' => 'Runway Gen-2はURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Runwayのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。動画生成AIです。',
            'icon' => '🎬'
        ],
        'pika' => [
            'name' => 'Pika Labs',
            'url' => 'https://pika.art/',
            'can_pass_prompt' => false,
            'reason' => 'Pika LabsはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'Pika Labsのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。動画生成AIです。',
            'icon' => '📹'
        ],
    ]
],
// 日本語特化AI
'japanese_ai' => [
    'title' => '日本語特化AI',
    'icon' => '🇯🇵',
    'description' => '日本語に特化したAIサービスです。日本語の文章生成や音声合成に優れています。',
    'services' => [
        'coeFont' => [
            'name' => 'CoeFont',
            'url' => 'https://coefont.cloud/',
            'can_pass_prompt' => false,
            'reason' => 'CoeFontはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'CoeFontのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。日本語音声合成AIです。',
            'icon' => '🇯🇵'
        ],
        'rinna' => [
            'name' => 'rinna',
            'url' => 'https://chat.rinna.co.jp/',
            'can_pass_prompt' => false,
            'reason' => 'rinnaはURLパラメータでプロンプトを渡す機能をサポートしていません。',
            'instructions' => 'rinnaのウェブサイトを開き、プロンプトを手動でコピー＆ペーストしてください。日本語対応チャットAIです。',
            'icon' => '🇯🇵'
        ],
    ]
],
];

try {
// データベース接続
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// カテゴリ一覧の取得
$stmt = $conn->query("SELECT * FROM AISampleCategories ORDER BY display_order, name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 最近追加されたサンプルの取得（上級者モードとカテゴリに応じて）
$query = "SELECT s.*, c.name as category_name, c.icon_key 
          FROM AISampleInfo s 
          LEFT JOIN AISampleCategories c ON s.category_id = c.id";

$whereConditions = [];

// 上級者モードでない場合は、上級者向けサンプルを除外
if (!$showAdvanced) {
    $whereConditions[] = "s.is_advanced = 0";
}

// カテゴリが指定されている場合は、そのカテゴリのサンプルのみ表示
if ($categoryId) {
    $whereConditions[] = "s.category_id = " . $categoryId;
}

// サービスタイプが指定されている場合は、そのAIタイプに関連するサンプルのみ表示
if ($serviceType) {
    switch ($serviceType) {
        case 'text_generation':
            $whereConditions[] = "(s.AiName LIKE '%GPT%' OR s.AiName LIKE '%Claude%' OR s.AiName LIKE '%Gemini%' OR s.AiName LIKE '%Copilot%' OR s.AiName LIKE '%Bard%' OR s.AiName LIKE '%Grok%' OR s.AiName LIKE '%Perplexity%' OR s.AiName LIKE '%Mistral%' OR s.AiName LIKE '%Cohere%' OR s.AiName LIKE '%Poe%' OR s.AiName LIKE '%Hugging%' OR s.AiName LIKE '%Llama%' OR s.AiName LIKE '%v0%')";
            break;
        case 'image_generation':
            $whereConditions[] = "(s.AiName LIKE '%Midjourney%' OR s.AiName LIKE '%DALL-E%' OR s.AiName LIKE '%Stable Diffusion%' OR s.AiName LIKE '%Leonardo%' OR s.AiName LIKE '%Firefly%' OR s.AiName LIKE '%Character%')";
            break;
        case 'audio_generation':
            $whereConditions[] = "(s.AiName LIKE '%Suno%' OR s.AiName LIKE '%ElevenLabs%' OR s.AiName LIKE '%音声%' OR s.AiName LIKE '%音楽%')";
            break;
        case 'video_generation':
            $whereConditions[] = "(s.AiName LIKE '%Runway%' OR s.AiName LIKE '%Pika%' OR s.AiName LIKE '%動画%')";
            break;
        case 'japanese_ai':
            $whereConditions[] = "(s.AiName LIKE '%CoeFont%' OR s.AiName LIKE '%rinna%' OR s.AiName LIKE '%日本語%')";
            break;
    }
}

// WHERE句の構築
if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

$query .= " ORDER BY s.id DESC LIMIT 6";

$stmt = $conn->query($query);
$recentSamples = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 特定のサンプルが指定されている場合
if ($sampleId) {
    // サンプル情報の取得
    $stmt = $conn->prepare("SELECT s.*, c.name as category_name, c.icon_key 
                           FROM AISampleInfo s 
                           LEFT JOIN AISampleCategories c ON s.category_id = c.id 
                           WHERE s.id = :id");
    $stmt->bindParam(':id', $sampleId);
    $stmt->execute();
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sample) {
        // 上級者向けサンプルかどうかを確認
        $isAdvanced = isset($sample['is_advanced']) && $sample['is_advanced'] == 1;
        
        // 上級者向けサンプルが指定されているが、上級者モードでない場合
        if ($isAdvanced && !$showAdvanced) {
            $warning = "このサンプルは上級者向けです。表示するには上級者モードに切り替えてください。";
        }
    } else {
        $error = "指定されたサンプルが見つかりません。";
    }
}
// 変更箇所1: try ブロック内、$advancedCount を取得する直前あたり（約370行目付近）に以下のコードを追加

// 各AIサービスタイプのサンプル数を取得
$serviceTypeCounts = [];
foreach (array_keys($aiServices) as $type) {
    $countQuery = "SELECT COUNT(*) FROM AISampleInfo s WHERE ";
    $whereConditions = [];
    
    // 上級者モードでない場合は、上級者向けサンプルを除外
    if (!$showAdvanced) {
        $whereConditions[] = "s.is_advanced = 0";
    }
    
    // AIタイプに応じた条件を追加
    switch ($type) {
        case 'text_generation':
            $whereConditions[] = "(s.AiName LIKE '%GPT%' OR s.AiName LIKE '%Claude%' OR s.AiName LIKE '%Gemini%' OR s.AiName LIKE '%Copilot%' OR s.AiName LIKE '%Bard%' OR s.AiName LIKE '%Grok%' OR s.AiName LIKE '%Perplexity%' OR s.AiName LIKE '%Mistral%' OR s.AiName LIKE '%Cohere%' OR s.AiName LIKE '%Poe%' OR s.AiName LIKE '%Hugging%' OR s.AiName LIKE '%Llama%' OR s.AiName LIKE '%v0%')";
            break;
        case 'image_generation':
            $whereConditions[] = "(s.AiName LIKE '%Midjourney%' OR s.AiName LIKE '%DALL-E%' OR s.AiName LIKE '%Stable Diffusion%' OR s.AiName LIKE '%Leonardo%' OR s.AiName LIKE '%Firefly%' OR s.AiName LIKE '%Character%')";
            break;
        case 'audio_generation':
            $whereConditions[] = "(s.AiName LIKE '%Suno%' OR s.AiName LIKE '%ElevenLabs%' OR s.AiName LIKE '%音声%' OR s.AiName LIKE '%音楽%')";
            break;
        case 'video_generation':
            $whereConditions[] = "(s.AiName LIKE '%Runway%' OR s.AiName LIKE '%Pika%' OR s.AiName LIKE '%動画%')";
            break;
        case 'japanese_ai':
            $whereConditions[] = "(s.AiName LIKE '%CoeFont%' OR s.AiName LIKE '%rinna%' OR s.AiName LIKE '%日本語%')";
            break;
    }
    
    // WHERE句の構築
    if (!empty($whereConditions)) {
        $countQuery .= implode(" AND ", $whereConditions);
    }
    
    $stmt = $conn->query($countQuery);
    $serviceTypeCounts[$type] = $stmt->fetchColumn();
}
// 上級者向けサンプルの数を取得
$stmt = $conn->query("SELECT COUNT(*) FROM AISampleInfo WHERE is_advanced = 1");
$advancedCount = $stmt->fetchColumn();

} catch(PDOException $e) {
$error = "データベースエラー: " . $e->getMessage();
} catch(Exception $e) {
$error = $e->getMessage();
}

// アイコン一覧
$icons = [
'document' => '📄',
'image' => '🖼️',
'accounting' => '💹',
'event' => '🎪',
'info' => '📢',
'disaster' => '🚨',
'community' => '👥',
'other' => '📌'
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>初心者向けＡＩ活用サンプル集<?php echo $sample ? ' - ' . htmlspecialchars($sample['Title']) : ''; ?></title>
<style>
    :root {
        --primary-color: #3498db;
        --primary-dark: #2980b9;
        --secondary-color: #6c757d;
        --secondary-dark: #5a6268;
        --success-color: #2ecc71;
        --warning-color: #f39c12;
        --danger-color: #e74c3c;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --border-color: #dee2e6;
        --text-color: #333;
        --text-muted: #6c757d;
        --bg-color: #f9f9f9;
    }
    
    * {
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Helvetica Neue', Arial, sans-serif;
        line-height: 1.6;
        color: var(--text-color);
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background-color: var(--bg-color);
    }
    
    h1, h2, h3, h4 {
        color: var(--dark-color);
        margin-top: 0;
    }
    
    a {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    a:hover {
        text-decoration: underline;
    }
    
    .container {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .error, .warning {
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
    }
    
    .error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .btn {
        display: inline-block;
        background-color: var(--secondary-color);
        color: white;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        margin-right: 10px;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .btn:hover {
        background-color: var(--secondary-dark);
        text-decoration: none;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
    }
    
    .btn-primary:hover {
        background-color: var(--primary-dark);
    }
    
    .btn-warning {
        background-color: var(--warning-color);
    }
    
    .btn-warning:hover {
        background-color: #e67e22;
    }
    
    .btn-danger {
        background-color: var(--danger-color);
    }
    
    .btn-danger:hover {
        background-color: #c0392b;
    }
    
    .btn-success {
        background-color: var(--success-color);
    }
    
    .btn-success:hover {
        background-color: #27ae60;
    }

    .count {
        margin-left: 5px;
        font-size: 0.9em;
        color: var(--text-muted);
    }

    .category-link.active .count {
        color: white;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .header-title h1 {
        margin: 0;
        font-size: 28px;
    }
    
    .header-description {
        color: var(--text-muted);
        margin-top: 5px;
    }
    
    .header-actions {
        display: flex;
        gap: 10px;
    }
    
    .main-content {
        display: flex;
        gap: 20px;
    }
    
    .sidebar {
        width: 250px;
        flex-shrink: 0;
    }
    
    .content {
        flex-grow: 1;
    }
    
    .category-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .category-item {
        margin-bottom: 8px;
    }
    
    .category-link {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 4px;
        color: var(--text-color);
        text-decoration: none;
        transition: background-color 0.2s;
    }
    
    .category-link:hover {
        background-color: var(--light-color);
        text-decoration: none;
    }
    
    .category-link.active {
        background-color: var(--primary-color);
        color: white;
    }
    
    .category-icon {
        margin-right: 10px;
        font-size: 1.2em;
    }
    
    .service-category-list {
        margin-top: 20px;
        border-top: 1px solid var(--border-color);
        padding-top: 15px;
    }
    
    .service-category-title {
        font-weight: bold;
        margin-bottom: 10px;
        color: var(--primary-dark);
    }
    
    .sample-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .sample-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .sample-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .sample-card-header {
        padding: 15px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .sample-card-title {
        margin: 0;
        font-size: 18px;
        display: flex;
        align-items: center;
    }
    
    .sample-card-body {
        padding: 15px;
        flex-grow: 1;
    }
    
    .sample-card-footer {
        padding: 15px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .sample-meta {
        color: var(--text-muted);
        font-size: 0.9em;
    }
    
    .sample-prompt {
        background-color: var(--light-color);
        padding: 10px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 0.9em;
        margin: 10px 0;
        max-height: 100px;
        overflow-y: auto;
        white-space: normal;
    }
    
    .sample-detail {
        margin-top: 30px;
    }
    
    .sample-header {
        display: flex;
        align-items: flex-start;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .sample-header-icon {
        font-size: 2.5em;
        margin-right: 20px;
        margin-top: 5px;
    }
    
    .sample-header-info {
        flex-grow: 1;
    }
    
    .sample-header-title {
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
    }
    
    .sample-section {
        margin-bottom: 30px;
    }
    
    .sample-section-title {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .sample-product {
        background-color: var(--light-color);
        padding: 15px;
        border-radius: 5px;
        font-family: monospace;
        white-space: pre-wrap;
        overflow-x: auto;
    }
    
    .category-badge, .advanced-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.9em;
        margin-right: 10px;
    }
    
    .category-badge {
        background-color: var(--light-color);
        color: var(--text-color);
    }
    
    .advanced-badge {
        background-color: var(--danger-color);
        color: white;
        margin-left: 10px;
    }

    .intermediate-badge {
        background-color: var(--warning-color);
        color: white;
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.9em;
        margin-left: 10px;
    }
    
    .section-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .section-title h2 {
        margin: 0;
    }
    
    .section-title-link {
        font-size: 0.9em;
    }
    
    .footer {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
        text-align: center;
        color: var(--text-muted);
    }
    
    .beginner-guide {
        background-color: #e8f4f8;
        border-left: 4px solid var(--primary-color);
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 0 4px 4px 0;
        position: relative;
    }

    .beginner-guide-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .beginner-guide-title {
        font-weight: bold;
        color: var(--primary-dark);
    }

    .guide-close-btn {
        font-size: 18px;
        color: var(--text-muted);
        text-decoration: none;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .guide-close-btn:hover {
        background-color: rgba(0, 0, 0, 0.1);
        text-decoration: none;
    }

    .show-guide-container {
        margin-bottom: 20px;
        text-align: right;
    }

    .show-guide-btn {
        font-size: 14px;
        color: var(--text-muted);
        text-decoration: none;
        padding: 5px 10px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        background-color: var(--light-color);
    }

    .show-guide-btn:hover {
        background-color: #e9ecef;
        text-decoration: none;
    }
    
    /* モバイル対応 */
    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .header-actions {
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .main-content {
            flex-direction: column;
        }
        
        .sidebar {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .sample-grid {
            grid-template-columns: 1fr;
        }
        
        .sample-header {
            flex-direction: column;
        }
        
        .sample-header-icon {
            margin-bottom: 15px;
        }
        
        .btn {
            margin-bottom: 10px;
        }
        
        .ai-service-buttons {
            flex-direction: column;
        }
    }
</style>
</head>
<body>
    <div class="header">
        <div class="header-title">
            <h1>初心者向けＡＩ活用サンプル集</h1>
            <div class="header-description">初心者に役立つＡＩプロンプトのサンプル集</div>
        </div>
        <div class="header-actions">
            <a href="AISample_Register.php" class="btn btn-primary">新規サンプル登録</a>
            <?php if ($showAdvanced): ?>
                <a href="?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?><?php echo $serviceType ? 'service_type=' . $serviceType . '&' : ''; ?>advanced=0" class="btn">一般向けモードに切替</a>
            <?php else: ?>
                <a href="?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?><?php echo $serviceType ? 'service_type=' . $serviceType . '&' : ''; ?>advanced=1" class="btn btn-warning">上級者モードに切替</a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($warning)): ?>
        <div class="warning">
            <?php echo $warning; ?>
            <p><a href="?id=<?php echo $sampleId; ?>&advanced=1" class="btn btn-warning">上級者モードで表示</a></p>
        </div>
    <?php endif; ?>
    
    <?php if ($showAdvanced): ?>
        <div class="warning">
            <strong>上級者モード:</strong> 現在、v0.devやGrokなどの高度なAIツールのサンプルも含めて表示しています。これらは一般の方には難しい場合があります。
        </div>
    <?php else: ?>
    <?php
    // Cookieで表示状態を管理
    $showGuide = true;
    if (isset($_COOKIE['hide_beginner_guide']) && $_COOKIE['hide_beginner_guide'] == '1') {
        $showGuide = false;
    }
    
    // 表示/非表示の切り替え処理
    if (isset($_GET['toggle_guide'])) {
        if ($_GET['toggle_guide'] == 'hide') {
            setcookie('hide_beginner_guide', '1', time() + 60*60*24*30, '/'); // 30日間保存
            $showGuide = false;
        } elseif ($_GET['toggle_guide'] == 'show') {
            setcookie('hide_beginner_guide', '0', time() + 60*60*24*30, '/'); // 30日間保存
            $showGuide = true;
        }
    }
    ?>
    
    <div class="beginner-guide" style="<?php echo $showGuide ? '' : 'display: none;'; ?>">
        <div class="beginner-guide-header">
            <div class="beginner-guide-title">初めての方へ</div>
            <a href="?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?><?php echo $serviceType ? 'service_type=' . $serviceType . '&' : ''; ?><?php echo $showAdvanced ? 'advanced=1&' : ''; ?>toggle_guide=hide" class="guide-close-btn" title="閉じる">×</a>
        </div>
        <p>このサイトでは、初心者がコピペするだけあるいはわずかな修正で使えるAIプロンプトのサンプルを集めています。カテゴリから探すか、最近追加されたサンプルから選んでください。</p>
        <p>サンプルを選ぶと、プロンプトの内容や生成された作品を見ることができます。プロンプトはコピーして、お好みのAIツールで使用できます。</p>
    </div>
    
    <?php if (!$showGuide): ?>
        <div class="show-guide-container">
            <a href="?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?><?php echo $serviceType ? 'service_type=' . $serviceType . '&' : ''; ?><?php echo $showAdvanced ? 'advanced=1&' : ''; ?>toggle_guide=show" class="show-guide-btn">初めての方へのガイドを表示</a>
        </div>
    <?php endif; ?>
<?php endif; ?>
    
    <div class="main-content">
        <div class="sidebar">
            <div class="container">
                <h3>プロンプトカテゴリ</h3>
                <p class="category-description">AIプロンプトの用途別分類</p>
                <ul class="category-list">
                    <li class="category-item">
                        <a href="?<?php echo $showAdvanced ? 'advanced=1' : ''; ?>" 
                           class="category-link<?php echo !isset($_GET['category']) && !isset($_GET['service_type']) ? ' active' : ''; ?>">
                            <span class="category-icon">📋</span>すべて
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <?php 
                        $catIcon = isset($category['icon_key']) && isset($icons[$category['icon_key']]) 
                            ? $icons[$category['icon_key']] 
                            : $icons['other'];
                        ?>
                        <li class="category-item">
                            <a href="?category=<?php echo $category['id']; ?><?php echo $showAdvanced ? '&advanced=1' : ''; ?>" 
                               class="category-link<?php echo isset($_GET['category']) && $_GET['category'] == $category['id'] ? ' active' : ''; ?>">
                                <span class="category-icon"><?php echo $catIcon; ?></span>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <!-- AIサービスタイプによるカテゴリ分け -->
                <!-- // 変更箇所2: サイドバーのAIサービスタイプリスト表示部分（約850行目付近）を以下のように変更 -->
                <div class="service-category-list">
                    <div class="service-category-title">AIサービスタイプ</div>
                    <ul class="category-list">
                        <?php foreach ($aiServices as $typeKey => $typeInfo): ?>
                            <li class="category-item">
                                <a href="?service_type=<?php echo $typeKey; ?><?php echo $showAdvanced ? '&advanced=1' : ''; ?>" 
                                class="category-link<?php echo isset($_GET['service_type']) && $_GET['service_type'] == $typeKey ? ' active' : ''; ?>">
                                    <span class="category-icon"><?php echo $typeInfo['icon']; ?></span>
                                    <?php echo htmlspecialchars($typeInfo['title']); ?>
                                    <?php if (isset($serviceTypeCounts[$typeKey]) && $serviceTypeCounts[$typeKey] > 0): ?>
                                        <span class="count">(<?php echo $serviceTypeCounts[$typeKey]; ?>)</span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="content">
            <?php if ($sample && (!$isAdvanced || $showAdvanced)): ?>
                <!-- サンプル詳細表示 -->
                <div class="container sample-detail">
                    <div class="sample-header">
                        <?php 
                        $categoryIcon = '📌'; // デフォルトアイコン
                        if (isset($sample['icon_key']) && isset($icons[$sample['icon_key']])) {
                            $categoryIcon = $icons[$sample['icon_key']];
                        } elseif (isset($sample['category_id']) && $sample['category_id'] > 0) {
                            $categoryIcon = $icons['other'];
                        }
                        ?>
                        <div class="sample-header-icon"><?php echo $categoryIcon; ?></div>
                        <div class="sample-header-info">
                            <h2 class="sample-header-title">
                                <?php echo htmlspecialchars($sample['Title']); ?>
                                <?php if ($isAdvanced): ?>
                                    <span class="advanced-badge">上級者向け</span>
                                <?php elseif (isset($sample['needs_modification']) && $sample['needs_modification'] == 1): ?>
                                    <span class="intermediate-badge">中級者向け</span>
                                <?php endif; ?>
                            </h2>
                            <div class="sample-meta">
                                <span>AI: <?php echo htmlspecialchars($sample['AiName']); ?></span>
                                <span> | </span>
                                <span>登録日: 
                                    <?php 
                                    // RegisterDateが存在するか確認してから処理
                                    if (isset($sample['RegisterDate']) && $sample['RegisterDate']) {
                                        echo date('Y-m-d', strtotime($sample['RegisterDate']));
                                    } else {
                                        echo '不明';
                                    }
                                    ?>
                                </span>
                                <span> | </span>
                                <span>カテゴリ: 
                                    <?php if (isset($sample['category_id']) && $sample['category_id'] > 0 && isset($sample['category_name'])): ?>
                                        <span class="category-badge">
                                            <span class="category-icon"><?php echo $categoryIcon; ?></span>
                                            <?php echo htmlspecialchars($sample['category_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="category-badge">
                                            <span class="category-icon">📌</span>
                                            未分類
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sample-section">
                        <h3 class="sample-section-title">プロンプト</h3>
                        <div class="sample-prompt" id="prompt-text"><?php echo htmlspecialchars($sample['Prompt']); ?></div>
                        <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 10px;">
                            <button class="copy-btn" onclick="copyToClipboard('prompt-text')">プロンプトをコピー</button>
                            
                            <!-- 外部翻訳サービスへのリンク -->
                            <a href="https://translate.google.com/?sl=<?php echo $is_english ? 'en' : 'ja'; ?>&tl=<?php echo $is_english ? 'ja' : 'en'; ?>&text=<?php echo urlencode($sample['Prompt']); ?>&op=translate" target="_blank" class="btn" style="font-size: 0.8em; padding: 4px 8px; background-color: #4285f4; color: white;">
                                Google翻訳で開く
                            </a>
                            <a href="https://www.deepl.com/translator#<?php echo $is_english ? 'en' : 'ja'; ?>/<?php echo $is_english ? 'ja' : 'en'; ?>/<?php echo urlencode($sample['Prompt']); ?>" target="_blank" class="btn" style="font-size: 0.8em; padding: 4px 8px; background-color: #0f2b46; color: white;">
                                DeepL翻訳で開く
                            </a>
                            <!-- <?php
                            // プロンプトが英語かどうかを簡易判定（英数字の割合が高いかどうか）
                            $text = $sample['Prompt'];
                            $english_chars = preg_match_all('/[a-zA-Z0-9\s.,!?;:\'\"()]/u', $text, $matches);
                            $total_chars = mb_strlen($text);
                            $is_english = ($total_chars > 0) && ($english_chars / $total_chars > 0.7);
                            
                            // 日本語から英語、または英語から日本語への翻訳リンク
                            $target_lang = $is_english ? 'JA' : 'EN';
                            $lang_text = $is_english ? '日本語に翻訳' : '英語に翻訳';
                            ?>
                            
                            <a href="translate_prompt.php?text=<?php echo urlencode($sample['Prompt']); ?>&target_lang=<?php echo $target_lang; ?>" class="btn" style="font-size: 0.8em; padding: 4px 8px; background-color: #6c5ce7; color: white;">
                                <?php echo $lang_text; ?>
                            </a> -->
                        </div>
                    </div>
                    
                    <!-- AIサービスへのリンクセクション -->
                    <div class="ai-services">
                        <div class="ai-services-title">AIサービスで使用する</div>
                        <p>以下のAIサービスでこのプロンプトを使用できます：</p>
                        
                        <?php foreach ($aiServices as $categoryKey => $category): ?>
                            <div class="ai-service-category">
                                <h4 class="ai-service-category-title"><?php echo htmlspecialchars($category['title']); ?></h4>
                                <div class="ai-service-buttons">
                                    <?php foreach ($category['services'] as $serviceKey => $service): ?>
                                        <?php if ($service['can_pass_prompt']): ?>
                                            <a href="<?php echo $service['url'] . urlencode($sample['Prompt']); ?>" target="_blank" class="btn btn-success">
                                                <span class="ai-service-icon"><?php echo $service['icon']; ?></span>
                                                <?php echo htmlspecialchars($service['name']); ?>で開く
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn" onclick="showAiServiceInfo('<?php echo $categoryKey . '_' . $serviceKey; ?>')">
                                                <span class="ai-service-icon"><?php echo $service['icon']; ?></span>
                                                <?php echo htmlspecialchars($service['name']); ?>で使用
                                            </button>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php foreach ($category['services'] as $serviceKey => $service): ?>
                                    <div id="ai-service-info-<?php echo $categoryKey . '_' . $serviceKey; ?>" class="ai-service-info" style="display: none;">
                                        <div class="ai-service-info-title">
                                            <span class="ai-service-icon"><?php echo $service['icon']; ?></span>
                                            <?php echo htmlspecialchars($service['name']); ?>での使用方法
                                        </div>
                                        <?php if (!$service['can_pass_prompt']): ?>
                                            <p><?php echo htmlspecialchars($service['reason']); ?></p>
                                        <?php endif; ?>
                                        <p><?php echo htmlspecialchars($service['instructions']); ?></p>
                                        <a href="<?php echo $service['url']; ?>" target="_blank" class="btn btn-primary">
                                            <?php echo htmlspecialchars($service['name']); ?>を開く
                                        </a>
                                        <button type="button" class="btn" onclick="hideAiServiceInfo('<?php echo $categoryKey . '_' . $serviceKey; ?>')">
                                            閉じる
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!empty($sample['Product'])): ?>
                    <div class="sample-section">
                        <h3 class="sample-section-title">生成された作品</h3>
                        <div class="sample-product"><?php echo htmlspecialchars($sample['Product']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($sample['Description'])): ?>
                    <div class="sample-section">
                        <h3 class="sample-section-title">説明</h3>
                        <div><?php echo nl2br(htmlspecialchars($sample['Description'])); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 30px;">
                        <a href="?<?php echo $categoryId ? 'category=' . $categoryId . '&' : ''; ?><?php echo $serviceType ? 'service_type=' . $serviceType . '&' : ''; ?><?php echo $showAdvanced ? 'advanced=1' : ''; ?>" class="btn">トップに戻る</a>
                        <?php if ($isAdvanced): ?>
                            <a href="?advanced=1" class="btn btn-warning">他の上級者向けサンプルを見る</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- サンプル一覧表示 -->
                <div class="section-title">
                    <h2>
                        <?php if ($serviceType && isset($aiServices[$serviceType])): ?>
                            <?php echo htmlspecialchars($aiServices[$serviceType]['title']); ?>向けサンプル
                        <?php elseif ($categoryId): ?>
                            <?php 
                            $categoryName = "選択されたカテゴリ";
                            foreach ($categories as $cat) {
                                if ($cat['id'] == $categoryId) {
                                    $categoryName = $cat['name'];
                                    break;
                                }
                            }
                            echo htmlspecialchars($categoryName) . "のサンプル";
                            ?>
                        <?php else: ?>
                            最近追加されたサンプル
                        <?php endif; ?>
                    </h2>
                    <a href="AISampleList_with_advanced.php<?php echo $showAdvanced ? '?advanced=1' : ''; ?><?php echo $categoryId ? ($showAdvanced ? '&' : '?') . 'category=' . $categoryId : ''; ?><?php echo $serviceType ? (($showAdvanced || $categoryId) ? '&' : '?') . 'service_type=' . $serviceType : ''; ?>" class="section-title-link">すべて見る &raquo;</a>
                </div>
                
                <?php if ($serviceType && isset($aiServices[$serviceType])): ?>
                    <div class="container">
                        <p><?php echo htmlspecialchars($aiServices[$serviceType]['description']); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="sample-grid">
                    <?php foreach ($recentSamples as $recentSample): ?>
                        <?php 
                        $isAdvancedSample = isset($recentSample['is_advanced']) && $recentSample['is_advanced'] == 1;
                        $isIntermediateSample = isset($recentSample['needs_modification']) && $recentSample['needs_modification'] == 1;
                        $categoryIcon = '📌'; // デフォルトアイコン
                        if (isset($recentSample['icon_key']) && isset($icons[$recentSample['icon_key']])) {
                            $categoryIcon = $icons[$recentSample['icon_key']];
                        } elseif (isset($recentSample['category_id']) && $recentSample['category_id'] > 0) {
                            $categoryIcon = $icons['other'];
                        }
                        ?>
                        <div class="sample-card">
                            <div class="sample-card-header">
                                <h3 class="sample-card-title">
                                    <span class="category-icon"><?php echo $categoryIcon; ?></span>
                                    <?php echo htmlspecialchars($recentSample['Title']); ?>
                                    <?php if ($isAdvancedSample): ?>
                                        <span class="advanced-badge">上級</span>
                                    <?php elseif ($isIntermediateSample): ?>
                                        <span class="intermediate-badge">中級</span>
                                    <?php endif; ?>
                                </h3>
                            </div>
                            <div class="sample-card-body">
                                <div class="sample-meta">
                                    <div>AI: <?php echo htmlspecialchars($recentSample['AiName']); ?></div>
                                    <div>カテゴリ: 
                                        <?php if (isset($recentSample['category_name'])): ?>
                                            <?php echo htmlspecialchars($recentSample['category_name']); ?>
                                        <?php else: ?>
                                            未分類
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="sample-prompt">
                                    <?php 
                                    $prompt = htmlspecialchars($recentSample['Prompt']);
                                    echo strlen($prompt) > 150 ? substr($prompt, 0, 150) . '...' : $prompt;
                                    ?>
                                </div>
                            </div>
                            <div class="sample-card-footer">
                                <div class="sample-meta">
                                    <?php 
                                    // RegisterDateが存在するか確認してから処理
                                    if (isset($recentSample['RegisterDate']) && $recentSample['RegisterDate']) {
                                        echo date('Y-m-d', strtotime($recentSample['RegisterDate']));
                                    } else {
                                        echo '不明';
                                    }
                                    ?>
                                </div>
                                <a href="?id=<?php echo $recentSample['id']; ?><?php echo $showAdvanced ? '&advanced=1' : ''; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $serviceType ? '&service_type=' . $serviceType : ''; ?>" class="btn btn-primary">詳細を見る</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (empty($recentSamples)): ?>
                        <div class="container">
                            <p>表示できるサンプルはありません。</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!$showAdvanced && $advancedCount > 0 && !$sample): ?>
        <!-- 上級者向けサンプルへのリンク（フッター部分） -->
        <div class="footer-advanced">
            <h3>上級者向けサンプル</h3>
            <p>v0.devやGrokなどの高度なAIツールのサンプルが <?php echo $advancedCount; ?> 件あります。これらは一般の方には難しい場合があります。</p>
            <a href="?advanced=1<?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $serviceType ? '&service_type=' . $serviceType : ''; ?>" class="btn btn-warning">上級者向けサンプルを表示</a>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> 自治会AI活用サンプル集</p>
        </div>
        
        <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.innerText;
            
            navigator.clipboard.writeText(text).then(function() {
                const button = document.querySelector('.copy-btn');
                const originalText = button.innerText;
                button.innerText = 'コピーしました！';
                
                setTimeout(function() {
                    button.innerText = originalText;
                }, 2000);
            }, function() {
                alert('コピーに失敗しました。');
            });
        }
        
        function showAiServiceInfo(serviceKey) {
            // すべての情報パネルを非表示にする
            const infoPanels = document.querySelectorAll('.ai-service-info');
            infoPanels.forEach(panel => {
                panel.style.display = 'none';
            });
            
            // 選択されたサービスの情報パネルを表示する
            const selectedPanel = document.getElementById('ai-service-info-' + serviceKey);
            if (selectedPanel) {
                selectedPanel.style.display = 'block';
                
                // パネルが表示されるようにスクロール
                selectedPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
        
        function hideAiServiceInfo(serviceKey) {
            const panel = document.getElementById('ai-service-info-' + serviceKey);
            if (panel) {
                panel.style.display = 'none';
            }
        }
        </script>
    </body>
</html>
<?php
// 出力バッファリングを終了し、内容を送信
ob_end_flush();
?>
