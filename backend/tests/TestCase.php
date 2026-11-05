<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Protección: las pruebas borran la base de datos (RefreshDatabase).
     * Si la configuración está en caché (php artisan config:cache), Laravel ignora phpunit.xml
     * y las pruebas correrían contra MySQL. En ese caso se detienen antes de tocar nada.
     */
    public function createApplication()
    {
        if (file_exists(dirname(__DIR__).'/bootstrap/cache/config.php')) {
            fwrite(STDERR, "\nLa configuración está en caché. Ejecuta 'php artisan config:clear' antes de las pruebas.\n");
            exit(1);
        }

        $app = parent::createApplication();

        $db = $app['config']->get('database.default');
        $name = $app['config']->get("database.connections.{$db}.database");
        if ($db !== 'sqlite' || $name !== ':memory:') {
            fwrite(STDERR, "\nLas pruebas solo pueden usar SQLite en memoria (actual: {$db} / {$name}).\n");
            exit(1);
        }

        return $app;
    }
}
