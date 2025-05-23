<?php
// 画像ファイルを整理するためのスクリプト
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 移動対象のファイル
$filesToMove = [
    'sample-image.png' => 'images/sample-image.png',
    'placeholder.png' => 'images/placeholder.png'
];

// 移動処理
foreach ($filesToMove as $source => $destination) {
    if (file_exists($source)) {
        // 移動先のファイルが既に存在する場合は上書きしない
        if (file_exists($destination)) {
            echo "ファイル {$destination} は既に存在します。<br>";
        } else {
            if (copy($source, $destination)) {
                echo "ファイル {$source} を {$destination} にコピーしました。<br>";
                // 元のファイルは残しておく（安全のため）
                // unlink($source);
                // echo "元のファイル {$source} を削除しました。<br>";
            } else {
                echo "ファイル {$source} のコピーに失敗しました。<br>";
            }
        }
    } else {
        echo "ファイル {$source} が見つかりません。<br>";
    }
}

// AISample-Detail.phpの画像パス参照を更新
$detailFile = 'AISample-Detail.php';
if (file_exists($detailFile)) {
    $content = file_get_contents($detailFile);
    
    // 画像パスの更新
    $content = str_replace('src="sample-image.png"', 'src="images/sample-image.png"', $content);
    $content = str_replace('src="placeholder.png"', 'src="images/placeholder.png"', $content);
    $content = str_replace("showImageModal('sample-image.png'", "showImageModal('images/sample-image.png'", $content);
    $content = str_replace("showImageModal('placeholder.png'", "showImageModal('images/placeholder.png'", $content);
    
    if (file_put_contents($detailFile, $content)) {
        echo "ファイル {$detailFile} の画像パス参照を更新しました。<br>";
    } else {
        echo "ファイル {$detailFile} の更新に失敗しました。<br>";
    }
} else {
    echo "ファイル {$detailFile} が見つかりません。<br>";
}

echo "<p>処理が完了しました。</p>";
echo "<p><a href='AISampleList-unified.php'>サンプル一覧ページに戻る</a></p>";
?>
