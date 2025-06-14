<?php
// エラー表示を有効化
ini_set('display_errors', 1);
error_reporting(E_ALL);

// バックアップを作成
$sourceFile = 'AI_index.php';
$backupFile = 'AI_index.php.backup_' . date('Ymd_His');

if (!file_exists($sourceFile)) {
    die("エラー: $sourceFile が見つかりません。");
}

// ファイルをバックアップ
if (!copy($sourceFile, $backupFile)) {
    die("エラー: バックアップの作成に失敗しました。");
}

echo "✓ バックアップを作成しました: $backupFile<br>";

// ファイルの内容を読み込む
$content = file_get_contents($sourceFile);
if ($content === false) {
    die("エラー: ファイルの読み込みに失敗しました。");
}

// リンク先を確認
$oldLink = '';
if (preg_match('/href=["\']([^"\']*)["\'][^>]*>最新をもっと見る/i', $content, $matches)) {
    $oldLink = $matches[1];
    echo "現在のリンク先: $oldLink<br>";
} else {
    echo "警告: '最新をもっと見る'リンクが見つかりませんでした。<br>";
}

// リンク先を修正
$newLink = 'AI_list.php';
$content = preg_replace(
    '/(href=["\'])([^"\']*)(["\']\s*[^>]*>最新をもっと見る)/i',
    '$1' . $newLink . '$3',
    $content
);

// 変更を保存
if (file_put_contents($sourceFile, $content) === false) {
    die("エラー: ファイルの書き込みに失敗しました。");
}

echo "✓ 'AI_index.php'の'最新をもっと見る'リンクを '$newLink' に修正しました。<br>";

// 修正結果を確認
echo "<h2>修正結果</h2>";
echo "<p>「最新をもっと見る」ボタンが正しく <strong>$newLink</strong> にリンクするようになりました。</p>";
echo "<p><a href='$sourceFile' target='_blank'>AI_index.phpを確認する</a></p>";
?>
