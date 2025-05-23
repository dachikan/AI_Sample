<?php
// データベース接続情報
$servername = "mysql213.phy.lolipop.lan";
$username = "LAA1337491";
$password = "kami2004";
$dbname = "LAA1337491-nsk";

// 初期化
$error = "";
$success = "";
$sampleId = 0;
$sampleData = [];
$versions = [];

// サンプルIDが指定されているか確認
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $sampleId = $_GET['id'];
    
    try {
        // データベース接続
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // テーブルが存在するか確認し、なければ作成
        $conn->exec("CREATE TABLE IF NOT EXISTS PromptVersions (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            sample_id INT(11) NOT NULL,
            prompt_text TEXT NOT NULL,
            version_number INT(11) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by VARCHAR(255),
            notes TEXT,
            FOREIGN KEY (sample_id) REFERENCES AISampleInfo(id) ON DELETE CASCADE
        )");
        
        // サンプル情報を取得
        $stmt = $conn->prepare("SELECT * FROM AISampleInfo WHERE id = :id");
        $stmt->bindParam(':id', $sampleId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sampleData = $row;
            
            // バージョン履歴を取得
            $stmt = $conn->prepare("SELECT * FROM PromptVersions WHERE sample_id = :sample_id ORDER BY version_number DESC");
            $stmt->bindParam(':sample_id', $sampleId, PDO::PARAM_INT);
            $stmt->execute();
            
            $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 現在のプロンプトをバージョン0として追加（表示用）
            array_unshift($versions, [
                'id' => 0,
                'sample_id' => $sampleId,
                'prompt_text' => $sampleData['Prompt'],
                'version_number' => 0,
                'created_at' => $sampleData['updated_at'] ?? $sampleData['created_at'],
                'created_by' => $sampleData['UserId'],
                'notes' => '現在のバージョン'
            ]);
        } else {
            $error = "指定されたIDのデータが見つかりません。";
        }
        
        // 復元処理
        if (isset($_POST['restore']) && isset($_POST['version_id']) && is_numeric($_POST['version_id'])) {
            $versionId = $_POST['version_id'];
            
            // バージョン情報を取得
            $stmt = $conn->prepare("SELECT prompt_text FROM PromptVersions WHERE id = :id AND sample_id = :sample_id");
            $stmt->bindParam(':id', $versionId, PDO::PARAM_INT);
            $stmt->bindParam(':sample_id', $sampleId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($versionRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // 現在のプロンプトをバージョン履歴に保存
                $stmt = $conn->prepare("SELECT MAX(version_number) as max_version FROM PromptVersions WHERE sample_id = :sample_id");
                $stmt->bindParam(':sample_id', $sampleId, PDO::PARAM_INT);
                $stmt->execute();
                $maxVersion = $stmt->fetch(PDO::FETCH_ASSOC)['max_version'] ?? 0;
                $newVersion = $maxVersion + 1;
                
                $stmt = $conn->prepare("INSERT INTO PromptVersions (sample_id, prompt_text, version_number, created_by, notes) VALUES (:sample_id, :prompt_text, :version_number, :created_by, :notes)");
                $stmt->bindParam(':sample_id', $sampleId, PDO::PARAM_INT);
                $stmt->bindParam(':prompt_text', $sampleData['Prompt'], PDO::PARAM_STR);
                $stmt->bindParam(':version_number', $newVersion, PDO::PARAM_INT);
                $stmt->bindParam(':created_by', $sampleData['UserId'], PDO::PARAM_STR);
                $notes = "バージョン復元前の自動保存";
                $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
                $stmt->execute();
                
                // 選択されたバージョンを現在のプロンプトに復元
                $stmt = $conn->prepare("UPDATE AISampleInfo SET Prompt = :prompt, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
                $stmt->bindParam(':prompt', $versionRow['prompt_text'], PDO::PARAM_STR);
                $stmt->bindParam(':id', $sampleId, PDO::PARAM_INT);
                $stmt->execute();
                
                $success = "プロンプトを過去のバージョンに復元しました。";
                
                // ページをリロードして最新情報を表示
                header("Location: prompt-history.php?id=$sampleId&restored=1");
                exit;
            } else {
                $error = "指定されたバージョンが見つかりません。";
            }
        }
        
        // 新しいバージョンの保存処理
        if (isset($_POST['save_version']) && isset($_POST['notes'])) {
            // 現在のプロンプトをバージョン履歴に保存
            $stmt = $conn->prepare("SELECT MAX(version_number) as max_version FROM PromptVersions WHERE sample_id = :sample_id");
            $stmt->bindParam(':sample_id', $sampleId, PDO::PARAM_INT);
            $stmt->execute();
            $maxVersion = $stmt->fetch(PDO::FETCH_ASSOC)['max_version'] ?? 0;
            $newVersion = $maxVersion + 1;
            
            $stmt = $conn->prepare("INSERT INTO PromptVersions (sample_id, prompt_text, version_number, created_by, notes) VALUES (:sample_id, :prompt_text, :version_number, :created_by, :notes)");
            $stmt->bindParam(':sample_id', $sampleId, PDO::PARAM_INT);
            $stmt->bindParam(':prompt_text', $sampleData['Prompt'], PDO::PARAM_STR);
            $stmt->bindParam(':version_number', $newVersion, PDO::PARAM_INT);
            $stmt->bindParam(':created_by', $sampleData['UserId'], PDO::PARAM_STR);
            $stmt->bindParam(':notes', $_POST['notes'], PDO::PARAM_STR);
            $stmt->execute();
            
            $success = "プロンプトの新しいバージョンを保存しました。";
            
            // ページをリロードして最新情報を表示
            header("Location: prompt-history.php?id=$sampleId&saved=1");
            exit;
        }
        
    } catch(PDOException $e) {
        $error = "データベースエラー: " . $e->getMessage();
    }
    
    $conn = null;
} else {
    $error = "IDが指定されていません。";
}

// 復元成功メッセージの表示
if (isset($_GET['restored']) && $_GET['restored'] == 1) {
    $success = "プロンプトを過去のバージョンに復元しました。";
}

// 保存成功メッセージの表示
if (isset($_GET['saved']) && $_GET['saved'] == 1) {
    $success = "プロンプトの新しいバージョンを保存しました。";
}

// AIの種類を判定
$isClaudeAI = false;
$isChatGPTAI = false;
if (!empty($sampleData) && isset($sampleData['AiName'])) {
    $aiNameLower = strtolower($sampleData['AiName']);
    $isClaudeAI = ($aiNameLower === 'claude');
    $isChatGPTAI = (strpos($aiNameLower, 'chatgpt') !== false || strpos($aiNameLower, 'gpt') !== false);
}

// テキストの差分を表示する関数
function showDiff($old, $new) {
    // 単純な差分表示（実際のアプリケーションではより高度な差分表示ライブラリを使用することをお勧めします）
    $oldLines = explode("\n", $old);
    $newLines = explode("\n", $new);
    
    $result = '';
    $diff = [];
    
    // 最大行数を取得
    $maxLines = max(count($oldLines), count($newLines));
    
    for ($i = 0; $i < $maxLines; $i++) {
        $oldLine = isset($oldLines[$i]) ? $oldLines[$i] : '';
        $newLine = isset($newLines[$i]) ? $newLines[$i] : '';
        
        if ($oldLine !== $newLine) {
            if ($oldLine && $newLine) {
                $result .= '<div class="diff-line diff-changed"><del>' . htmlspecialchars($oldLine) . '</del><ins>' . htmlspecialchars($newLine) . '</ins></div>';
            } elseif ($oldLine) {
                $result .= '<div class="diff-line diff-removed"><del>' . htmlspecialchars($oldLine) . '</del></div>';
            } elseif ($newLine) {
                $result .= '<div class="diff-line diff-added"><ins>' . htmlspecialchars($newLine) . '</ins></div>';
            }
        } else {
            $result .= '<div class="diff-line">' . htmlspecialchars($oldLine) . '</div>';
        }
    }
    
    return $result;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($sampleData) ? htmlspecialchars($sampleData['Title']) : "プロンプト履歴"; ?> - プロンプト改良履歴</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: <?php 
                if ($isClaudeAI) echo '#f9f5ff';
                else if ($isChatGPTAI) echo '#f2f9ff';
                else echo '#f9f9f9';
            ?>;
        }
        header {
            margin-bottom: 30px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid <?php 
                if ($isClaudeAI) echo '#9b59b6';
                else if ($isChatGPTAI) echo '#10a37f';
                else echo '#3498db';
            ?>;
            padding-bottom: 10px;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            <?php if ($isClaudeAI): ?>
            border-top: 5px solid #9b59b6;
            <?php elseif ($isChatGPTAI): ?>
            border-top: 5px solid #10a37f;
            <?php endif; ?>
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
            background-color: <?php 
                if ($isClaudeAI) echo '#9b59b6';
                else if ($isChatGPTAI) echo '#10a37f';
                else echo '#3498db';
            ?>;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            margin-right: 10px;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: <?php 
                if ($isClaudeAI) echo '#8e44ad';
                else if ($isChatGPTAI) echo '#0d8a6c';
                else echo '#2980b9';
            ?>;
        }
        .btn-back {
            background-color: #95a5a6;
        }
        .btn-back:hover {
            background-color: #7f8c8d;
        }
        .btn-restore {
            background-color: #e67e22;
        }
        .btn-restore:hover {
            background-color: #d35400;
        }
        .btn-save {
            background-color: #27ae60;
        }
        .btn-save:hover {
            background-color: #2ecc71;
        }
        .version-list {
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        .version-item {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            transition: background-color 0.2s;
        }
        .version-item:last-child {
            border-bottom: none;
        }
        .version-item:hover {
            background-color: #f8f9fa;
        }
        .version-item.current {
            background-color: <?php 
                if ($isClaudeAI) echo '#f5eeff';
                else if ($isChatGPTAI) echo '#f0f7f5';
                else echo '#f0f7fb';
            ?>;
        }
        .version-number {
            font-weight: bold;
            margin-right: 15px;
            min-width: 80px;
        }
        .version-date {
            color: #666;
            margin-right: 15px;
            min-width: 180px;
        }
        .version-notes {
            flex-grow: 1;
            margin-right: 15px;
        }
        .version-actions {
            display: flex;
            gap: 10px;
        }
        .version-content {
            margin-top: 10px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            white-space: pre-wrap;
            display: none;
            width: 100%;
        }
        .version-item.expanded .version-content {
            display: block;
        }
        .diff-view {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .diff-line {
            padding: 2px 0;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .diff-added {
            background-color: #e6ffed;
        }
        .diff-removed {
            background-color: #ffeef0;
        }
        .diff-changed {
            background-color: #fff5b1;
        }
        del {
            background-color: #ffeef0;
            text-decoration: line-through;
            color: #b31d28;
        }
        ins {
            background-color: #e6ffed;
            text-decoration: none;
            color: #22863a;
        }
        .compare-container {
            margin-top: 20px;
            display: none;
        }
        .compare-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .compare-title {
            font-weight: bold;
        }
        .compare-close {
            cursor: pointer;
            color: #666;
        }
        .new-version-form {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
        }
        .meta-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-size: 0.9em;
            color: #666;
        }
        @media (max-width: 768px) {
            .version-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .version-number, .version-date, .version-notes {
                margin-bottom: 10px;
                min-width: auto;
                width: 100%;
            }
            .version-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
    <script>
        // ページ読み込み時の処理
        document.addEventListener('DOMContentLoaded', function() {
            // バージョン項目のクリックイベント
            document.querySelectorAll('.version-toggle').forEach(function(button) {
                button.addEventListener('click', function() {
                    const versionItem = this.closest('.version-item');
                    versionItem.classList.toggle('expanded');
                    
                    // ボタンのテキストを変更
                    if (versionItem.classList.contains('expanded')) {
                        this.textContent = '閉じる';
                    } else {
                        this.textContent = '表示';
                    }
                });
            });
            
            // 比較ボタンのクリックイベント
            document.querySelectorAll('.btn-compare').forEach(function(button) {
                button.addEventListener('click', function() {
                    const versionId = this.getAttribute('data-version');
                    const compareContainer = document.getElementById('compare-container');
                    const compareContent = document.getElementById('compare-content');
                    
                    // 現在のバージョンと選択したバージョンのプロンプトを取得
                    const currentPrompt = document.getElementById('current-prompt').textContent;
                    const selectedPrompt = document.getElementById('version-content-' + versionId).textContent;
                    
                    // 差分を表示
                    compareContent.innerHTML = showDiff(selectedPrompt, currentPrompt);
                    
                    // 比較コンテナを表示
                    compareContainer.style.display = 'block';
                    
                    // スクロール
                    compareContainer.scrollIntoView({ behavior: 'smooth' });
                });
            });
            
            // 比較を閉じるボタンのクリックイベント
            document.getElementById('compare-close').addEventListener('click', function() {
                document.getElementById('compare-container').style.display = 'none';
            });
        });
        
        // テキストの差分を表示する関数（JavaScript版）
        function showDiff(oldText, newText) {
            const oldLines = oldText.split('\n');
            const newLines = newText.split('\n');
            
            let result = '';
            
            // 最大行数を取得
            const maxLines = Math.max(oldLines.length, newLines.length);
            
            for (let i = 0; i < maxLines; i++) {
                const oldLine = i < oldLines.length ? oldLines[i] : '';
                const newLine = i < newLines.length ? newLines[i] : '';
                
                if (oldLine !== newLine) {
                    if (oldLine && newLine) {
                        result += `<div class="diff-line diff-changed"><del>${escapeHtml(oldLine)}</del><ins>${escapeHtml(newLine)}</ins></div>`;
                    } else if (oldLine) {
                        result += `<div class="diff-line diff-removed"><del>${escapeHtml(oldLine)}</del></div>`;
                    } else if (newLine) {
                        result += `<div class="diff-line diff-added"><ins>${escapeHtml(newLine)}</ins></div>`;
                    }
                } else {
                    result += `<div class="diff-line">${escapeHtml(oldLine)}</div>`;
                }
            }
            
            return result;
        }
        
        // HTMLエスケープ関数
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</head>
<body>
    <header>
        <h1>プロンプト改良履歴</h1>
    </header>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
        <a href="view.php?id=<?php echo $sampleId; ?>" class="btn btn-back">詳細に戻る</a>
    <?php elseif (!empty($sampleData)): ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="container">
            <h2><?php echo htmlspecialchars($sampleData['Title']); ?> のプロンプト履歴</h2>
            
            <div class="meta-info">
                <p><strong>AI名:</strong> <?php echo htmlspecialchars($sampleData['AiName']); ?></p>
                <p><strong>投稿者:</strong> <?php echo htmlspecialchars($sampleData['UserId']); ?></p>
                <p><strong>登録日時:</strong> <?php echo htmlspecialchars($sampleData['created_at']); ?></p>
            </div>
            
            <div class="new-version-form">
                <h3>現在のプロンプトをバージョンとして保存</h3>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="notes">変更内容のメモ:</label>
                        <textarea id="notes" name="notes" required placeholder="このバージョンでの変更点や改良点を記入してください"></textarea>
                    </div>
                    <button type="submit" name="save_version" class="btn btn-save">現在のプロンプトをバージョンとして保存</button>
                </form>
            </div>
            
            <h3>バージョン履歴</h3>
            
            <?php if (empty($versions)): ?>
                <p>まだバージョン履歴がありません。</p>
            <?php else: ?>
                <div class="version-list">
                    <?php foreach ($versions as $index => $version): ?>
                        <div class="version-item <?php echo $version['version_number'] === 0 ? 'current' : ''; ?>">
                            <div class="version-number">
                                <?php if ($version['version_number'] === 0): ?>
                                    現在のバージョン
                                <?php else: ?>
                                    バージョン <?php echo $version['version_number']; ?>
                                <?php endif; ?>
                            </div>
                            <div class="version-date">
                                <?php echo date('Y-m-d H:i:s', strtotime($version['created_at'])); ?>
                            </div>
                            <div class="version-notes">
                                <?php echo htmlspecialchars($version['notes']); ?>
                            </div>
                            <div class="version-actions">
                                <button class="btn version-toggle">表示</button>
                                
                                <?php if ($version['version_number'] !== 0): ?>
                                    <button class="btn btn-compare" data-version="<?php echo $version['id']; ?>">現在と比較</button>
                                    
                                    <form method="post" action="" style="display: inline;">
                                        <input type="hidden" name="version_id" value="<?php echo $version['id']; ?>">
                                        <button type="submit" name="restore" class="btn btn-restore" onclick="return confirm('このバージョンに復元してもよろしいですか？現在のプロンプトは履歴に保存されます。');">このバージョンに復元</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="version-content" id="version-content-<?php echo $version['id']; ?>">
                                <?php echo htmlspecialchars($version['prompt_text']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- 現在のプロンプトを非表示で保持（比較用） -->
                <div id="current-prompt" style="display: none;"><?php echo htmlspecialchars($sampleData['Prompt']); ?></div>
                
                <!-- 比較表示エリア -->
                <div id="compare-container" class="compare-container">
                    <div class="compare-header">
                        <div class="compare-title">プロンプトの比較（選択したバージョン → 現在のバージョン）</div>
                        <div id="compare-close" class="compare-close">✕ 閉じる</div>
                    </div>
                    <div id="compare-content" class="diff-view"></div>
                </div>
            <?php endif; ?>
            
            <div>
                <a href="view.php?id=<?php echo $sampleId; ?>" class="btn btn-back">詳細に戻る</a>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>