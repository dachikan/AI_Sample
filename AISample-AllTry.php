<?php
// 正しいパスでdb_connect.phpを読み込む
require_once 'config/db_connect.php';

// エラー表示を有効化（開発時のみ）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// AIタイプの取得
$aiTypes = getAITypes();

// カテゴリーの取得
$categories = getCategories();

// プロンプトテンプレートの取得
$templates = [];
$sql = "SELECT * FROM AIPromptTemplates ORDER BY name ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $templates[] = $row;
    }
}

// フォーム送信処理
$output = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedAiType = $_POST["ai_type"] ?? 1;
    $selectedCategory = $_POST["category"] ?? 1;
    $prompt = $_POST["prompt"] ?? "";
    $temperature = $_POST["temperature"] ?? 0.7;
    
    // ここで実際のAI APIを呼び出す処理を実装
    // 今回はサンプルレスポンスを返す
    $aiTypeName = "";
    foreach ($aiTypes as $type) {
        if ($type['id'] == $selectedAiType) {
            $aiTypeName = $type['name'];
            break;
        }
    }
    
    if (strpos($aiTypeName, 'テキスト') !== false) {
        $output = "AIからの出力を記入するTextarea";
    } elseif (strpos($aiTypeName, '画像') !== false) {
        $output = "AIからの出力を記入するTextarea";
    } else {
        $output = "AIからの出力を記入するTextarea";
    }
    
    // 試行結果を保存
    if (function_exists('saveTrialResult')) {
        saveTrialResult(null, $selectedAiType, $prompt, $output);
    }
}

// プロンプトカウントの取得
$promptCount = getPromptCount();

// カテゴリごとのプロンプト数を取得
$categoryPromptCounts = getPromptCountByCategory();

// AIタイプごとのプロンプト数を取得
$aiTypePromptCounts = getPromptCountByAIType();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIプロンプト試行ページ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            padding: 20px;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .category-item, .ai-type-item {
            padding: 10px 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .category-item:hover, .ai-type-item:hover {
            background-color: #f1f3f5;
        }
        .category-item.active, .ai-type-item.active {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .category-icon, .ai-type-icon {
            width: 24px;
            height: 24px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .category-count, .ai-type-count {
            margin-left: auto;
            background-color: #6c757d;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        .prompt-textarea {
            min-height: 200px;
            resize: vertical;
        }
        .output-textarea {
            min-height: 200px;
            background-color: #f8f9fa;
            resize: vertical;
        }
        .explanation-box {
            background-color: #e7f5ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .btn-copy {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
        .template-select {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center my-4">AIプロンプト試行ページ</h1>
        
        <div class="row">
            <!-- 左側のサイドバー -->
            <div class="col-md-3">
                <!-- プロンプトカテゴリ -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">プロンプトカテゴリ</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="category-item active" data-category-id="all">
                            <div class="category-icon"><i class="bi bi-grid"></i></div>
                            <span>すべて</span>
                            <span class="category-count"><?php echo $promptCount; ?></span>
                        </div>
                        <?php foreach ($categories as $category): ?>
                            <?php 
                                $count = 0;
                                foreach ($categoryPromptCounts as $catCount) {
                                    if ($catCount['id'] == $category['id']) {
                                        $count = $catCount['count'];
                                        break;
                                    }
                                }
                                
                                // カテゴリごとのアイコン
                                $icon = 'bi-folder';
                                switch ($category['name']) {
                                    case 'ビジネス':
                                        $icon = 'bi-briefcase';
                                        break;
                                    case 'クリエイティブ':
                                        $icon = 'bi-palette';
                                        break;
                                    case '技術':
                                        $icon = 'bi-code-square';
                                        break;
                                    case '教育':
                                        $icon = 'bi-book';
                                        break;
                                    case '日常生活':
                                        $icon = 'bi-house';
                                        break;
                                    case '画像生成':
                                        $icon = 'bi-image';
                                        break;
                                }
                            ?>
                            <div class="category-item" data-category-id="<?php echo $category['id']; ?>">
                                <div class="category-icon"><i class="bi <?php echo $icon; ?>"></i></div>
                                <span><?php echo htmlspecialchars($category['name']); ?></span>
                                <span class="category-count"><?php echo $count; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- AIサービスタイプ -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">AIサービスタイプ</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="ai-type-item active" data-ai-type-id="all">
                            <div class="ai-type-icon"><i class="bi bi-grid"></i></div>
                            <span>その他</span>
                            <span class="ai-type-count"><?php echo $promptCount; ?></span>
                        </div>
                        <?php foreach ($aiTypes as $aiType): ?>
                            <?php 
                                $count = 0;
                                foreach ($aiTypePromptCounts as $typeCount) {
                                    if ($typeCount['id'] == $aiType['id']) {
                                        $count = $typeCount['count'];
                                        break;
                                    }
                                }
                                
                                // AIタイプごとのアイコン
                                $icon = 'bi-robot';
                                switch ($aiType['name']) {
                                    case 'テキスト生成AI':
                                        $icon = 'bi-chat-text';
                                        break;
                                    case '画像生成AI':
                                        $icon = 'bi-image';
                                        break;
                                    case '音声・音楽生成AI':
                                        $icon = 'bi-music-note-beamed';
                                        break;
                                    case '動画生成AI':
                                        $icon = 'bi-film';
                                        break;
                                    case '日本語特化AI':
                                        $icon = 'bi-translate';
                                        break;
                                }
                            ?>
                            <div class="ai-type-item" data-ai-type-id="<?php echo $aiType['id']; ?>">
                                <div class="ai-type-icon"><i class="bi <?php echo $icon; ?>"></i></div>
                                <span><?php echo htmlspecialchars($aiType['name']); ?></span>
                                <span class="ai-type-count"><?php echo $count; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- 右側のメインコンテンツ -->
            <div class="col-md-9">
                <!-- 説明文 -->
                <div class="explanation-box">
                    <p class="mb-0">このページでは、様々なAIサービスで使用できるプロンプトを試すことができます。左側でカテゴリとAI種類を選択すると、試行結果を確認できます。プロンプトを選択または編集し、AIサービスで試してみましょう。</p>
                </div>
                
                <!-- プロンプト編集 -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">プロンプト編集</h5>
                    </div>
                    <div class="card-body">
                        <form id="promptForm" method="post" action="">
                            <input type="hidden" name="ai_type" id="ai_type_hidden" value="1">
                            <input type="hidden" name="category" id="category_hidden" value="1">
                            
                            <!-- テンプレート選択 -->
                            <div class="template-select">
                                <select class="form-select" id="template_select">
                                    <option value="">テンプレートを選択してください</option>
                                    <?php foreach ($templates as $template): ?>
                                        <option value="<?php echo $template['id']; ?>"><?php echo htmlspecialchars($template['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <ul class="nav nav-tabs" id="promptTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" 
                                            data-bs-target="#basic" type="button" role="tab" 
                                            aria-controls="basic" aria-selected="true">基本</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="advanced-tab" data-bs-toggle="tab" 
                                            data-bs-target="#advanced" type="button" role="tab" 
                                            aria-controls="advanced" aria-selected="false">詳細設定</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content mt-3" id="promptTabsContent">
                                <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                                    <div class="mb-3">
                                        <textarea class="form-control prompt-textarea" name="prompt" id="prompt" 
                                                  placeholder="AIに指示するプロンプトを入力してください..."></textarea>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="advanced" role="tabpanel" aria-labelledby="advanced-tab">
                                    <div class="mb-3">
                                        <textarea class="form-control prompt-textarea" name="prompt_advanced" id="prompt_advanced" 
                                                  placeholder="AIに指示するプロンプトを入力してください..."></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="temperature" class="form-label">温度 (Temperature): <span id="temp-value">0.7</span></label>
                                        <input type="range" class="form-range" name="temperature" id="temperature" 
                                               min="0" max="1" step="0.1" value="0.7">
                                        <small class="text-muted">低い値（0に近い）は予測可能な出力、高い値（1に近い）はより創造的な出力になります。</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-3">
                                <button type="submit" class="btn btn-primary" id="submitBtn">プロンプトを実行</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- AIサービスで使用する -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">AIサービスで使用する</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-primary" data-ai-service="chatgpt">ChatGPT</button>
                            <button class="btn btn-outline-primary" data-ai-service="claude">Claude</button>
                            <button class="btn btn-outline-primary" data-ai-service="gemini">Google Gemini</button>
                            <button class="btn btn-outline-primary" data-ai-service="copilot">Microsoft Copilot</button>
                        </div>
                    </div>
                </div>
                
                <!-- 出力結果 -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">AIからの出力結果</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary me-2" id="copyBtn">
                                <i class="bi bi-clipboard"></i> コピー
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="downloadBtn">
                                <i class="bi bi-download"></i> ダウンロード
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control output-textarea" id="output" readonly>AIからの出力を記入するTextarea</textarea>
                    </div>
                </div>
                
                <!-- 画像アップロード機能 -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">画像アップロード</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="imageUpload" class="form-label">画像をアップロード</label>
                            <input class="form-control" type="file" id="imageUpload" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pasteEnabled">
                                <label class="form-check-label" for="pasteEnabled">
                                    クリップボードからの貼り付けを有効にする
                                </label>
                            </div>
                        </div>
                        <div id="imagePreview" class="mt-3 d-none">
                            <h6>プレビュー:</h6>
                            <img id="previewImg" src="#" alt="プレビュー" class="img-fluid" style="max-height: 200px;">
                            <button class="btn btn-sm btn-danger mt-2" id="removeImage">削除</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 温度スライダーの値を表示
            const tempSlider = document.getElementById('temperature');
            const tempValue = document.getElementById('temp-value');
            
            if (tempSlider && tempValue) {
                tempSlider.addEventListener('input', function() {
                    tempValue.textContent = this.value;
                });
            }
            
            // 基本タブとアドバンスタブでテキストエリアを同期
            const promptBasic = document.getElementById('prompt');
            const promptAdvanced = document.getElementById('prompt_advanced');
            
            if (promptBasic && promptAdvanced) {
                promptBasic.addEventListener('input', function() {
                    promptAdvanced.value = this.value;
                });
                
                promptAdvanced.addEventListener('input', function() {
                    promptBasic.value = this.value;
                });
            }
            
            // カテゴリー選択
            const categoryItems = document.querySelectorAll('.category-item');
            categoryItems.forEach(item => {
                item.addEventListener('click', function() {
                    categoryItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    
                    const categoryId = this.getAttribute('data-category-id');
                    document.getElementById('category_hidden').value = categoryId === 'all' ? '' : categoryId;
                });
            });
            
            // AIタイプ選択
            const aiTypeItems = document.querySelectorAll('.ai-type-item');
            aiTypeItems.forEach(item => {
                item.addEventListener('click', function() {
                    aiTypeItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    
                    const aiTypeId = this.getAttribute('data-ai-type-id');
                    document.getElementById('ai_type_hidden').value = aiTypeId === 'all' ? '' : aiTypeId;
                });
            });
            
            // テンプレート選択
            const templateSelect = document.getElementById('template_select');
            if (templateSelect) {
                templateSelect.addEventListener('change', function() {
                    const templateId = this.value;
                    if (templateId) {
                        // テンプレートの内容を取得（実際はAjaxで取得するか、事前にJSオブジェクトとして用意）
                        fetch(`get-template.php?id=${templateId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.content) {
                                    promptBasic.value = data.content;
                                    promptAdvanced.value = data.content;
                                }
                            })
                            .catch(error => {
                                console.error('テンプレート取得エラー:', error);
                                // エラー時のフォールバック
                                const templates = {
                                    1: `以下の要件に基づいて、丁寧なビジネスメールを作成してください。

【目的】：{目的}
【相手】：{相手}
【伝えたい内容】：{内容}
【トーン】：{トーン}`,
                                    2: `以下の材料と条件で作れる料理のレシピを教えてください。

【材料】：{材料リスト}
【調理時間】：{時間}分以内
【難易度】：{難易度}
【好み】：{好み}`
                                };
                                
                                if (templates[templateId]) {
                                    promptBasic.value = templates[templateId];
                                    promptAdvanced.value = templates[templateId];
                                }
                            });
                    }
                });
            }
            
            // AIサービスボタン
            const aiServiceButtons = document.querySelectorAll('[data-ai-service]');
            aiServiceButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const service = this.getAttribute('data-ai-service');
                    const prompt = promptBasic.value;
                    
                    if (!prompt) {
                        alert('プロンプトを入力してください');
                        return;
                    }
                    
                    // サービスごとのURLを設定
                    let url = '';
                    switch (service) {
                        case 'chatgpt':
                            url = `https://chat.openai.com/share?prompt=${encodeURIComponent(prompt)}`;
                            break;
                        case 'claude':
                            url = `https://claude.ai/chat?prompt=${encodeURIComponent(prompt)}`;
                            break;
                        case 'gemini':
                            url = `https://gemini.google.com/app?prompt=${encodeURIComponent(prompt)}`;
                            break;
                        case 'copilot':
                            url = `https://copilot.microsoft.com/?prompt=${encodeURIComponent(prompt)}`;
                            break;
                    }
                    
                    if (url) {
                        window.open(url, '_blank');
                    }
                });
            });
            
            // コピーボタン
            const copyBtn = document.getElementById('copyBtn');
            const output = document.getElementById('output');
            
            if (copyBtn && output) {
                copyBtn.addEventListener('click', function() {
                    output.select();
                    document.execCommand('copy');
                    alert('出力をクリップボードにコピーしました');
                });
            }
            
            // ダウンロードボタン
            const downloadBtn = document.getElementById('downloadBtn');
            
            if (downloadBtn && output) {
                downloadBtn.addEventListener('click', function() {
                    const text = output.value;
                    const blob = new Blob([text], { type: 'text/plain' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'ai-output.txt';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                });
            }
            
            // 画像アップロード
            const imageUpload = document.getElementById('imageUpload');
            const pasteEnabled = document.getElementById('pasteEnabled');
            const imagePreview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            const removeImage = document.getElementById('removeImage');
            
            if (imageUpload && imagePreview && previewImg && removeImage) {
                imageUpload.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            imagePreview.classList.remove('d-none');
                        };
                        reader.readAsDataURL(file);
                    }
                });
                
                removeImage.addEventListener('click', function() {
                    imageUpload.value = '';
                    previewImg.src = '#';
                    imagePreview.classList.add('d-none');
                });
            }
            
            // クリップボードからの画像貼り付け
            if (pasteEnabled) {
                pasteEnabled.addEventListener('change', function() {
                    if (this.checked) {
                        document.addEventListener('paste', handlePaste);
                    } else {
                        document.removeEventListener('paste', handlePaste);
                    }
                });
                
                function handlePaste(e) {
                    const items = (e.clipboardData || e.originalEvent.clipboardData).items;
                    for (let i = 0; i < items.length; i++) {
                        if (items[i].type.indexOf('image') !== -1) {
                            const blob = items[i].getAsFile();
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                previewImg.src = e.target.result;
                                imagePreview.classList.remove('d-none');
                            };
                            reader.readAsDataURL(blob);
                            break;
                        }
                    }
                }
            }
            
            <?php if (!empty($output)): ?>
            // 出力結果がある場合は表示
            output.value = <?php echo json_encode($output); ?>;
            <?php endif; ?>
        });
    </script>
</body>
</html>
