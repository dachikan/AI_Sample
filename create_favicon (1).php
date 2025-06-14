<?php
/**
 * favicon.icoを作成するスクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>favicon作成</title></head>";
echo "<body>";
echo "<h1>🖼️ favicon.ico作成ツール</h1>";

// faviconのパス
$favicon_path = 'favicon.ico';

// faviconが既に存在するか確認
if (file_exists($favicon_path)) {
    echo "<p>✓ favicon.icoは既に存在します</p>";
    echo "<p>場所: " . realpath($favicon_path) . "</p>";
    echo "<p>サイズ: " . filesize($favicon_path) . " bytes</p>";
} else {
    // シンプルなfaviconを作成
    try {
        // GDライブラリが利用可能か確認
        if (!extension_loaded('gd')) {
            throw new Exception("GD拡張モジュールが有効ではありません");
        }
        
        // 16x16のイメージを作成
        $img = imagecreatetruecolor(16, 16);
        
        // 背景色（青）
        $blue = imagecolorallocate($img, 0, 102, 204);
        imagefill($img, 0, 0, $blue);
        
        // 文字色（白）
        $white = imagecolorallocate($img, 255, 255, 255);
        
        // 'A'の文字を描画（簡易的）
        imagestring($img, 1, 5, 4, 'A', $white);
        
        // PNGとして出力
        $temp_png = 'temp_favicon.png';
        imagepng($img, $temp_png);
        imagedestroy($img);
        
        // PNGをICOに変換（簡易的な方法）
        $png_data = file_get_contents($temp_png);
        
        // ICOヘッダー
        $ico_data = pack('vvv', 0, 1, 1);  // 0: reserved, 1: ICO type, 1: number of images
        
        // ICOディレクトリエントリ
        $ico_data .= pack('CCCCvvVV', 
            16,                     // width
            16,                     // height
            0,                      // color palette
            0,                      // reserved
            1,                      // color planes
            24,                     // bits per pixel
            strlen($png_data),      // size of image data
            6 + 16                  // offset to image data
        );
        
        // PNG画像データを追加
        $ico_data .= $png_data;
        
        // ICOファイルとして保存
        if (file_put_contents($favicon_path, $ico_data)) {
            echo "<p style='color:green'>✓ favicon.icoを作成しました</p>";
            echo "<p>場所: " . realpath($favicon_path) . "</p>";
            echo "<p>サイズ: " . filesize($favicon_path) . " bytes</p>";
            
            // 一時ファイルを削除
            unlink($temp_png);
        } else {
            throw new Exception("ファイルの書き込みに失敗しました");
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ favicon.icoの作成に失敗しました: " . $e->getMessage() . "</p>";
        
        // 代替方法：空のfaviconファイルを作成
        echo "<p>代替方法として空のfaviconファイルを作成します...</p>";
        
        if (file_put_contents($favicon_path, '')) {
            echo "<p style='color:green'>✓ 空のfavicon.icoを作成しました</p>";
        } else {
            echo "<p style='color:red'>✗ 空のファイルの作成にも失敗しました</p>";
        }
    }
}

echo "<h2>🔧 403エラーの解決</h2>";
echo "<p>favicon.icoが作成されたことで、ブラウザが自動的にリクエストするfavicon.icoの403エラーは解消されるはずです。</p>";

echo "<p><a href='simple_debug.php'>デバッグ情報に戻る</a></p>";

echo "</body></html>";
?>
