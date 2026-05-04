<?php

// Clever Cloud injecte MYSQL_ADDON_URI mais pas DATABASE_URL.
// En PHP-FPM, $_SERVER ne contient pas les variables d'environnement processus :
// on utilise getenv() en priorité, puis $_SERVER comme fallback.
$_mysqlUri = getenv('MYSQL_ADDON_URI') ?: ($_SERVER['MYSQL_ADDON_URI'] ?? '');
$_dbUrl    = getenv('DATABASE_URL')    ?: ($_SERVER['DATABASE_URL']    ?? '');
if (!empty($_mysqlUri) && (empty($_dbUrl) || str_starts_with($_dbUrl, 'mysql://admin:'))) {
    $base = strtok($_mysqlUri, '?');
    $url  = $base . '?serverVersion=8.0&charset=utf8mb4';
    putenv('DATABASE_URL=' . $url);
    $_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] = $url;
}
unset($_mysqlUri, $_dbUrl, $base, $url);

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
