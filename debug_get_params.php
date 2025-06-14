<?php
/**
 * GETパラメータをデバッグするシンプルなスクリプト
 */

// エラー表示を有効にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head><meta charset='UTF-8'><title>GETパラメータデバッグ</title></head>";
echo "<body>";
echo "<h1>🔍 GETパラメータデバッグ</h1>";

echo "<h2>現在のURLパラメータ</h2>";
echo "<pre>";
var_dump($_GET);
echo "</pre>";

echo "<h2>パラメータの処理例</h2>";
echo "<pre>";
if (isset($_GET["ids"]) && is_array($_GET["ids"])) {
    $selectedIds = array_map("intval", $_GET["ids"]);
    echo "処理後のIDs: ";
    var_dump($selectedIds);
} else {
    echo "ids[]パラメータがありません";
}
echo "</pre>";

echo "<h2>テスト用リンク</h2>";
echo "<ul>";
echo "<li><a href='debug_get_params.php'>パラメータなし</a></li>";
echo "<li><a href='debug_get_params.php?ids[]=1&ids[]=2'>ids[]=1&ids[]=2</a></li>";
echo "<li><a href='debug_get_params.php?ids[]=29&ids[]=15'>ids[]=29&ids[]=15</a></li>";
echo "</ul>";

echo "<h2>🔧 AI_comparison.phpの修正ポイント</h2>";
echo "<ol>";
echo "<li>URLパラメータ（ids[]）の適切な処理</li>";
echo "<li>存在しないIDの処理（29や15など）</li>";
echo "<li>エラーハンドリングの追加</li>";
echo "</ol>";

echo "<p><a href='fix_ai_comparison_with_params.php'>AI_comparison.phpを修正する</a></p>";

echo "</body></html>";
?>
