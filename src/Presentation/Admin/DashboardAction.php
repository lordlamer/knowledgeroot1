<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class DashboardAction
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $response->getBody()->write($this->twig->render('admin/dashboard.html', [
            'active' => 'dashboard',
        ]));

        return $response;
    }
}
