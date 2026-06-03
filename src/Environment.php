<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

use function in_array;
use function sprintf;

final class Environment
{
    public const DEV = 'dev';
    public const TEST = 'test';
    public const PROD = 'prod';

    public const ENVIRONMENTS = [
        self::DEV,
        self::TEST,
        self::PROD,
    ];

    private static array $values = [];

    public static function prepare(): void
    {
        self::setEnvironment();
        self::setBoolean('APP_C3', false);
        self::setBoolean('APP_DEBUG', false);
        self::setNonEmptyStringOrNull('APP_HOST_PATH', null);
        self::setString('DB_HOST', '127.0.0.1');
        self::setString('DB_PORT', '3306');
        self::setString('DB_NAME', 'teqic_yii3');
        self::setString('DB_USER', 'root');
        self::setString('DB_PASSWORD', '');
        self::setString('HAWI_SSO_URL', 'http://localhost');
        self::setString('HAWI_SSO_CLIENT_ID', '');
        self::setString('HAWI_SSO_CLIENT_SECRET', '');
        self::setString('HAWI_SSO_REDIRECT_URI', 'http://localhost/auth/callback');
        self::setString('DOREH_URL', 'http://localhost:3000');
    }

    /**
     * @return non-empty-string
     */
    public static function appEnv(): string
    {
        /** @var non-empty-string */
        return self::$values['APP_ENV'];
    }

    public static function isDev(): bool
    {
        return self::appEnv() === self::DEV;
    }

    public static function isTest(): bool
    {
        return self::appEnv() === self::TEST;
    }

    public static function isProd(): bool
    {
        return self::appEnv() === self::PROD;
    }

    /**
     * @return non-empty-string|null
     */
    public static function appHostPath(): ?string
    {
        /** @var non-empty-string|null */
        return self::$values['APP_HOST_PATH'];
    }

    public static function appC3(): bool
    {
        /** @var bool */
        return self::$values['APP_C3'];
    }

    public static function appDebug(): bool
    {
        /** @var bool */
        return self::$values['APP_DEBUG'];
    }

    public static function dbHost(): string
    {
        return (string) self::$values['DB_HOST'];
    }

    public static function dbPort(): string
    {
        return (string) self::$values['DB_PORT'];
    }

    public static function dbName(): string
    {
        return (string) self::$values['DB_NAME'];
    }

    public static function dbUser(): string
    {
        return (string) self::$values['DB_USER'];
    }

    public static function dbPassword(): string
    {
        return (string) self::$values['DB_PASSWORD'];
    }

    public static function hawiSsoUrl(): string
    {
        return (string) self::$values['HAWI_SSO_URL'];
    }

    public static function hawiSsoClientId(): string
    {
        return (string) self::$values['HAWI_SSO_CLIENT_ID'];
    }

    public static function hawiSsoClientSecret(): string
    {
        return (string) self::$values['HAWI_SSO_CLIENT_SECRET'];
    }

    public static function hawiSsoRedirectUri(): string
    {
        return (string) self::$values['HAWI_SSO_REDIRECT_URI'];
    }

    public static function dorehUrl(): string
    {
        return (string) self::$values['DOREH_URL'];
    }

    private static function setEnvironment(): void
    {
        $environment = self::getRawValue('APP_ENV') ?: self::PROD;

        if (!in_array($environment, self::ENVIRONMENTS, true)) {
            throw new RuntimeException(
                sprintf(
                    'APP_ENV="%s" is invalid. Valid values are "%s".',
                    $environment,
                    implode('", "', self::ENVIRONMENTS),
                ),
            );
        }

        self::$values['APP_ENV'] = $environment;
    }

    private static function setBoolean(string $key, bool $default): void
    {
        $value = self::getRawValue($key);
        self::$values[$key] = $value === null
            ? $default
            : (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default);
    }

    private static function setInteger(string $key, int $default): void
    {
        $value = self::getRawValue($key);
        self::$values[$key] = $value === null ? $default : (int) $value;
    }

    private static function setString(string $key, string $default): void
    {
        $value = self::getRawValue($key);
        self::$values[$key] = $value ?? $default;
    }

    private static function setNonEmptyStringOrNull(string $key, ?string $default): void
    {
        $value = self::getRawValue($key);
        self::$values[$key] = $value === null || $value === '' ? $default : $value;
    }

    private static function getRawValue(string $key): ?string
    {
        $value = getenv($key, true);
        if ($value !== false) {
            return $value;
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return isset($_ENV[$key]) ? (string) $_ENV[$key] : null;
    }
}
