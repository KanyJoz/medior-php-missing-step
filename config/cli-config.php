<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use KanyJoz\CodeFlash\Configuration;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = require_once dirname(__DIR__) . '/bin/bootstrap_cli.php';

/** @var Configuration $config */
$config = $container->get(Configuration::class);

$migrationsConfig = new PhpFile('config/migrations.php');

$conn = DriverManager::getConnection([
    'driver' => sprintf('pdo_%s', $config->get('db.driver')),
    'host' => $config->get('db.mysql.host'),
    'port' => $config->get('db.mysql.port'),
    'dbname' => $config->get('db.mysql.db_name'),
    'user' => $config->get('db.mysql.user'),
    'password' => $config->get('db.mysql.pass'),
]);

return DependencyFactory::fromConnection($migrationsConfig, new ExistingConnection($conn));
