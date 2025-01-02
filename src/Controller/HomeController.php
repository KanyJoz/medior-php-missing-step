<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Controller;

use KanyJoz\CodeFlash\Exception\Card\DatabaseException;
use KanyJoz\CodeFlash\Helper\Http;
use KanyJoz\CodeFlash\Helper\PathBuilder;
use KanyJoz\CodeFlash\Repository\CardRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class HomeController
{
    public function __construct(
        private Twig $twig,
        private PathBuilder $paths,
        private CardRepositoryInterface $cards,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $this->logger->info('Home Page Reached', [
            'template' => 'home.twig',
        ]);

        try {
            $cards = $this->cards->all();
        } catch (DatabaseException $ex) {
            throw new HttpInternalServerErrorException($request, $ex->getMessage(), $ex);
        }

        try {
            return $this->twig->render(
                Http::OK($response),
                $this->paths->page('home.twig'),
                ['cards' => $cards]
            );
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new HttpInternalServerErrorException($request, $e->getMessage(), $e);
        }
    }
}