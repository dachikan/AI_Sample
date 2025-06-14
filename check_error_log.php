<?php
// エラーログの場所を確認
echo "PHP Error Log: " . ini_get('error_log') . "\n";
echo "Log Errors: " . (ini_get('log_errors') ? 'On' : 'Off') . "\n";
echo "Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "\n";

// 一般的なエラーログの場所
$possible_logs = [
    '/var/log/apache2/error.log',
    '/var/log/httpd/error_log',
    '/var/log/nginx/error.log',
    './error_log',
    '../error_log',
    '../../error_log'
];

foreach ($possible_logs as $log) {
    if (file_exists($log) && is_readable($log)) {
        echo "Found log file: $log\n";
        // 最後の10行を表示
        $lines = file($log);
        $last_lines = array_slice($lines, -10);
        foreach ($last_lines as $line) {
            echo $line;
        }
        break;
    }
}
?>