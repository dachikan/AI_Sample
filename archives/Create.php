<?php
// 既存のコードの中で、データ更新処理の部分を修正

// 例えば、以下のような更新処理があるとします
if (isset($_POST['update']) && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = $_POST['id'];
    
    // 既存のデータを取得
    $stmt = $conn->prepare("SELECT * FROM AISampleInfo WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $existingData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // フォームデータを取得
    $userId = $_POST['userId'];
    $title = $_POST['title'];
    $aiName = $_POST['aiName'];
    $aiUrl = $_POST['aiUrl'];
    $product = $_POST['product'];
    $prompt = $_POST['prompt'];
    $howToUse = $_POST['howToUse'] ?? '';
    $noteUrl = $_POST['noteUrl'] ?? '';
    
    try {
        // プロンプトが変更されている場合、履歴に保存
        if ($existingData['Prompt'] !== $prompt) {
            // PromptVersionsテーブルが存在するか確認し、なければ作成
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
            
            // 最新のバージョン番号を取得
            $stmt = $conn->prepare("SELECT MAX(version_number) as max_version FROM PromptVersions WHERE sample_id = :sample_id");
            $stmt->bindParam(':sample_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $maxVersion = $stmt->fetch(PDO::FETCH_ASSOC)['max_version'] ?? 0;
            $newVersion = $maxVersion + 1;
            
            // 古いプロンプトをバージョン履歴に保存
            $stmt = $conn->prepare("INSERT INTO PromptVersions (sample_id, prompt_text, version_number, created_by, notes) VALUES (:sample_id, :prompt_text, :version_number, :created_by, :notes)");
            $stmt->bindParam(':sample_id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':prompt_text', $existingData['Prompt'], PDO::PARAM_STR);
            $stmt->bindParam(':version_number', $newVersion, PDO::PARAM_INT);
            $stmt->bindParam(':created_by', $existingData['UserId'], PDO::PARAM_STR);
            $notes = "編集時の自動保存";
            $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
            $stmt->execute();
        }
        
        // 通常の更新処理を続行
        $stmt = $conn->prepare("UPDATE AISampleInfo SET UserId = :userId, Title = :title, AiName = :aiName, AiUrl = :aiUrl, Product = :product, Prompt = :prompt, HowToUse = :howToUse, noteUrl = :noteUrl, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':aiName', $aiName, PDO::PARAM_STR);
        $stmt->bindParam(':aiUrl', $aiUrl, PDO::PARAM_STR);
        $stmt->bindParam(':product', $product, PDO::PARAM_STR);
        $stmt->bindParam(':prompt', $prompt, PDO::PARAM_STR);
        $stmt->bindParam(':howToUse', $howToUse, PDO::PARAM_STR);
        $stmt->bindParam(':noteUrl', $noteUrl, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $success = "データを更新しました。";
        
        // リダイレクト
        header("Location: Create.php?success=" . urlencode($success));
        exit;
    } catch(PDOException $e) {
        $error = "データ更新エラー: " . $e->getMessage();
    }
}

// 残りのCreate.phpのコードはそのまま...
?>