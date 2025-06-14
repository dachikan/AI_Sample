<?php
/**
 * AI_comparison.phpを更新するスクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>AI_comparison.php更新</title></head>";
echo "<body>";
echo "<h1>🔄 AI_comparison.php更新</h1>";

// ファイルパス
$original_file = 'AI_comparison.php';
$fixed_file = 'AI_comparison_fixed.php';
$backup_file = 'AI_comparison_backup_' . date('Ymd_His') . '.php';

// バックアップを作成
if (file_exists($original_file)) {
    if (copy($original_file, $backup_file)) {
        echo "<p style='color:green'>✓ バックアップファイル作成: $backup_file</p>";
    } else {
        echo "<p style='color:red'>✗ バックアップファイルの作成に失敗しました</p>";
        exit;
    }
} else {
    echo "<p style='color:red'>✗ 元のファイルが見つかりません: $original_file</p>";
    exit;
}

// 修正ファイルが存在するか確認
if (!file_exists($fixed_file)) {
    echo "<p style='color:red'>✗ 修正ファイルが見つかりません: $fixed_file</p>";
    exit;
}

// 修正ファイルを元のファイルに上書き
if (copy($fixed_file, $original_file)) {
    echo "<p style='color:green'>✓ AI_comparison.phpを更新しました</p>";
    
    echo "<h2>主な修正内容:</h2>";
    echo "<ul>";
    echo "<li>URLパラメータ（ids[]）の適切な処理</li>";
    echo "<li>存在しないIDの処理（29や15など）</li>";
    echo "<li>エラーハンドリングの追加</li>";
    echo "<li>サービスデータの定義（5つのAIサービス）</li>";
    echo "<li>画像ファイルの存在チェック</li>";
    echo "</ul>";
    
    echo "<h2>🧪 テスト方法</h2>";
    echo "<p>以下のURLでテストしてください：</p>";
    echo "<ul>";
    echo "<li><a href='AI_comparison.php' target='_blank'>AI_comparison.php</a> - 選択なし</li>";
    echo "<li><a href='AI_comparison.php?ids[]=1&ids[]=2' target='_blank'>AI_comparison.php?ids[]=1&ids[]=2</a> - ChatGPTとClaudeの比較</li>";
    echo "<li><a href='AI_comparison.php?ids[]=29&ids[]=15' target='_blank'>AI_comparison.php?ids[]=29&ids[]=15</a> - 存在しないIDのテスト</li>";
    echo "</ul>";
} else {
    echo "<p style='color:red'>✗ ファイルの更新に失敗しました</p>";
}

echo "<p><a href='simple_debug.php'>デバッグ情報に戻る</a></p>";

echo "</body></html>";
?>
