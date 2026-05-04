<?php

// Clever Cloud injecte MYSQL_ADDON_URI mais pas DATABASE_URL.
// On construit DATABASE_URL ici, avant que Symfony Dotenv le lise,
// pour que Doctrine utilise la bonne connexion en production.
if (empty($_SERVER['DATABASE_URL']) && !empty($_SERVER['MYSQL_ADDON_URI'])) {
    $base = strtok($_SERVER['MYSQL_ADDON_URI'], '?');
    $_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] =
        $base . '?serverVersion=8.0&charset=utf8mb4';
}

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
