<?php

// =======================
// 💡 ЛОГИРОВАНИЕ ОШИБОК
// =======================
ini_set('display_errors', 1);           // Показывать ошибки в браузере (временно)
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER["DOCUMENT_ROOT"] . '/bitrix/error.log');
error_reporting(E_ALL);                 // Логировать всё, включая Notice и Warning

// Чтобы видеть, что скрипт жив и не падает молча:
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $message = '[' . date('Y-m-d H:i:s') . '] FATAL: ' .
                   $error['message'] . ' in ' . $error['file'] . ':' . $error['line'] . "\n";
        error_log($message, 3, $_SERVER["DOCUMENT_ROOT"] . '/bitrix/error.log');
        if (php_sapi_name() !== 'cli') {
            echo '<pre style="color:red;background:#fff0f0;padding:10px;border:1px solid #f99;">
            ⚠️ Ошибка PHP: ' . htmlspecialchars($error['message']) . '<br>Файл: ' .
            htmlspecialchars($error['file']) . ':' . $error['line'] . '</pre>';
        }
    }
});
?>
