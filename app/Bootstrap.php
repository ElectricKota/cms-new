<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;
use Tracy\Debugger;

final class Bootstrap
{
    public static function boot(): Configurator
    {
        $rootDir = dirname(__DIR__);
        $configurator = new Configurator();
        umask(0002);

        $runtimeUser = 'web';
        if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
            $userInfo = posix_getpwuid(posix_geteuid());
            $runtimeUser = is_array($userInfo) ? (string) $userInfo['name'] : $runtimeUser;
        }

        $tempDir = $rootDir . '/temp/runtime-' . preg_replace('~[^a-zA-Z0-9_-]+~', '-', $runtimeUser);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $configurator->setTempDirectory($tempDir);

        $debugIps = ['127.0.0.1', '::1'];
        $debugCookie = $_COOKIE['nette-debug'] ?? null;
        if ($debugCookie === 'michal') {
            $debugIps[] = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        $configurator->setDebugMode($debugIps);
        $configurator->enableTracy($rootDir . '/log');
        Debugger::$strictMode = true;

        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();

        $configurator->addConfig($rootDir . '/config/common.neon');
        $local = $rootDir . '/config/local.neon';
        if (is_file($local)) {
            $configurator->addConfig($local);
        }

        return $configurator;
    }
}
