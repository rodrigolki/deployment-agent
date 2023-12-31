<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => isset($_SERVER['DEV']) ? true : false, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'db' => [
                    'driver' => 'pgsql',
                    'host' => $_SERVER['DB_HOST'] ?? "",
                    'database' => $_SERVER['DB_NAME'] ?? "",
                    'username' => $_SERVER['DB_USER'] ?? "",
                    'password' => $_SERVER['DB_PASSWORD'] ?? "",
                    'prefix'    => '',
                    'timezone' => 'America/Sao_Paulo',
                ]
            ]);
        }
    ]);
};
