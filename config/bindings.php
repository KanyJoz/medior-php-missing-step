<?php

declare(strict_types=1);

use KanyJoz\CodeFlash\Configuration;
use KanyJoz\CodeFlash\Filter\ShorterTwigFilter;
use KanyJoz\CodeFlash\Helper\Auth;
use KanyJoz\CodeFlash\Repository\CardRepositoryInterface;
use KanyJoz\CodeFlash\Repository\CardRepositoryMySQL;
use KanyJoz\CodeFlash\Repository\UserRepositoryInterface;
use KanyJoz\CodeFlash\Repository\UserRepositoryMySQL;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\RoutingMiddleware;
use Slim\Views\Twig;
use Twig\TwigFilter;

return [
    App::class => function(ContainerInterface $container) {
        return AppFactory::createFromContainer($container);
    },

    Configuration::class => function() {
        return new Configuration(require_once APP_CONFIG_PATH);
    },

    Twig::class => function(Configuration $config) {
        $twig = Twig::create(
            $config->get('twig.template.path'),
            $config->get('twig.template.options')
        );

        $environment = $twig->getEnvironment();
        $environment->addFilter(new TwigFilter('shorter', [ShorterTwigFilter::class, 'shorter']));

        return $twig;
    },

    LoggerInterface::class => function() {
        $logger = new Logger('app');

        // Formats date of log entry
        $dateFormat = 'Y-m-d H:i:s';
        $formatter = new LineFormatter(dateFormat: $dateFormat);

        // Handles DEBUG and INFO levels
        $debugHandler = new StreamHandler(INFO_LOG_PATH);
        $debugHandler->setLevel(Level::Debug);
        $debugHandler->setFormatter($formatter);
        $logger->pushHandler($debugHandler);

        // Handles WARNING and ERROR levels
        $errorHandler = new StreamHandler(ERROR_LOG_PATH);
        $errorHandler->setLevel(Level::Warning);
        $errorHandler->setBubble(false);
        $errorHandler->setFormatter($formatter);
        $logger->pushHandler($errorHandler);

        return $logger;
    },

    ErrorMiddleware::class => function(App $app, Configuration $config, LoggerInterface $logger) {
        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            $config->get('errors.display'),
            $config->get('errors.use_logger'),
            $config->get('errors.log_details'),
            $logger
        );
    },
    RoutingMiddleware::class => function(App $app) {
        return new RoutingMiddleware(
            $app->getRouteResolver(),
            $app->getRouteCollector()->getRouteParser()
        );
    },
    BodyParsingMiddleware::class => function() {
        $bodyParsers = [];

        return new BodyParsingMiddleware($bodyParsers);
    },

    PDO::class => function(Configuration $config) {
        switch ($config->get('db.driver')) {
            default:
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                    $config->get('db.mysql.host'),
                    $config->get('db.mysql.port'),
                    $config->get('db.mysql.db_name'),
                    $config->get('db.mysql.charset'),
                );
                $user = $config->get('db.mysql.user');
                $pass = $config->get('db.mysql.pass');
                $options = $config->get('db.mysql.options');

                return new PDO($dsn, $user, $pass, $options);
        }
    },
    CardRepositoryInterface::class => function(PDO $pdo) {
        return new CardRepositoryMySQL($pdo);
    },
    UserRepositoryInterface::class => function(PDO $pdo) {
        return new UserRepositoryMySQL($pdo);
    },

    SessionManagerInterface::class => function(ContainerInterface $container) {
        return $container->get(SessionInterface::class);
    },
    SessionInterface::class => function(Configuration $config) {
        $options= [
            'name' => $config->get('session.name'),
            'cache_limiter' => $config->get('session.cache_limiter'),
            ...$config->get('session.cookie')
        ];

        return new PhpSession($options);
    },

    Auth::class => function(SessionInterface $session) {
        return new Auth($session);
    },
    ResponseFactoryInterface::class => function() {
        return new Psr17Factory();
    },
];