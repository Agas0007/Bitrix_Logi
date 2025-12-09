Сделаем так, чтобы все PHP-ошибки и исключения Bitrix писались в /bitrix/error.log с точной строкой и файлом, где они произошли.
Также выведем лёгкое debug-сообщение в браузере (только для разработчиков), чтобы не гадать, где именно падает.

Шаг 1. Включи отладку в /bitrix/php_interface/init.php

Добавь в самый верх файла (можно и вниз) init.php:

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

Что делает код:
включает display_errors (временно, потом можно выключить);
направляет всё в bitrix/error.log;
ловит фатальные ошибки (shutdown handler);
выводит краткое сообщение прямо в HTML, чтобы ты видел, где именно 500.

✅ Шаг 2. Убедись, что файл bitrix/error.log доступен для записи
если нет файла — создай пустой:

touch /var/www/html/bitrix/error.log
chmod 666 /var/www/html/bitrix/error.log

✅ Шаг 3. Проверка

Теперь, если в шаблоне или компоненте произойдёт любая ошибка (например, в preg_replace или HighloadBlock), ты увидишь в браузере что-то вроде:
⚠️ Ошибка PHP: preg_replace(): Compilation failed: missing terminating ] for character class at offset 0
Файл: /bitrix/templates/comtech/header.php:95

и в bitrix/error.log появится запись:
[2025-11-01 14:23:42] FATAL: preg_replace(): Compilation failed: missing terminating ] for character class at offset 0 in /bitrix/templates/comtech/header.php:95

✅ Шаг 4. После отладки — выключи вывод ошибок в браузере
Когда поймаешь источник, просто отключи отображение ошибок, но оставь логирование:

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);


🪶 Бонус — быстрый способ протестировать

trigger_error("Тестовая ошибка для проверки логов", E_USER_WARNING);

После перезагрузки страницы в bitrix/error.log должно появиться:
[2025-11-01 14:26:00] PHP Warning:  Тестовая ошибка для проверки логов in /bitrix/templates/comtech/header.php on line ...
