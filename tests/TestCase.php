<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Guard against running the suite (and RefreshDatabase's migrate:fresh) on a real database.
     * A cached config (bootstrap/cache/config.php) overrides phpunit.xml's test database settings.
     */
    protected function setUpTraits(): array
    {
        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if ($database !== ':memory:' && ! str_ends_with($database, '_test')) {
            throw new \RuntimeException(sprintf(
                'Refusing to run tests against database "%s" (APP_ENV=%s): only ":memory:" or a "*_test" database is allowed. Cached config? Run with APP_CONFIG_CACHE pointed at a non-existent file.',
                $database,
                config('app.env'),
            ));
        }

        return parent::setUpTraits();
    }
}
