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
$sample = [
    'UserId' => 'excel_pro123',
    'Title' => '自治会費集金表の機能拡張：金額入力と自動計算で管理を効率化',
    'AiName' => 'chatgpt',
    'AiUrl' => 'https://chat.openai.com/',
    'Product' => '機能拡張版自治会費集金管理システム（Excel）',
    'Prompt' => "Excelで自治会費集金表に以下の機能を追加したいです：

1. 金額入力機能
   - 基本設定シートで標準月額や割引率を設定
   - 世帯区分（一般/高齢者/単身）に応じた自動計算
   - 特別徴収月（夏祭り月など）の追加料金設定

2. 自動計算機能
   - 月ごとの集金合計額の自動計算
   - 会員ごとの年間納入額の自動計算
   - 納入率のグラフ化と未納者リストの自動生成
   - 年間予測額と目標達成率の計算

3. 印刷プレビュー機能
   - A4用紙に最適化された印刷レイアウト
   - 全会員一覧/未納者のみ/月別など複数の印刷形式
   - ワンクリックで印刷プレビューを表示するボタン

具体的なExcel関数とVBAマクロの実装方法を教えてください。",
    'HowToUse' => "1. ChatGPTにアクセスし、上記のプロンプトを入力します

2. 生成された指示に従って、以下の手順でExcelファイルを作成します：
   - 基本設定シートの作成（標準月額、割引率、特別徴収月の設定）
   - 会員情報シートの作成（世帯区分、適用割引率の設定）
   - 集金表シートの作成（月ごとの納入額入力と自動計算）
   - 統計シートの作成（グラフと予測機能）
   - VBAマクロの実装（未納者リスト生成、印刷プレビュー機能）

3. 必要に応じて「印刷設定をもっと詳しく」「グラフの作り方を教えて」などと追加質問します

4. 完成したExcelファイルに実際のデータを入力して使用します

5. 年度が変わる際は、新しいファイルを作成するか、既存ファイルをコピーして年度を更新します

※ この拡張版集金表は、単なる集金記録だけでなく、会費管理の効率化と可視化を実現します。特に高齢者の役員でも使いやすいよう、ボタン一つで操作できる機能を多数実装しています。"
];

try {
    // データベース接続
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // サンプルデータの追加
    $stmt = $conn->prepare("INSERT INTO AISampleInfo (UserId, Title, AiName, AiUrl, Product, Prompt, HowToUse, created_at, updated_at) VALUES (:UserId, :Title, :AiName, :AiUrl, :Product, :Prompt, :HowToUse, NOW(), NOW())");
    
    $stmt->bindParam(':UserId', $sample['UserId']);
    $stmt->bindParam(':Title', $sample['Title']);
    $stmt->bindParam(':AiName', $sample['AiName']);
    $stmt->bindParam(':AiUrl', $sample['AiUrl']);
    $stmt->bindParam(':Product', $sample['Product']);
    $stmt->bindParam(':Prompt', $sample['Prompt']);
    $stmt->bindParam(':HowToUse', $sample['HowToUse']);
    $stmt->execute();
    
    $newId = $conn->lastInsertId();
    $success = "機能拡張版自治会費集金表サンプルの追加が完了しました。ID: " . $newId;
    
} catch(PDOException $e) {
    $error = "エラー: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>機能拡張版自治会費集金表サンプル登録</title>
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
        .feature-list {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .feature-list h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .preview-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <h1>機能拡張版自治会費集金表サンプル登録</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="container">
        <h2>追加したサンプル</h2>
        
        <div class="sample-card">
            <div class="sample-title"><?php echo htmlspecialchars($sample['Title']); ?></div>
            <div class="sample-ai">AI: <?php echo htmlspecialchars($sample['AiName']); ?></div>
            <div class="sample-product">製品: <?php echo htmlspecialchars($sample['Product']); ?></div>
            
            <div class="feature-list">
                <h3>主な機能</h3>
                <ul>
                    <li><strong>金額入力機能</strong>: 世帯区分に応じた自動計算、特別徴収月の設定</li>
                    <li><strong>自動計算機能</strong>: 集計、統計、予測機能</li>
                    <li><strong>印刷プレビュー機能</strong>: 複数の印刷形式、A4最適化</li>
                </ul>
            </div>
            
            <h3>イメージ</h3>
            <img src="/placeholder.svg?height=300&width=600&query=Excel spreadsheet showing community association fee collection system with automatic calculations and charts" alt="機能拡張版自治会費集金表" class="preview-image">
        </div>
        
        <a href="View.php" class="btn">AI活用サンプル一覧に戻る</a>
        <?php if (!empty($newId)): ?>
            <a href="View.php?id=<?php echo $newId; ?>" class="btn">追加したサンプルを表示</a>
        <?php endif; ?>
    </div>
</body>
</html>