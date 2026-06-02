<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Always load .env for testing environment to ensure DB parameters are loaded.
if (class_exists(\Dotenv\Dotenv::class)) {
    \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

// Force environment to test
$_ENV['APP_ENV'] = 'test';
putenv('APP_ENV=test');

// Align session save path for CLI functional tests
$savePath = dirname(__DIR__) . '/runtime/sessions';
if (!is_dir($savePath)) {
    mkdir($savePath, 0777, true);
}
ini_set('session.save_path', $savePath);

App\Environment::prepare();
