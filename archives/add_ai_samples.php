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
        'UserId' => 'creative_fox328',
        'Title' => '画像からレシピを提案！Geminiで簡単料理、買い物リストも自動生成',
        'AiName' => 'Gemini (Google)',
        'AiUrl' => 'https://gemini.google.com/',
        'Product' => '季節の野菜を使った料理レシピと買い物リスト',
        'Prompt' => '添付した画像の野菜を使った簡単で美味しい家庭料理のレシピを3つ提案してください。各レシピには、料理名、材料（4人分）、調理手順を含めてください。最後に、これらのレシピに必要な食材をまとめた買い物リストも作成してください。',
        'HowToUse' => "1. スマートフォンで冷蔵庫の中や手持ちの食材の写真を撮影します\n2. Geminiのチャットに写真をアップロードし、上記のプロンプトを入力します\n3. 生成されたレシピと買い物リストを確認します\n4. 必要に応じて「もっと簡単なレシピにして」「調理時間を短くして」などと指示すると、要望に合わせたレシピに調整してくれます\n5. 買い物リストはコピーしてメモアプリに保存したり、家族と共有したりできます"
    ],
    [
        'UserId' => 'community_helper42',
        'Title' => '自治会活動をAIでスマートに！v0.dev/catでタスク管理Webアプリのuiを爆速生成',
        'AiName' => 'v0.dev/cat',
        'AiUrl' => 'https://v0.dev/',
        'Product' => '自治会活動管理アプリのUI設計',
        'Prompt' => "シンプルな自治会活動管理Webアプリケーションのuiを作成してください。以下の機能が必要です：\n1. 「活動カレンダー」：今後の清掃活動や会合の日程を表示\n2. 「お知らせ掲示板」：重要なお知らせを投稿・閲覧できる機能\n3. 「集金管理」：会費の納入状況を管理する機能\n4. 「メンバーリスト」：自治会メンバーの連絡先リスト\n\nモダンでシンプルなデザインで、高齢者でも使いやすいUIにしてください。",
        'HowToUse' => "1. v0.dev/catにアクセスし、上記のプロンプトを入力します\n2. 生成されたUIデザインを確認します\n3. 必要に応じて「ボタンを大きくして」「文字サイズを大きくして」などと指示して調整します\n4. 生成されたコードをコピーして、自分のWebサイトに実装します\n5. 必要に応じてカスタマイズを加えて、自治会のニーズに合わせます"
    ],
    [
        'UserId' => 'excel_master99',
        'Title' => '後期高齢者とＡＩ：ＡＩにExcel作成を手伝ってもらう',
        'AiName' => 'chatgpt',
        'AiUrl' => 'https://chat.openai.com/',
        'Product' => '自治会費集金管理システム（Excel）',
        'Prompt' => "Excelで自治会費の集金管理システムを作りたいです。以下の機能を含めてください：\n\n1. メインシート：「2025年度自治会費集金表」\n   - 縦軸：会員名（A氏、B氏など、10名分の行を用意）\n   - 横軸：4月から翌3月までの12ヶ月\n   - 各セルには納入額を入力（標準は月500円）\n   - 右端に年間合計額を自動計算する列\n   - 下部に月ごとの合計額を自動計算する行\n\n2. 設定シート：\n   - 会員情報（名前、住所、電話番号）\n   - 月額設定（標準額、割引額など）\n\n3. 機能：\n   - 納入状況に応じてセルの色が変わる（納入済み：緑、未納：赤）\n   - 簡単な印刷設定\n   - 月ごとの納入率を計算するグラフ\n\n詳細なExcel関数とセル書式の設定方法を教えてください。",
        'HowToUse' => "1. ChatGPTにアクセスし、上記のプロンプトを入力します\n2. 生成された指示に従ってExcelファイルを作成します\n3. 必要に応じて「印刷設定をもっと詳しく」「グラフの作り方を教えて」などと追加質問します\n4. 完成したExcelファイルに実際のデータを入力して使用します\n5. 年度が変わる際は、新しいファイルを作成するか、既存ファイルをコピーして年度を更新します"
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
    foreach ($samples as $sample) {
        $stmt->bindParam(':UserId', $sample['UserId']);
        $stmt->bindParam(':Title', $sample['Title']);
        $stmt->bindParam(':AiName', $sample['AiName']);
        $stmt->bindParam(':AiUrl', $sample['AiUrl']);
        $stmt->bindParam(':Product', $sample['Product']);
        $stmt->bindParam(':Prompt', $sample['Prompt']);
        $stmt->bindParam(':HowToUse', $sample['HowToUse']);
        $stmt->execute();
        $addedCount++;
    }
    
    // トランザクションをコミット
    $conn->commit();
    
    $success = "サンプルデータの追加が完了しました。追加件数: " . $addedCount;
    
} catch(PDOException $e) {
    // エラーが発生した場合はロールバック
    if ($conn) {
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
    <title>AI活用サンプル追加</title>
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
        h1, h2 {
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
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <h1>AI活用サンプル追加</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="container">
        <h2>追加したサンプル</h2>
        
        <?php foreach ($samples as $sample): ?>
            <div class="sample-card">
                <div class="sample-title"><?php echo htmlspecialchars($sample['Title']); ?></div>
                <div class="sample-ai">AI: <?php echo htmlspecialchars($sample['AiName']); ?></div>
                <div class="sample-product">製品: <?php echo htmlspecialchars($sample['Product']); ?></div>
            </div>
        <?php endforeach; ?>
        
        <a href="ViewView.php" class="btn">AI活用サンプル一覧に戻る</a>
    </div>
</body>
</html>