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
    $relatedSamples = [];
    $mode = "view"; // 基本は詳細表示モード

    // 画像表示用の関数
    function getImagePath($filename) {
        // 画像の基本パス - サーバー上の実際のパスに合わせて修正
        $basePath = '/images/';
        
        // ファイルが存在するか確認
        $serverPath = $_SERVER['DOCUMENT_ROOT'] . $basePath . $filename;
        
        if (file_exists($serverPath)) {
            return $basePath . $filename;
        } else {
            // ファイルが見つからない場合はnullを返す
            return null;
        }
    }

    // データベース接続
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // サンプルIDが指定されているか確認
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $sampleId = $_GET['id'];
            
            // サンプル詳細を取得するクエリ
            $stmt = $conn->prepare("
            SELECT 
                s.*, 
                c.CategoryName,
                s.InputImagePath,
                s.OutputImagePath
            FROM      AISampleInfo s
            LEFT JOIN Categories c ON s.category_id = c.CategoryID
            WHERE     s.id = :id
            ");
            $stmt->bindParam(':id', $sampleId, PDO::PARAM_INT);
            $stmt->execute();
            $sampleData = $stmt->fetch(PDO::FETCH_ASSOC);

            // ここが問題の箇所です - 2回目のfetch()を削除し、$sampleDataを使用する
            if ($sampleData) {
                $formData = $sampleData; // $sampleDataを$formDataにコピー

                // HowToUseフィールドがない場合は空文字を設定
                if (!isset($formData['HowToUse'])) {
                    $formData['HowToUse'] = '';
                }
                
                // 使用統計を記録 - エラーが発生しても処理を続行
                try {
                    $stmt = $conn->prepare("INSERT INTO AIUsageStats (ai_name, sample_id, user_id, action_type) VALUES (:ai_name, :sample_id, :user_id, :action_type)");
                    $stmt->bindParam(':ai_name', $formData['AiName'], PDO::PARAM_STR);
                    $stmt->bindParam(':sample_id', $sampleId, PDO::PARAM_INT);
                    $userId = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
                    $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
                    $actionType = 'view';
                    $stmt->bindParam(':action_type', $actionType, PDO::PARAM_STR);
                    $stmt->execute();
                } catch (PDOException $e) {
                    // 使用統計の記録に失敗しても、ユーザー体験に影響しないようにエラーを無視
                    // 開発時にはコメントを外してエラーを確認できるようにする
                    // $error = "使用統計記録エラー: " . $e->getMessage();
                }
                
                // 関連サンプルを取得（同じAIを使用しているサンプル）
                try {
                    $stmt = $conn->prepare("SELECT id, Title, AiName, Product, updated_at FROM AISampleInfo 
                                            WHERE AiName = :aiName AND id != :currentId 
                                            ORDER BY updated_at DESC LIMIT 3");
                    $stmt->bindParam(':aiName', $formData['AiName'], PDO::PARAM_STR);
                    $stmt->bindParam(':currentId', $sampleId, PDO::PARAM_INT);
                    $stmt->execute();
                    $relatedSamples = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    // 関連サンプルの取得に失敗しても、主要機能には影響しないのでエラーを無視
                }
            } else {
                $error = "指定されたIDのデータが見つかりません。";
                $mode = "error";
            }
        } else {
            // IDが指定されていない場合は一覧ページにリダイレクト
            header("Location: AISampleList_with_advanced.php");
            exit;
        }
        
        // 成功メッセージの表示
        if (isset($_GET['success'])) {
            $success = $_GET['success'];
        }
        
    } catch(PDOException $e) {
        $error = "データベース接続エラー: " . $e->getMessage();
        $mode = "error";
    }

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

    // AIの色を取得する関数
    function getAiColor($aiName) {
        $aiNameLower = strtolower($aiName);
        
        if (strpos($aiNameLower, 'claude') !== false) {
            return '#9b59b6';
        } elseif (strpos($aiNameLower, 'chatgpt') !== false || strpos($aiNameLower, 'gpt') !== false) {
            return '#10a37f';
        } elseif (strpos($aiNameLower, 'gemini') !== false || strpos($aiNameLower, 'bard') !== false) {
            return '#4285f4';
        } elseif (strpos($aiNameLower, 'mistral') !== false) {
            return '#5e35b1';
        } elseif (strpos($aiNameLower, 'llama') !== false) {
            return '#ff6f00';
        } else {
            return '#3498db'; // デフォルト色
        }
    }

    // AIのホバー色を取得する関数
    function getAiHoverColor($aiName) {
        $aiNameLower = strtolower($aiName);
        
        if (strpos($aiNameLower, 'claude') !== false) {
            return '#8e44ad';
        } elseif (strpos($aiNameLower, 'chatgpt') !== false || strpos($aiNameLower, 'gpt') !== false) {
            return '#0d8a6c';
        } elseif (strpos($aiNameLower, 'gemini') !== false || strpos($aiNameLower, 'bard') !== false) {
            return '#3367d6';
        } elseif (strpos($aiNameLower, 'mistral') !== false) {
            return '#4527a0';
        } elseif (strpos($aiNameLower, 'llama') !== false) {
            return '#e65100';
        } else {
            return '#2980b9'; // デフォルトホバー色
        }
    }

    // 日付をフォーマットする関数
    function formatDate($dateString) {
        $date = new DateTime($dateString);
        return $date->format('Y年m月d日');
    }

    // プロンプトをコピーするためのJavaScriptコード
    $copyScript = "
    function copyPrompt() {
        const promptText = document.getElementById('prompt-text').innerText;
        navigator.clipboard.writeText(promptText)
            .then(() => {
                const copyBtn = document.getElementById('copy-btn');
                const originalText = copyBtn.innerText;
                copyBtn.innerText = 'コピーしました！';
                setTimeout(() => {
                    copyBtn.innerText = originalText;
                }, 2000);
            })
            .catch(err => {
                alert('コピーに失敗しました: ' + err);
            });
    }
    ";
    ?>
<?php
    // 画像パスの検証と代替表示の関数
    function validateImagePath($imagePath) {
        if (empty($imagePath)) {
            return false;
        }
        
        // 絶対パスに変換
        $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
        
        // ファイルの存在確認
        if (!file_exists($absolutePath)) {
            return false;
        }
        
        // 画像ファイルかどうかの確認
        $imageInfo = @getimagesize($absolutePath);
        if ($imageInfo === false) {
            return false;
        }
        
        return true;
    }

    // 使用例
    // 画像パスの検証関数の呼び出し部分を修正
    // 228-229行目付近
    $inputImageValid = false;
    $outputImageValid = false;

    if (isset($sampleData) && is_array($sampleData)) {
        $inputImageValid = isset($sampleData['InputImagePath']) ? validateImagePath($sampleData['InputImagePath']) : false;
        $outputImageValid = isset($sampleData['OutputImagePath']) ? validateImagePath($sampleData['OutputImagePath']) : false;
    }
    ?>

<!-- 表示部分 -->
<?php if ($inputImageValid): ?>
    <!-- 画像表示コード -->
<?php else: ?>
    <!-- 代替表示コード -->
<?php endif; ?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php 
        if ($mode == "view" && !empty($formData)) {
            echo htmlspecialchars($formData['Title']) . " - ";
        }
        ?>
        AI活用サンプル詳細
    </title>
    <!-- ヘッダー内に追加 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.2/simple-lightbox.min.css">
    <style>
        :root {
            --primary-color: <?php 
                if ($mode == "view" && !empty($formData)) {
                    echo getAiColor($formData['AiName']);
                } else {
                    echo '#3498db';
                }
            ?>;
            --primary-hover: <?php 
                if ($mode == "view" && !empty($formData)) {
                    echo getAiHoverColor($formData['AiName']);
                } else {
                    echo '#2980b9';
                }
            ?>;
            --bg-color: <?php 
                if ($mode == "view") {
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
        
        h1, h2, h3 {
            color: #2c3e50;
        }
        
        h1 {
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
            margin-bottom: 30px;
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
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .detail-header {
            margin-bottom: 20px;
        }
        
        .detail-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .detail-meta-item {
            display: flex;
            align-items: center;
        }
        
        .detail-ai {
            font-size: 16px;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .detail-section {
            margin-bottom: 30px;
        }
        
        .detail-section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .detail-product {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .detail-prompt {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
            font-family: monospace;
            margin-bottom: 20px;
            position: relative;
        }
        
        .detail-how-to-use {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        
        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
        }
        
        .copy-btn:hover {
            background-color: #f1f1f1;
        }
        
        .examples-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .example-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .example-card-header {
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        
        .example-card-title {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .example-card-body {
            padding: 15px;
        }
        
        .example-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .no-image-placeholder {
            width: 100%;
            height: 200px;
            background-color: #f8f9fa;
            border: 1px dashed #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .placeholder-text {
            color: #999;
            text-align: center;
        }
        
        .ai-services {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .ai-service-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px;
            text-align: center;
            transition: transform 0.2s;
        }
        
        .ai-service-card:hover {
            transform: translateY(-5px);
        }
        
        .ai-service-logo {
            max-height: 50px;
            margin-bottom: 10px;
        }
        
        .related-samples {
            margin-top: 30px;
        }
        
        .related-samples-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .related-sample-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px;
            transition: transform 0.2s;
        }
        
        .related-sample-card:hover {
            transform: translateY(-5px);
        }
        
        .related-sample-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .related-sample-ai {
            color: var(--primary-color);
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .related-sample-product {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .related-sample-date {
            font-size: 12px;
            color: #999;
        }
        
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        /* レスポンシブデザイン */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                margin-top: 15px;
            }
            
            .examples-container {
                grid-template-columns: 1fr;
            }
            
            .ai-services {
                grid-template-columns: 1fr 1fr;
            }
            
            .related-samples-list {
                grid-template-columns: 1fr;
            }
            
            .navigation-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .navigation-buttons .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
    <style>
        /* 画像表示関連のスタイル */
        .example-image {
            min-height: 200px;
            position: relative;
            overflow: hidden;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .example-image img {
            max-height: 400px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .hover-zoom:hover {
            transform: scale(1.03);
            cursor: pointer;
        }

        .no-image-placeholder {
            min-height: 200px;
            background-color: #f8f9fa;
            border-radius: 4px;
            color: #6c757d;
        }

        /* レスポンシブ対応 */
        @media (max-width: 767.98px) {
            .example-image img {
                max-height: 300px;
            }
        }

        @media (max-width: 575.98px) {
            .example-image img {
                max-height: 250px;
            }
        }

        /* 画像ローディングアニメーション */
        .example-image.loading {
            position: relative;
        }

        .example-image.loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 40px;
            height: 40px;
            margin-top: -20px;
            margin-left: -20px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* 画像説明テキスト */
        .example-description {
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        /* カード高さ揃え */
        .card-deck .card {
            display: flex;
            flex-direction: column;
        }

        .card-body {
            flex: 1 1 auto;
        }
        </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.2/simple-lightbox.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lightboxの初期化
            var lightbox = new SimpleLightbox('.image-popup', {
                captionsData: 'title',
                captionDelay: 250,
                animationSpeed: 250,
                fadeSpeed: 200,
                scrollZoom: true,
                maxZoom: 3
            });
            
            // 画像の読み込みエラー処理
            document.querySelectorAll('.example-image img').forEach(function(img) {
                img.onerror = function() {
                    var container = this.closest('.example-image');
                    container.innerHTML = `
                        <div class="text-center text-muted p-4">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <p>画像の読み込みに失敗しました</p>
                        </div>
                    `;
                };
            });
        });
    </script>
    <script>
        <?php echo $copyScript; ?>
    </script>
</head>
<body>
    <header>
        <h1>老人向けAI活用サンプル詳細</h1>
        <div class="header-actions">
            <a href="AISampleList_with_advanced.php" class="btn btn-secondary">サンプル一覧に戻る</a>
            <a href="AISample_main.php" class="btn">メイン画面に戻る</a>
        </div>
    </header>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($mode == "view" && !empty($formData)): ?>
        <div class="container">
            <div class="detail-header">
                <div class="detail-title"><?php echo htmlspecialchars($formData['Title']); ?></div>
                <div class="detail-ai"><?php echo htmlspecialchars($formData['AiName']); ?></div>
                <div class="detail-meta">
                    <div class="detail-meta-item">
                        <span>投稿者: <?php echo htmlspecialchars($formData['UserId']); ?></span>
                    </div>
                    <div class="detail-meta-item">
                        <span>登録日: <?php echo formatDate($formData['created_at']); ?></span>
                    </div>
                    <div class="detail-meta-item">
                        <span>更新日: <?php echo formatDate($formData['updated_at']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <div class="detail-section-title">製品・サービス</div>
                <div class="detail-product"><?php echo nl2br(htmlspecialchars($formData['Product'])); ?></div>
            </div>
            
            <div class="detail-section">
                <div class="detail-section-title">プロンプト</div>
                <div class="detail-prompt" id="prompt-text"><?php echo htmlspecialchars($formData['Prompt']); ?></div>
                <button id="copy-btn" class="copy-btn" onclick="copyPrompt()">コピー</button>
            </div>
            
            <?php if (!empty($formData['HowToUse'])): ?>
            <div class="detail-section">
                <div class="detail-section-title">使い方</div>
                <div class="detail-how-to-use"><?php echo nl2br(htmlspecialchars($formData['HowToUse'])); ?></div>
            </div>
            <?php endif; ?>
            
            <!-- 使用例セクション -->
            <div class="detail-section">
                <div class="detail-section-title">使用例</div>
                <div class="examples-container">
                    <!-- 入力例 -->
                    <div class="example-card">
                        <div class="example-card-header">
                            <h3 class="example-card-title">入力例</h3>
                        </div>
                        <div class="example-card-body">
                            <?php
                            // 入力例の画像を選択
                            $inputImage = null;
                            if (!empty($formData)) {
                                // タイトルに基づいて画像を選択
                                $title = isset($formData['Title']) ? $formData['Title'] : '';
                                
                                if (strpos($title, '料理') !== false || strpos($title, 'レシピ') !== false) {
                                    $inputImage = getImagePath('japanese-curry.png');
                                } elseif (strpos($title, 'アイコン') !== false) {
                                    $inputImage = getImagePath('japanese-community-icons.png');
                                } elseif (strpos($title, 'ヘッダー') !== false) {
                                    $inputImage = getImagePath('japanese-community-header.png');
                                } elseif (strpos($title, 'Excel') !== false || strpos($title, '集金') !== false) {
                                    $inputImage = getImagePath('abstract-geometric-shapes.png');
                                } else {
                                    // デフォルト画像
                                    $inputImage = getImagePath('abstract-geometric-shapes.png');
                                }
                            }
                            
                            // 画像を表示
                            if ($inputImage) {
                                echo '<img src="' . htmlspecialchars($inputImage) . '" alt="入力例" class="example-image">';
                            } else {
                                echo '<div class="no-image-placeholder">';
                                echo '<div class="placeholder-text">';
                                echo '<p>入力例の画像はありません</p>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                            <div class="example-description">
                                <p>このような入力をAIに提供します。</p>
                            </div>
                        </div>
                    </div>

                    <!-- 出力例 -->
                    <div class="example-card">
                        <div class="example-card-header">
                            <h3 class="example-card-title">出力例</h3>
                        </div>
                        <div class="example-card-body">
                            <?php
                            // 出力例の画像を選択
                            $outputImage = null;
                            if (!empty($formData)) {
                                // タイトルに基づいて画像を選択
                                $title = isset($formData['Title']) ? $formData['Title'] : '';
                                
                                if (strpos($title, '料理') !== false || strpos($title, 'レシピ') !== false) {
                                    $outputImage = getImagePath('food-recipe-generation.png');
                                } elseif (strpos($title, 'アイコン') !== false) {
                                    $outputImage = getImagePath('japanese-community-icons.png');
                                } elseif (strpos($title, 'ヘッダー') !== false) {
                                    $outputImage = getImagePath('japanese-community-header.png');
                                } elseif (strpos($title, 'Excel') !== false || strpos($title, '集金') !== false) {
                                    $outputImage = getImagePath('abstract-geometric-shapes.png');
                                } else {
                                    // デフォルト画像
                                    $outputImage = getImagePath('abstract-geometric-shapes.png');
                                }
                            }
                            
                            // 画像を表示
                            if ($outputImage) {
                                echo '<img src="' . htmlspecialchars($outputImage) . '" alt="出力例" class="example-image">';
                            } else {
                                echo '<div class="no-image-placeholder">';
                                echo '<div class="placeholder-text">';
                                echo '<p>出力例の画像はありません</p>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                            <div class="example-description">
                                <p>AIは上記のような結果を生成します。</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 既存のフォームの適切な位置に追加 -->
            <div class="form-group">
                <label for="input_image">入力例画像:</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="input_image" name="input_image" accept="image/*">
                    <label class="custom-file-label" for="input_image">ファイルを選択...</label>
                </div>
                <?php if (!empty($formData['InputImagePath'])): ?>
                    <div class="mt-2">
                        <p>現在の画像:</p>
                        <img src="<?php echo htmlspecialchars($formData['InputImagePath']); ?>" alt="入力例" class="img-thumbnail" style="max-height: 200px;">
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" id="remove_input_image" name="remove_input_image">
                            <label class="form-check-label" for="remove_input_image">
                                この画像を削除
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                <small class="form-text text-muted">推奨サイズ: 800x600px以下、最大ファイルサイズ: 2MB</small>
            </div>

            <div class="form-group">
                <label for="output_image">出力例画像:</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="output_image" name="output_image" accept="image/*">
                    <label class="custom-file-label" for="output_image">ファイルを選択...</label>
                </div>
                <?php if (!empty($formData['OutputImagePath'])): ?>
                    <div class="mt-2">
                        <p>現在の画像:</p>
                        <img src="<?php echo htmlspecialchars($formData['OutputImagePath']); ?>" alt="出力例" class="img-thumbnail" style="max-height: 200px;">
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" id="remove_output_image" name="remove_output_image">
                            <label class="form-check-label" for="remove_output_image">
                                この画像を削除
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                <small class="form-text text-muted">推奨サイズ: 800x600px以下、最大ファイルサイズ: 2MB</small>
            </div>

            <!-- 使用例セクション -->
            <div class="container mt-4">
                <h3>使用例</h3>
                <div class="row">
                    <!-- 入力例 -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4>入力例</h4>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <?php if (!empty($sampleData['InputImagePath']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $sampleData['InputImagePath'])): ?>
                                    <div class="example-image mb-3 flex-grow-1 d-flex align-items-center justify-content-center">
                                        <a href="<?php echo htmlspecialchars($sampleData['InputImagePath']); ?>" class="image-popup" title="入力例の拡大表示">
                                            <img src="<?php echo htmlspecialchars($sampleData['InputImagePath']); ?>" alt="入力例" class="img-fluid rounded shadow-sm hover-zoom">
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="no-image-placeholder flex-grow-1 d-flex align-items-center justify-content-center">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-image fa-3x mb-3"></i>
                                            <p>入力例の画像はありません</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="example-description mt-auto">
                                    <p>このような入力をAIに提供します。</p>
                                    <?php if (!empty($sampleData['InputDescription'])): ?>
                                        <div class="alert alert-light">
                                            <?php echo nl2br(htmlspecialchars($sampleData['InputDescription'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 出力例 -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4>出力例</h4>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <?php if (!empty($sampleData['OutputImagePath']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $sampleData['OutputImagePath'])): ?>
                                    <div class="example-image mb-3 flex-grow-1 d-flex align-items-center justify-content-center">
                                        <a href="<?php echo htmlspecialchars($sampleData['OutputImagePath']); ?>" class="image-popup" title="出力例の拡大表示">
                                            <img src="<?php echo htmlspecialchars($sampleData['OutputImagePath']); ?>" alt="出力例" class="img-fluid rounded shadow-sm hover-zoom">
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="no-image-placeholder flex-grow-1 d-flex align-items-center justify-content-center">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-image fa-3x mb-3"></i>
                                            <p>出力例の画像はありません</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="example-description mt-auto">
                                    <p>AIは上記のような結果を生成します。</p>
                                    <?php if (!empty($sampleData['OutputDescription'])): ?>
                                        <div class="alert alert-light">
                                            <?php echo nl2br(htmlspecialchars($sampleData['OutputDescription'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 対応AIサービスセクション -->
            <div class="detail-section">
                <div class="detail-section-title">対応AIサービス</div>
                <div class="ai-services">
                    <?php
                    // 利用可能なAIサービスとそのロゴ
                    $aiServices = [
                        ['name' => 'Google Gemini', 'logo' => 'gemini.png'],
                        ['name' => 'GPT-4', 'logo' => 'gpt4-logo.png'],
                        ['name' => 'Perplexity', 'logo' => 'perplexity-logo.png']
                    ];
                    
                    foreach ($aiServices as $service) {
                        $logoPath = getImagePath($service['logo']);
                        echo '<div class="ai-service-card">';
                        
                        if ($logoPath) {
                            echo '<img src="' . htmlspecialchars($logoPath) . '" alt="' . htmlspecialchars($service['name']) . '" class="ai-service-logo">';
                        }
                        
                        echo '<p>' . htmlspecialchars($service['name']) . 'で使用</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            
            <!-- 出力テキスト例セクション -->
            <div class="detail-section">
                <div class="detail-section-title">出力テキスト例</div>
                <div class="detail-how-to-use">
                    <?php
                    // サンプルのタイトルに基づいて適切な出力例を表示
                    $outputExample = '';
                    if (!empty($formData)) {
                        $title = isset($formData['Title']) ? $formData['Title'] : '';
                        
                        if (strpos($title, '料理') !== false || strpos($title, 'レシピ') !== false) {
                            $outputExample = '<h4>カレーライスのレシピ</h4>
                            <p><strong>材料（4人分）:</strong></p>
                            <ul>
                                <li>肉類: 豚肉または牛肉 300g</li>
                                <li>野菜: にんじん 1本、じゃがいも 2個、玉ねぎ 1個</li>
                                <li>調味料: カレールー 1箱、塩・こしょう 少々、サラダ油 大さじ1</li>
                                <li>その他: 白米 3合</li>
                            </ul>
                            <p><strong>作り方:</strong></p>
                            <ol>
                                <li>野菜と肉を一口大に切ります</li>
                                <li>鍋に油を熱し、肉を炒めます</li>
                                <li>肉の色が変わったら野菜を加えて炒めます</li>
                                <li>水を加えて20分ほど煮込みます</li>
                                <li>火を止め、カレールーを溶かし入れます</li>
                                <li>再び弱火で5分ほど煮込んだら完成です</li>
                            </ol>';
                        } elseif (strpos($title, 'Excel') !== false || strpos($title, '集金') !== false) {
                            $outputExample = '<h4>シンプルな会費管理表</h4>
                            <p><strong>列構成:</strong></p>
                            <ul>
                                <li>A列: 番号</li>
                                <li>B列: 氏名</li>
                                <li>C列: 会費区分</li>
                                <li>D列: 金額</li>
                                <li>E列: 納入日</li>
                                <li>F列: 備考</li>
                            </ul>
                            <p><strong>機能:</strong></p>
                            <ul>
                                <li>合計金額の自動計算</li>
                                <li>納入状況の色分け表示</li>
                                <li>会費区分ごとの集計</li>
                            </ul>';
                        } else {
                            $outputExample = '<p>このAIプロンプトを使用すると、上記のような出力が得られます。実際の出力は入力内容によって異なります。</p>';
                        }
                    }
                    
                    echo $outputExample;
                    ?>
                </div>
            </div>
            
            <!-- 関連サンプルセクション -->
            <?php if (!empty($relatedSamples)): ?>
            <div class="related-samples">
                <div class="detail-section-title">関連サンプル</div>
                <div class="related-samples-list">
                    <?php foreach ($relatedSamples as $sample): ?>
                        <a href="View.php?id=<?php echo $sample['id']; ?>" class="related-sample-card">
                            <div class="related-sample-title"><?php echo htmlspecialchars($sample['Title']); ?></div>
                            <div class="related-sample-ai"><?php echo htmlspecialchars($sample['AiName']); ?></div>
                            <div class="related-sample-product"><?php echo htmlspecialchars($sample['Product']); ?></div>
                            <div class="related-sample-date">更新日: <?php echo formatDate($sample['updated_at']); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php
                // 複数画像のサポート（将来的な拡張用）
                // 例: InputImagePath に複数のパスがカンマ区切りで格納されている場合
                if (!empty($sampleData['InputImagePath'])) {
                    $inputImages = explode(',', $sampleData['InputImagePath']);
                    
                    if (count($inputImages) > 1): ?>
                        <div class="image-gallery mb-3">
                            <div class="row">
                                <?php foreach ($inputImages as $index => $imagePath): ?>
                                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . trim($imagePath))): ?>
                                        <div class="col-6 col-md-4 mb-2">
                                            <a href="<?php echo htmlspecialchars(trim($imagePath)); ?>" class="image-popup" title="入力例 <?php echo $index + 1; ?>">
                                                <img src="<?php echo htmlspecialchars(trim($imagePath)); ?>" alt="入力例 <?php echo $index + 1; ?>" class="img-fluid rounded shadow-sm hover-zoom" loading="lazy">
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- 単一画像の表示（既存のコード） -->
                    <?php endif;
                }
            ?>
            <?php if (!empty($sampleData['InputImagePath']) && !empty($sampleData['OutputImagePath']) &&
                file_exists($_SERVER['DOCUMENT_ROOT'] . $sampleData['InputImagePath']) && 
                file_exists($_SERVER['DOCUMENT_ROOT'] . $sampleData['OutputImagePath'])): 
            ?>
            <div class="container mt-4">
                <h4>入力/出力比較</h4>
                <div class="image-comparison-container">
                    <div class="image-comparison">
                        <div class="before-image">
                            <img src="<?php echo htmlspecialchars($sampleData['InputImagePath']); ?>" alt="入力例" loading="lazy">
                            <span class="label">入力</span>
                        </div>
                        <div class="after-image">
                            <img src="<?php echo htmlspecialchars($sampleData['OutputImagePath']); ?>" alt="出力例" loading="lazy">
                            <span class="label">出力</span>
                        </div>
                        <div class="slider-handle">
                            <div class="handle-line"></div>
                            <div class="handle-circle">
                                <i class="fas fa-chevron-left"></i>
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    <style>
        .image-comparison-container {
            margin: 20px 0;
        }
        .image-comparison {
            position: relative;
            width: 100%;
            height: 400px;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .before-image, .after-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .before-image img, .after-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .after-image {
            width: 50%;
            overflow: hidden;
        }
        .slider-handle {
            position: absolute;
            top: 0;
            left: 50%;
            height: 100%;
            width: 40px;
            margin-left: -20px;
            cursor: ew-resize;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .handle-line {
            position: absolute;
            top: 0;
            left: 50%;
            width: 2px;
            height: 100%;
            background-color: white;
            transform: translateX(-50%);
        }
        .handle-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            z-index: 1;
        }
        .handle-circle i {
            color: #333;
            font-size: 12px;
        }
        .handle-circle i:first-child {
            margin-right: 2px;
        }
        .label {
            position: absolute;
            top: 10px;
            padding: 5px 10px;
            background-color: rgba(0,0,0,0.5);
            color: white;
            font-size: 12px;
            border-radius: 4px;
        }
        .before-image .label {
            left: 10px;
        }
        .after-image .label {
            right: 10px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.querySelector('.slider-handle');
            const beforeImage = document.querySelector('.before-image');
            const afterImage = document.querySelector('.after-image');
            
            let isActive = false;
            
            // PC用イベント
            slider.addEventListener('mousedown', () => {
                isActive = true;
            });
            
            document.addEventListener('mouseup', () => {
                isActive = false;
            });
            
            document.addEventListener('mousemove', (e) => {
                if (!isActive) return;
                handleSlide(e.clientX);
            });
            
            // モバイル用イベント
            slider.addEventListener('touchstart', () => {
                isActive = true;
            });
            
            document.addEventListener('touchend', () => {
                isActive = false;
            });
            
            document.addEventListener('touchmove', (e) => {
                if (!isActive) return;
                handleSlide(e.touches[0].clientX);
            });
            
            function handleSlide(clientX) {
                const container = document.querySelector('.image-comparison');
                const rect = container.getBoundingClientRect();
                const position = (clientX - rect.left) / rect.width;
                
                if (position < 0) return;
                if (position > 1) return;
                
                afterImage.style.width = `${position * 100}%`;
                slider.style.left = `${position * 100}%`;
            }
        });
        </script>
    <?php endif; ?>

            <!-- アクションボタン -->
            <div class="navigation-buttons">
                <div>
                    <a href="AISampleList_with_advanced.php" class="btn btn-secondary">サンプル一覧に戻る</a>
                    <?php if (!empty($formData['AiUrl'])): ?>
                        <a href="<?php echo htmlspecialchars($formData['AiUrl']); ?>" target="_blank" class="btn">AIを試す</a>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="prompt-history.php?id=<?php echo $sampleId; ?>" class="btn btn-outline">プロンプト履歴を見る</a>
                    <a href="Edit.php?edit=<?php echo $sampleId; ?>" class="btn">編集する</a>
                </div>
            </div>
        </div>
    <?php elseif ($mode == "error"): ?>
        <div class="container">
            <p>エラーが発生しました。サンプル一覧に戻ってください。</p>
            <a href="AISampleList_with_advanced.php" class="btn">サンプル一覧に戻る</a>
        </div>
    <?php endif; ?>
</body>
</html>