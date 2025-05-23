<?php
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
$success = "";

// 追加するサンプルデータ
$samples = [
    [
        'UserId' => 'design_helper42',
        'Title' => '自治会イベントポスターを簡単作成！MidjourneyでプロレベルのデザインAI生成',
        'AiName' => 'Midjourney',
        'AiUrl' => 'https://www.midjourney.com/',
        'Product' => '自治会イベントポスター用イラスト',
        'Prompt' => "A poster for a community summer festival, Japanese neighborhood association event, watercolor style, cheerful atmosphere, families enjoying activities together, paper lanterns, food stalls, traditional games, bright summer colors, text area at the top for event title, clean layout, professional design, high quality illustration --ar 3:4 --v 6",
        'HowToUse' => "1. Midjourneyのウェブサイトにアクセスし、Discordサーバーに参加します
2. 専用のチャンネルで上記のプロンプトを入力します（/imagine コマンドを使用）
3. 生成された4つの画像から最適なものを選びます
4. 必要に応じて「Upscale」して高解像度版を生成します
5. ダウンロードした画像をポスターテンプレートに配置します
6. イベント名、日時、場所などのテキスト情報を追加します
7. 印刷またはデジタル配布用にPDF形式で保存します

※ プロンプトの最後の「--ar 3:4」は縦長のポスター比率を指定、「--v 6」はMidjourney Version 6を使用することを意味します。季節やイベントに合わせて「summer」を「autumn」「winter」「spring」に変更したり、「festival」を「cleanup day」「disaster prevention workshop」などに変更することで、様々なイベントに対応できます。"
    ],
    [
        'UserId' => 'safety_first99',
        'Title' => '防災マニュアルの説明図をDALL-E 3で分かりやすく生成',
        'AiName' => 'DALL-E 3',
        'AiUrl' => 'https://openai.com/dall-e-3',
        'Product' => '防災マニュアル用説明図',
        'Prompt' => "Create a clear, informative illustration for a Japanese neighborhood disaster prevention manual. Show a cross-section view of a typical Japanese home with labeled safety measures during an earthquake. Include: emergency exit routes marked with arrows, safe spots to take cover, location of emergency supplies, and proper placement of fire extinguishers. Use a simple, clean art style with limited colors (red, blue, black) on a white background. Add Japanese labels for key items. Make it look like a professional safety diagram that would appear in an official guide.",
        'HowToUse' => "1. ChatGPTのDALL-E 3機能またはDALL-E 3のウェブサイトにアクセスします
2. 上記のプロンプトを入力します
3. 生成された画像をダウンロードします
4. 必要に応じて画像編集ソフトで日本語のテキストを追加・調整します
5. 防災マニュアルのレイアウトに合わせて配置します
6. 説明文と組み合わせて、分かりやすい防災情報を作成します

※ プロンプトの内容を変更することで、地震以外の災害（台風、洪水、火災など）に対応した説明図も作成できます。また、「Japanese home」の部分を「apartment building」や「community center」に変更することで、様々な建物タイプに対応できます。"
    ],
    [
        'UserId' => 'newsletter_pro55',
        'Title' => '自治会だよりが華やかに！Stable Diffusionで季節感あふれるヘッダー画像',
        'AiName' => 'Stable Diffusion',
        'AiUrl' => 'https://stability.ai/',
        'Product' => '自治会だより用ヘッダー画像',
        'Prompt' => "A beautiful header image for a Japanese neighborhood association newsletter, showing the local area in spring season with cherry blossoms, clean streets, traditional Japanese houses, mountains in background, blue sky with few clouds, watercolor painting style, soft pastel colors, horizontal banner format, space for text overlay, professional magazine quality, detailed illustration, 8k --width 1200 --height 300",
        'HowToUse' => "1. Stable DiffusionのウェブUIにアクセスします（DreamStudio、Automatic1111など）
2. 上記のプロンプトを入力します
3. 画像サイズを横長（1200×300ピクセル）に設定します
4. 生成された画像をダウンロードします
5. 必要に応じて画像編集ソフトで「自治会だより」などのテキストを追加します
6. ニュースレターのテンプレートに配置して使用します

※ プロンプトの「spring season with cherry blossoms」の部分を季節に合わせて変更できます。例えば「summer season with lush greenery」「autumn season with colorful foliage」「winter season with snow covered landscape」などに変更することで、四季折々のヘッダー画像を作成できます。"
    ],
    [
        'UserId' => 'digital_notice123',
        'Title' => 'デジタル回覧板の背景画像をLeonardo.AIで自動生成',
        'AiName' => 'Leonardo.AI',
        'AiUrl' => 'https://leonardo.ai/',
        'Product' => 'デジタル回覧板の背景画像',
        'Prompt' => "A subtle background image for a digital community bulletin board, Japanese neighborhood style, very light pastel colors, minimal design with simple patterns of traditional Japanese motifs, unobtrusive design that won't interfere with text overlay, professional UI background, soft texture, clean modern look with traditional Japanese elements, high resolution",
        'HowToUse' => "1. Leonardo.AIのウェブサイトにアクセスします
2. 上記のプロンプトを入力します
3. モデルは「Leonardo Creative」または「Leonardo Diffusion XL」を選択します
4. アスペクト比は16:9を選択します
5. 生成された画像の中から最適なものを選びダウンロードします
6. デジタル回覧板のアプリやウェブサイトの背景として設定します
7. テキストや情報が読みやすいことを確認します

※ プロンプトの「traditional Japanese motifs」の部分を「seasonal elements」に変更し、「spring cherry blossoms」「summer fireworks」「autumn leaves」「winter snow patterns」などを追加することで、季節に合わせた背景画像を作成できます。"
    ],
    [
        'UserId' => 'web_master77',
        'Title' => '自治会ウェブサイト用アイコンセットをFirefly AIで統一感あるデザインに',
        'AiName' => 'Adobe Firefly',
        'AiUrl' => 'https://firefly.adobe.com/',
        'Product' => '自治会ウェブサイト用アイコンセット',
        'Prompt' => "Create a simple, clean icon representing a neighborhood community center in Japanese style. Minimalist design, single building with traditional Japanese roof elements, suitable for website navigation, flat design style with limited color palette (blue and dark gray), square format with transparent background, professional look, vector style quality.",
        'HowToUse' => "1. Adobe Fireflyのウェブサイトにアクセスします
2. 上記のプロンプトを入力します
3. 生成された画像の中から最適なものを選びます
4. ダウンロードしてウェブサイトのアイコンとして使用します
5. 同様のスタイルで他のアイコンも作成します：
   - 「community center」を「event calendar」に変更→イベントカレンダーアイコン
   - 「community center」を「announcement board」に変更→お知らせアイコン
   - 「community center」を「contact form」に変更→お問い合わせアイコン
   - 「community center」を「member directory」に変更→会員名簿アイコン
6. すべてのアイコンをウェブサイトに実装します

※ プロンプトの「blue and dark gray」の部分を変更することで、ウェブサイトのカラースキームに合わせたアイコンを作成できます。また、「flat design」を「3D style」や「watercolor style」に変更することで、異なる雰囲気のアイコンセットを作成できます。"
    ]
];

try {
    // データベース接続
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // トランザクション開始
    $conn->beginTransaction();

    // サンプルデータの追加
    $stmt = $conn->prepare("INSERT INTO AISampleInfo (UserId, Title, AiName, AiUrl, Product, Prompt, HowToUse, created_at, updated_at) VALUES (:UserId, :Title, :AiName, :AiUrl, :Product, :Prompt, :HowToUse, NOW(), NOW())");

    $addedCount = 0;
    $newIds = [];
    foreach ($samples as $sample) {
        $stmt->bindParam(':UserId', $sample['UserId']);
        $stmt->bindParam(':Title', $sample['Title']);
        $stmt->bindParam(':AiName', $sample['AiName']);
        $stmt->bindParam(':AiUrl', $sample['AiUrl']);
        $stmt->bindParam(':Product', $sample['Product']);
        $stmt->bindParam(':Prompt', $sample['Prompt']);
        $stmt->bindParam(':HowToUse', $sample['HowToUse']);
        $stmt->execute();
        $newIds[] = $conn->lastInsertId();
        $addedCount++;
    }

    // トランザクションをコミット
    $conn->commit();

    $success = "画像生成AIサンプルの追加が完了しました。追加件数: " . $addedCount;

} catch(PDOException $e) {
    // エラーが発生した場合はロールバック
    if (isset($conn)) {
        $conn->rollback();
    }
    $error = "エラー: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>画像生成AIサンプル登録</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
        }
        h1, h2, h3 {
            color: #2c3e50;
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
        .sample-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
        }
        .sample-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .sample-ai {
            font-size: 14px;
            color: #3498db;
            margin-bottom: 10px;
        }
        .sample-product {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            margin-right: 10px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .image-preview {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
        }
        .ai-comparison {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .ai-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e9ecef;
        }
        .ai-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .ai-strength {
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>画像生成AIサンプル登録</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="container">
        <h2>追加した画像生成AIサンプル</h2>
        
        <?php foreach ($samples as $index => $sample): ?>
            <div class="sample-card">
                <div class="sample-title"><?php echo htmlspecialchars($sample['Title']); ?></div>
                <div class="sample-ai">AI: <?php echo htmlspecialchars($sample['AiName']); ?></div>
                <div class="sample-product">製品: <?php echo htmlspecialchars($sample['Product']); ?></div>
                
                <h3>イメージ例</h3>
                <?php
                    $imageQueries = [
                        "Japanese community summer festival poster with lanterns and food stalls, watercolor style",
                        "Cross-section diagram of Japanese home showing earthquake safety measures with labeled elements",
                        "Japanese neighborhood newsletter header with cherry blossoms and traditional houses, watercolor style",
                        "Subtle Japanese pattern background for digital bulletin board, light pastel colors",
                        "Minimalist icon of Japanese community center building, flat design style"
                    ];
                    $imageQuery = urlencode($imageQueries[$index]);
                ?>
                <img src="/placeholder.svg?height=300&width=500&query=<?php echo $imageQuery; ?>" alt="<?php echo htmlspecialchars($sample['Product']); ?>" class="image-preview">
                
                <?php if (isset($newIds[$index])): ?>
                    <a href="View.php?id=<?php echo $newIds[$index]; ?>" class="btn">詳細を見る</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <h2>画像生成AI比較</h2>
        <div class="ai-comparison">
            <div class="ai-card">
                <div class="ai-name">Midjourney</div>
                <div class="ai-strength">
                    <strong>特徴:</strong> 芸術的で美しい画像生成に優れています。ポスターやイベントチラシなど、視覚的なインパクトが必要な用途に最適です。
                </div>
            </div>
            
            <div class="ai-card">
                <div class="ai-name">DALL-E 3</div>
                <div class="ai-strength">
                    <strong>特徴:</strong> テキスト指示の理解力が高く、複雑な説明図や情報グラフィックスの作成に適しています。防災マニュアルなどの説明資料に最適です。
                </div>
            </div>
            
            <div class="ai-card">
                <div class="ai-name">Stable Diffusion</div>
                <div class="ai-strength">
                    <strong>特徴:</strong> カスタマイズ性が高く、様々なスタイルの画像を生成できます。ニュースレターのヘッダーなど、定期的に更新する画像に適しています。
                </div>
            </div>
            
            <div class="ai-card">
                <div class="ai-name">Leonardo.AI</div>
                <div class="ai-strength">
                    <strong>特徴:</strong> 背景やテクスチャの生成に優れています。テキストと組み合わせて使用する背景画像の作成に最適です。
                </div>
            </div>
            
            <div class="ai-card">
                <div class="ai-name">Adobe Firefly</div>
                <div class="ai-strength">
                    <strong>特徴:</strong> 商用利用に適した画像生成が可能で、アイコンやロゴなどのグラフィック要素の作成に優れています。
                </div>
            </div>
        </div>
        
        <a href="View.php" class="btn">AI活用サンプル一覧に戻る</a>
    </div>
</body>
</html>