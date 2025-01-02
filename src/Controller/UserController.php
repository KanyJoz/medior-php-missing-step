<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Controller;

use KanyJoz\CodeFlash\Exception\Card\DatabaseException;
use KanyJoz\CodeFlash\Exception\Card\DuplicateEmailException;
use KanyJoz\CodeFlash\Exception\Card\InvalidCredentialsException;
use KanyJoz\CodeFlash\Helper\Http;
use KanyJoz\CodeFlash\Helper\PathBuilder;
use KanyJoz\CodeFlash\Helper\ResponseFormatter;
use KanyJoz\CodeFlash\Repository\CardRepositoryInterface;
use KanyJoz\CodeFlash\Repository\UserRepositoryInterface;
use KanyJoz\CodeFlash\Validation\Validator;
use KanyJoz\CodeFlash\Validation\ValidatorFactory;
use Odan\Session\Exception\SessionException;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class UserController
{
    public function __construct(
        private Twig $twig,
        private UserRepositoryInterface $users,
        private PathBuilder $paths,
        private ValidatorFactory $validators,
        private ResponseFormatter $responses,
        private SessionInterface $session,
    ) {}

    public function register(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        try {
            return $this->twig->render(
                Http::OK($response),
                $this->paths->user('register.twig'),
            );
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new HttpInternalServerErrorException($request, $e->getMessage(), $e);
        }
    }

    public function registerPost(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        // Get form data
        $body = $request->getParsedBody();
        if ($body === null || is_object($body)) {
            throw new HttpInternalServerErrorException($request);
        }

        // Validate
        $validator = $this->validators->instance();
        $validator->check($validator->minChars($body['email'], 10),
            'email', 'This field cannot be less than 10 characters');
        $validator->check($validator->matches($body['email'], Validator::EMAIL_PATTERN),
            'email', 'This field must be a valid email address');
        $validator->check($validator->minChars($body['password'], 8),
            'password', 'This field must be at least 8 characters long');

        // Keep Old
        $validator->keepArray(['email' => $body['email']]);

        // If there is validation error
        // we render the register.twig with the old and error arrays
        if (!$validator->valid()) {
            try {
                return $this->twig->render(
                    Http::UnprocessableEntity($response),
                    $this->paths->user('register.twig'),
                    [
                        'errors' => $validator->errors(),
                        'old' => $validator->old(),
                    ]
                );
            } catch (LoaderError|RuntimeError|SyntaxError $e) {
                throw new HttpInternalServerErrorException($request, $e->getMessage(), $e);
            }
        }

        // We do the same if we have a duplicate email
        try {
            $this->users->save($body['email'], $body['password']);
        } catch (DuplicateEmailException) {
            $validator->addError('email', 'Email address is already in use');

            try {
                return $this->twig->render(
                    Http::UnprocessableEntity($response),
                    $this->paths->user('register.twig'),
                    [
                        'errors' => $validator->errors(),
                        'old' => $validator->old(),
                    ]
                );
            } catch (LoaderError|RuntimeError|SyntaxError $e) {
                throw new HttpInternalServerErrorException($request, $e->getMessage(), $e);
            }
        } catch (DatabaseException $e) {
            throw new HttpInternalServerErrorException($request, $e->getMessage(), $e);
        }

        // We use the session to put a success flash message
        $this->session->getFlash()
            ->add('flash', 'Registration successful!');

        // And redirect to the login page
        return $this->responses->redirect($response, '/users/login');
    }

    public function login(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        try {
            return $this->twig->render(
                Http::OK($response),
                $this->paths->user('login.twig'),
            );
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new HttpInternalServerErrorException($request, $e->getMessage(), $e);
        }
    }

    public function loginPost(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        // Get form data
        $body = $request->getParsedBody();
        if ($body === null || is_object($body)) {
            throw new HttpInternalServerErrorException($request);
        }

        // Validate
        $validator = $this->validators->instance();
        $validator->check($validator->minChars($body['email'], 10),
            'email', 'This field cannot be less than 10 characters');
        $validator->check($validator->matches($body['email'], Validator::EMAIL_PATTERN),
            'email', 'This field must be a valid email address');
        $validator->check($validator->minChars($body['password'], 8),
            'password', 'This field must be at least 8 characters long');

        // Keep Old
        $validator->keepArray(['email' => $body['email']]);

        // Validation check, if failed: render login page with errors and old values
        if (!$validator->valid()) {
            try {
                return $this->twig->render(
                    Http::UnprocessableEntity($response),
                    $this->paths->user('login.twig'),
                    [
                        'errors' => $validator->errors(),
                        'old' => $validator->old(),
                    ]
                );
            } catch (LoaderError|RuntimeError|SyntaxError $e) {
                throw new HttpInternalServerErrorException($request,
                    $e->getMessage(), $e);
            }
        }

        // Log the user in
        try {
            $id = $this->users->check($body['email'], $body['password']);
        } catch (InvalidCredentialsException) {
            $validator->addGeneralError('Email or Password is incorrect');

            try {
                return $this->twig->render(
                    Http::UnprocessableEntity($response),
                    $this->paths->user('login.twig'),
                    [
                        'generalErrors' => $validator->generalErrors(),
                        'old' => $validator->old(),
                    ]
                );
            } catch (LoaderError|RuntimeError|SyntaxError $e) {
                throw new HttpInternalServerErrorException($request,
                    $e->getMessage(), $e);
            }
        } catch (DatabaseException $ex) {
            throw new HttpInternalServerErrorException($request,
                $ex->getMessage(), $ex);
        }

        // Regenerate session id
        try {
            $this->session->regenerateId();
        } catch (SessionException $ex) {
            throw new HttpInternalServerErrorException($request,
                $ex->getMessage(), $ex);
        }

        // Put the userid into the session
        $this->session->set('userID', $id);

        // Redirect
        return $this->responses->redirect($response, '/cards/create');
    }

    public function logoutPost(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        // Regenerate session id
        try {
            $this->session->regenerateId();
        } catch (SessionException $ex) {
            throw new HttpInternalServerErrorException($request,
                $ex->getMessage(), $ex);
        }

        // Remove userId from session, essentially logging the user out
        $this->session->delete('userID');

        // Flash success message from session
        $this->session->getFlash()->add('flash',
            'You have been logged out successfully!');

        // Redirect
        return $this->responses->redirect($response, '/users/login');
    }
}