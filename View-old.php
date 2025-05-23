<?php
// このファイルは AISample-Detail.php にリダイレクトします
// 後方互換性のために残しています

// クエリパラメータを取得
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // IDが指定されている場合は詳細ページにリダイレクト
    header('Location: AISample-Detail.php?id=' . $id . (isset($_GET['success']) ? '&success=' . $_GET['success'] : ''));
} else {
    // IDが指定されていない場合は一覧ページにリダイレクト
    header('Location: AISampleList-unified.php');
}
exit;
?>
