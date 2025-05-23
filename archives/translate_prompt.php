<?php
// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DeepL API設定
$deepl_api_key = ""; // ここにDeepL APIキーを入力
$deepl_api_url = "https://api-free.deepl.com/v2/translate"; // 無料版APIのURL

// 初期化
$original_text = "";
$translated_text = "";
$error = "";
$success = "";

// POSTリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['translate'])) {
    $original_text = $_POST['text'] ?? '';
    $target_lang = $_POST['target_lang'] ?? 'JA'; // デフォルトは日本語
    
    if (empty($original_text)) {
        $error = "翻訳するテキストを入力してください。";
    } elseif (empty($deepl_api_key)) {
        $error = "DeepL APIキーが設定されていません。";
    } else {
        // DeepL APIを呼び出す
        $result = translateText($original_text, $target_lang);
        
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            $translated_text = $result['text'];
            $success = "翻訳が完了しました。";
        }
    }
}

/**
 * DeepL APIを使用してテキストを翻訳する
 * 
 * @param string $text 翻訳するテキスト
 * @param string $target_lang 翻訳先の言語コード（JA: 日本語, EN: 英語）
 * @return array 翻訳結果または失敗時のエラー
 */
function translateText($text, $target_lang) {
    global $deepl_api_key, $deepl_api_url;
    
    $params = [
        'auth_key' => $deepl_api_key,
        'text' => $text,
        'target_lang' => $target_lang
    ];
    
    // 言語を自動検出
    if ($target_lang === 'JA') {
        $params['source_lang'] = 'EN';
    } else {
        $params['source_lang'] = 'JA';
    }
    
    // cURLを使用してAPIリクエストを送信
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $deepl_api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    if ($http_code !== 200) {
        return ['error' => "API呼び出しエラー: HTTP $http_code"];
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['translations']) && !empty($result['translations'])) {
        return ['text' => $result['translations'][0]['text']];
    } else {
        return ['error' => "翻訳結果の解析に失敗しました。"];
    }
}

// Google翻訳を使用する代替手段（APIキーが不要）
function getGoogleTranslateUrl($text, $target_lang) {
    $source_lang = $target_lang === 'ja' ? 'en' : 'ja';
    return "https://translate.google.com/?sl={$source_lang}&tl={$target_lang}&text=" . urlencode($text) . "&op=translate";
}

// DeepL翻訳を使用する代替手段（APIキーが不要）
function getDeepLTranslateUrl($text, $target_lang) {
    $source_lang = $target_lang === 'ja' ? 'en' : 'ja';
    return "https://www.deepl.com/translator#{$source_lang}/{$target_lang}/" . urlencode($text);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロンプト翻訳ツール</title>
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
        
        .error, .success {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
            min-height: 150px;
            resize: vertical;
        }
        
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--secondary-color);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
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
        
        .btn-success {
            background-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #27ae60;
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
        
        .translation-result {
            background-color: var(--light-color);
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
            overflow-x: auto;
            margin-bottom: 15px;
        }
        
        .copy-btn {
            background-color: var(--light-color);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .copy-btn:hover {
            background-color: #e9ecef;
        }
        
        .alternative-services {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--text-muted);
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
            
            .btn {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">
            <h1>プロンプト翻訳ツール</h1>
            <div class="header-description">英語のプロンプトを日本語に、日本語のプロンプトを英語に翻訳します</div>
        </div>
        <div class="header-actions">
            <a href="AISample_main.php" class="btn">メイン画面に戻る</a>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="container">
        <form method="post" action="">
            <div class="form-group">
                <label for="text">翻訳するテキスト</label>
                <textarea id="text" name="text" required><?php echo htmlspecialchars($original_text); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="target_lang">翻訳先の言語</label>
                <select id="target_lang" name="target_lang">
                    <option value="JA">英語 → 日本語</option>
                    <option value="EN">日本語 → 英語</option>
                </select>
            </div>
            
            <?php if (empty($deepl_api_key)): ?>
                <div class="error">
                    <p>DeepL APIキーが設定されていないため、自動翻訳機能は使用できません。</p>
                    <p>以下の外部翻訳サービスを利用してください：</p>
                </div>
                
                <div class="form-group">
                    <button type="button" class="btn btn-primary" onclick="openGoogleTranslate()">Google翻訳で開く</button>
                    <button type="button" class="btn btn-primary" onclick="openDeepLTranslate()">DeepLで開く</button>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <button type="submit" name="translate" class="btn btn-primary">翻訳する</button>
                </div>
            <?php endif; ?>
        </form>
        
        <?php if (!empty($translated_text)): ?>
            <div style="margin-top: 30px;">
                <h3>翻訳結果</h3>
                <div class="translation-result" id="translation-result"><?php echo htmlspecialchars($translated_text); ?></div>
                <button class="copy-btn" onclick="copyToClipboard('translation-result')">翻訳結果をコピー</button>
            </div>
        <?php endif; ?>
        
        <div class="alternative-services">
            <h3>外部翻訳サービス</h3>
            <p>より高品質な翻訳が必要な場合は、以下の外部サービスを利用することもできます：</p>
            <div>
                <button type="button" class="btn" onclick="openGoogleTranslate()">Google翻訳で開く</button>
                <button type="button" class="btn" onclick="openDeepLTranslate()">DeepLで開く</button>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h3>使い方</h3>
        <ol>
            <li>翻訳したいプロンプトをテキストエリアに貼り付けます。</li>
            <li>翻訳先の言語を選択します。</li>
            <li>「翻訳する」ボタンをクリックします。</li>
            <li>翻訳結果が表示されたら、「翻訳結果をコピー」ボタンでコピーできます。</li>
        </ol>
        <p><strong>注意：</strong> 自動翻訳は完璧ではありません。特に専門用語や複雑な表現は、翻訳後に内容を確認し、必要に応じて修正することをお勧めします。</p>
    </div>
    
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
    
    function openGoogleTranslate() {
        const text = document.getElementById('text').value;
        if (!text) {
            alert('翻訳するテキストを入力してください。');
            return;
        }
        
        const targetLang = document.getElementById('target_lang').value.toLowerCase();
        const sourceLang = targetLang === 'ja' ? 'en' : 'ja';
        const url = `https://translate.google.com/?sl=${sourceLang}&tl=${targetLang}&text=${encodeURIComponent(text)}&op=translate`;
        
        window.open(url, '_blank');
    }
    
    function openDeepLTranslate() {
        const text = document.getElementById('text').value;
        if (!text) {
            alert('翻訳するテキストを入力してください。');
            return;
        }
        
        const targetLang = document.getElementById('target_lang').value.toLowerCase();
        const sourceLang = targetLang === 'ja' ? 'en' : 'ja';
        const url = `https://www.deepl.com/translator#${sourceLang}/${targetLang}/${encodeURIComponent(text)}`;
        
        window.open(url, '_blank');
    }
    </script>
</body>
</html>

