<?php
// プレースホルダー画像を動的に生成するスクリプト
header('Content-Type: image/svg+xml');
header('Cache-Control: max-age=86400');

// パラメータを取得
$width = isset($_GET['width']) ? intval($_GET['width']) : 800;
$height = isset($_GET['height']) ? intval($_GET['height']) : 400;
$text = isset($_GET['text']) ? htmlspecialchars($_GET['text']) : 'プレースホルダー画像';

// SVG画像を生成
echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
?>
<svg width="<?php echo $width; ?>" height="<?php echo $height; ?>" xmlns="http://www.w3.org/2000/svg">
  <rect width="100%" height="100%" fill="#f0f0f0"/>
  <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="24" text-anchor="middle" dominant-baseline="middle" fill="#666">
    <?php echo $text; ?>
  </text>
  <text x="50%" y="58%" font-family="Arial, sans-serif" font-size="16" text-anchor="middle" dominant-baseline="middle" fill="#999">
    <?php echo $width; ?> x <?php echo $height; ?>
  </text>
</svg>