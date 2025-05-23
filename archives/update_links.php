<?php
/**
 * AISample.php から View.php へのリンク自動更新スクリプト
 * 
 * 使用方法:
 * 1. このスクリプトをプロジェクトのルートディレクトリに配置
 * 2. コマンドラインから実行: php update_links.php
 * 3. または、ブラウザからアクセス（パスワード保護推奨）
 * 
 * 機能:
 * - 指定ディレクトリ内の全PHPファイルをスキャン
 * - AISample.phpへのリンクをView.phpに置換
 * - 変更前にバックアップを作成
 * - 詳細な変更レポートを生成
 */

// 設定
$config = [
    'scan_dir' => '.', // スキャンするディレクトリ（カレントディレクトリ）
    'backup_dir' => './backups_' . date('Y-m-d_H-i-s'), // バックアップディレクトリ
    'log_file' => './link_update_log_' . date('Y-m-d_H-i-s') . '.txt', // ログファイル
    'exclude_dirs' => ['backups', 'vendor', 'node_modules', '.git'], // 除外するディレクトリ
    'dry_run' => false, // trueの場合、実際の変更は行わず、変更予定の内容のみ表示
    'verbose' => true, // 詳細な出力を表示
    'search_pattern' => 'AISample.php', // 検索パターン
    'replace_with' => 'View.php', // 置換後の文字列
    'extensions' => ['php', 'html', 'js'], // 処理対象の拡張子
];

// 実行モードの確認（CLIかブラウザか）
$is_cli = php_sapi_name() === 'cli';

// 出力関数
function output($message, $type = 'info') {
    global $is_cli, $config;
    
    // ログファイルに記録
    file_put_contents($config['log_file'], date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
    
    // verboseモードでない場合、infoタイプのメッセージはスキップ
    if (!$config['verbose'] && $type === 'info') {
        return;
    }
    
    // 出力形式の設定
    if ($is_cli) {
        // CLI用の色付き出力
        switch ($type) {
            case 'error':
                echo "\033[31m[ERROR] $message\033[0m" . PHP_EOL;
                break;
            case 'warning':
                echo "\033[33m[WARNING] $message\033[0m" . PHP_EOL;
                break;
            case 'success':
                echo "\033[32m[SUCCESS] $message\033[0m" . PHP_EOL;
                break;
            case 'change':
                echo "\033[36m[CHANGE] $message\033[0m" . PHP_EOL;
                break;
            default:
                echo "[INFO] $message" . PHP_EOL;
        }
    } else {
        // ブラウザ用のHTML出力
        $color = 'black';
        $prefix = 'INFO';
        
        switch ($type) {
            case 'error':
                $color = 'red';
                $prefix = 'ERROR';
                break;
            case 'warning':
                $color = 'orange';
                $prefix = 'WARNING';
                break;
            case 'success':
                $color = 'green';
                $prefix = 'SUCCESS';
                break;
            case 'change':
                $color = 'blue';
                $prefix = 'CHANGE';
                break;
        }
        
        echo "<div style='color: $color;'><strong>[$prefix]</strong> " . htmlspecialchars($message) . "</div>";
    }
}

// バックアップディレクトリの作成
if (!$config['dry_run']) {
    if (!file_exists($config['backup_dir'])) {
        if (!mkdir($config['backup_dir'], 0755, true)) {
            output("バックアップディレクトリの作成に失敗しました: {$config['backup_dir']}", 'error');
            exit(1);
        }
    }
}

// ファイルをスキャンする関数
function scanDirectory($dir, $config) {
    $result = [];
    $items = scandir($dir);
    
    foreach ($items as $item) {
        // 特殊ディレクトリをスキップ
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        // ディレクトリの場合
        if (is_dir($path)) {
            // 除外ディレクトリをスキップ
            if (in_array($item, $config['exclude_dirs']) || in_array(basename($dir) . '/' . $item, $config['exclude_dirs'])) {
                output("ディレクトリをスキップします: $path", 'info');
                continue;
            }
            
            // 再帰的にスキャン
            $result = array_merge($result, scanDirectory($path, $config));
        } 
        // ファイルの場合
        else if (is_file($path)) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            
            // 指定された拡張子のファイルのみ処理
            if (in_array(strtolower($extension), $config['extensions'])) {
                $result[] = $path;
            }
        }
    }
    
    return $result;
}

// ファイル内のリンクを更新する関数
function updateLinks($file_path, $config) {
    // ファイルの内容を読み込み
    $content = file_get_contents($file_path);
    if ($content === false) {
        output("ファイルの読み込みに失敗しました: $file_path", 'error');
        return false;
    }
    
    // 変更前のコンテンツを保存
    $original_content = $content;
    
    // 変更パターンの定義
    $patterns = [
        // 通常のリンク: href="AISample.php" または href='AISample.php'
        '/(href=["|\'])' . preg_quote($config['search_pattern'], '/') . '(["|\'])/i',
        
        // クエリ付きリンク: href="AISample.php?id=123" または href='AISample.php?id=123'
        '/(href=["|\'])' . preg_quote($config['search_pattern'], '/') . '(\?[^"\']*["|\'])/i',
        
        // PHPのheader関数: header("Location: AISample.php")
        '/(header\s*\(\s*["|\'])Location:\s*' . preg_quote($config['search_pattern'], '/') . '(["|\'])/i',
        
        // PHPのheader関数（クエリ付き）: header("Location: AISample.php?id=123")
        '/(header\s*\(\s*["|\'])Location:\s*' . preg_quote($config['search_pattern'], '/') . '(\?[^"\']*["|\'])/i',
        
        // include/require文: include("AISample.php") または require_once 'AISample.php'
        '/(include|require|include_once|require_once)(\s*\(?["|\'])' . preg_quote($config['search_pattern'], '/') . '(["|\'])/i',
        
        // 変数内のパス: $path = "AISample.php" または $url = 'AISample.php?id=123'
        '/(\$[a-zA-Z0-9_]+\s*=\s*["|\'])' . preg_quote($config['search_pattern'], '/') . '(["|\'])/i',
        
        // 変数内のパス（クエリ付き）: $path = "AISample.php?id=123"
        '/(\$[a-zA-Z0-9_]+\s*=\s*["|\'])' . preg_quote($config['search_pattern'], '/') . '(\?[^"\']*["|\'])/i',
        
        // フォームのaction属性: action="AISample.php"
        '/(action=["|\'])' . preg_quote($config['search_pattern'], '/') . '(["|\'])/i',
        
        // フォームのaction属性（クエリ付き）: action="AISample.php?mode=edit"
        '/(action=["|\'])' . preg_quote($config['search_pattern'], '/') . '(\?[^"\']*["|\'])/i',
        
        // JavaScriptのwindow.location: window.location = "AISample.php"
        '/(window\.location(?:\.href)?\s*=\s*["|\'])' . preg_quote($config['search_pattern'], '/') . '(["|\'])/i',
        
        // JavaScriptのwindow.location（クエリ付き）: window.location = "AISample.php?id=123"
        '/(window\.location(?:\.href)?\s*=\s*["|\'])' . preg_quote($config['search_pattern'], '/') . '(\?[^"\']*["|\'])/i',
    ];
    
    $replacements = [
        '$1' . $config['replace_with'] . '$2',
        '$1' . $config['replace_with'] . '$2',
        '$1Location: ' . $config['replace_with'] . '$2',
        '$1Location: ' . $config['replace_with'] . '$2',
        '$1$2' . $config['replace_with'] . '$3',
        '$1' . $config['replace_with'] . '$2',
        '$1' . $config['replace_with'] . '$2',
        '$1' . $config['replace_with'] . '$2',
        '$1' . $config['replace_with'] . '$2',
        '$1' . $config['replace_with'] . '$2',
        '$1' . $config['replace_with'] . '$2',
    ];
    
    // 変更を適用
    $new_content = $content;
    $changes = [];
    
    foreach ($patterns as $index => $pattern) {
        $new_content_temp = preg_replace($pattern, $replacements[$index], $new_content, -1, $count);
        
        if ($count > 0) {
            // 変更された行を特定
            preg_match_all($pattern, $new_content, $matches, PREG_OFFSET_CAPTURE);
            
            foreach ($matches[0] as $match) {
                $pos = $match[1];
                $line_number = substr_count(substr($new_content, 0, $pos), "\n") + 1;
                $line_content = getLineContent($new_content, $line_number);
                $changes[] = [
                    'line' => $line_number,
                    'original' => $line_content,
                    'new' => str_replace(
                        $config['search_pattern'], 
                        $config['replace_with'], 
                        $line_content
                    )
                ];
            }
            
            $new_content = $new_content_temp;
        }
    }
    
    // 変更があった場合
    if ($new_content !== $original_content) {
        output("ファイル内で変更が見つかりました: $file_path", 'change');
        
        foreach ($changes as $change) {
            output("  行 {$change['line']}:", 'info');
            output("    元: " . trim($change['original']), 'info');
            output("    新: " . trim($change['new']), 'info');
        }
        
        // ドライランモードでなければ実際に変更を適用
        if (!$config['dry_run']) {
            // バックアップを作成
            $backup_path = $config['backup_dir'] . '/' . str_replace('/', '_', $file_path);
            $backup_dir = dirname($backup_path);
            
            if (!file_exists($backup_dir)) {
                mkdir($backup_dir, 0755, true);
            }
            
            if (!copy($file_path, $backup_path)) {
                output("バックアップの作成に失敗しました: $backup_path", 'error');
                return false;
            }
            
            // 変更を書き込み
            if (file_put_contents($file_path, $new_content) === false) {
                output("ファイルの書き込みに失敗しました: $file_path", 'error');
                return false;
            }
            
            output("ファイルを更新しました: $file_path", 'success');
        }
        
        return count($changes);
    }
    
    return 0;
}

// 指定行の内容を取得する関数
function getLineContent($content, $line_number) {
    $lines = explode("\n", $content);
    return isset($lines[$line_number - 1]) ? $lines[$line_number - 1] : '';
}

// メイン処理
output("リンク更新処理を開始します", 'info');
output("検索パターン: {$config['search_pattern']}", 'info');
output("置換後: {$config['replace_with']}", 'info');
output("ドライラン: " . ($config['dry_run'] ? 'はい' : 'いいえ'), 'info');

// ファイルのスキャン
output("ディレクトリのスキャンを開始: {$config['scan_dir']}", 'info');
$files = scanDirectory($config['scan_dir'], $config);
output("スキャン完了: " . count($files) . " ファイルが見つかりました", 'info');

// 各ファイルの処理
$total_files = count($files);
$changed_files = 0;
$total_changes = 0;

foreach ($files as $index => $file) {
    $progress = ($index + 1) . '/' . $total_files;
    output("ファイルを処理中 ($progress): $file", 'info');
    
    $changes = updateLinks($file, $config);
    
    if ($changes > 0) {
        $changed_files++;
        $total_changes += $changes;
    }
}

// 結果の出力
output("処理完了", 'success');
output("スキャンしたファイル数: $total_files", 'info');
output("変更があったファイル数: $changed_files", 'info');
output("合計変更数: $total_changes", 'info');

if ($config['dry_run']) {
    output("ドライランモードのため、実際の変更は行われていません", 'warning');
    output("実際に変更を適用するには、スクリプト内の 'dry_run' 設定を false に変更してください", 'info');
} else {
    output("バックアップは以下のディレクトリに保存されました: {$config['backup_dir']}", 'info');
    output("ログファイル: {$config['log_file']}", 'info');
}

// ブラウザ表示の場合、HTMLフッターを追加
if (!$is_cli) {
    echo "<hr><p>処理が完了しました。詳細はログファイルを確認してください。</p>";
}
?>