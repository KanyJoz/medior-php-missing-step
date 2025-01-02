<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Controller;

use KanyJoz\CodeFlash\Exception\Card\DatabaseException;
use KanyJoz\CodeFlash\Exception\Card\RecordNotFoundException;
use KanyJoz\CodeFlash\Helper\Http;
use KanyJoz\CodeFlash\Helper\PathBuilder;
use KanyJoz\CodeFlash\Helper\ResponseFormatter;
use KanyJoz\CodeFlash\Repository\CardRepositoryInterface;
use KanyJoz\CodeFlash\Validation\ValidatorFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class CardController
{
    public function __construct(
        private Twig $twig,
        private CardRepositoryInterface $cards,
        private PathBuilder $paths,
        private ValidatorFactory $validators,
        private ResponseFormatter $responses,
        private SessionInterface $session,
    ) {}

    public function show(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        $id = $args['cardID'];
        if (!is_numeric($id)) {
            throw new HttpNotFoundException($request);
        }

        $id = intval($id);
        if ($id < 1) {
            throw new HttpNotFoundException($request);
        }

        try {
            $card = $this->cards->getByID($id);
        } catch (RecordNotFoundException $ex) {
            throw new HttpNotFoundException($request, $ex->getMessage(), $ex);
        } catch (DatabaseException $ex) {
            throw new HttpInternalServerErrorException($request, $ex->getMessage(), $ex);
        }

        try {
            return $this->twig->render(
                Http::OK($response),
                $this->paths->card('show.twig'),
                ['card' => $card],
            );
        } catch (LoaderError|RuntimeError|SyntaxError $ex) {
            throw new HttpInternalServerErrorException($request, $ex->getMessage(), $ex);
        }
    }

    public function create(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        try {
            return $this->twig->render(
                Http::OK($response),
                $this->paths->card('create.twig'),
            );
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new HttpInternalServerErrorException($request, $e->getMessage(), $e);
        }
    }

    public function save(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        // Parse the incoming data
        $body = $request->getParsedBody();
        if ($body === null || is_object($body)) {
            throw new HttpInternalServerErrorException($request);
        }

        // Get the data from the Form
        $question = $body['question'];
        $answer = $body['answer'];

        $validator = $this->validators->instance();
        $validator->check($validator->minChars($question, 3),
            'question', 'This field cannot be less than 3 characters');
        $validator->check($validator->maxChars($question, 100),
            'question', 'This field cannot be more than 100 characters long');
        $validator->check($validator->minChars($answer, 10),
            'answer', 'This field cannot be less than 10 characters');

        // We keep the old data
        $validator->keepArray(['question' => $question, 'answer' => $answer]);

        if (!$validator->valid()) {
            try {
                // Pass it down in case of errors, together with errors
                return $this->twig->render(
                    Http::UnprocessableEntity($response),
                    $this->paths->card('create.twig'),
                    [
                        'errors' => $validator->errors(),
                        'old' => $validator->old(),
                    ]
                );
            } catch (LoaderError|RuntimeError|SyntaxError $e) {
                throw new HttpInternalServerErrorException($request, $e->getMessage(), $e);
            }
        }

        // Otherwise we just proceed and save the card
        // Then redirect to the show page
        try {
            $id = $this->cards->save($question, $answer);
        } catch (DatabaseException $e) {
            throw new HttpInternalServerErrorException($request, $e->getMessage(), $e);
        }

        // ...

        // Add Flash message
        $this->session->getFlash()->add('flash', 'Card was created!');

        return $this->responses->redirect($response, sprintf('/cards/%d', $id));
    }
}