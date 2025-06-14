<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'AI情報システム'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .ai-card {
            transition: transform 0.2s;
            height: 100%;
        }
        .ai-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .ai-icon {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .ai-icon-small {
            width: 32px;
            height: 32px;
            object-fit: contain;
            border-radius: 4px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .ai-icon-placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #e9ecef;
            color: #6c757d;
            font-size: 24px;
        }
        .ai-icon-small-placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .brand-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .popularity-score {
            background: linear-gradient(45deg, #007bff, #28a745);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .pricing-badge {
            font-size: 0.75em;
            padding: 2px 6px;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .detail-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        /* 画像読み込みエラー時のスタイル */
        .image-error {
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
    </style>
    <script>
        // 画像読み込みエラー時の処理
        function handleImageError(img, size = 'normal') {
            const placeholder = document.createElement('div');
            placeholder.className = size === 'small' ? 'ai-icon-small ai-icon-small-placeholder' : 'ai-icon ai-icon-placeholder';
            placeholder.innerHTML = '<i class="fas fa-robot"></i>';
            img.parentNode.replaceChild(placeholder, img);
        }
        
        // ページ読み込み完了後に画像エラーをチェック
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img[src*="images/"]');
            images.forEach(function(img) {
                img.addEventListener('error', function() {
                    const isSmall = this.classList.contains('ai-icon-small');
                    handleImageError(this, isSmall ? 'small' : 'normal');
                });
            });
        });
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="AI_index.php">
                <i class="fas fa-robot me-2"></i>AI情報システム
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="AI_index.php">ホーム</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="AI_list.php">一覧表示</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="AI_comparison.php">比較ページ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="AI_ranking.php">人気ランキング</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="AI_search.php">検索</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
