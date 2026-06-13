<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Admin;

use Knowledgeroot\Infrastructure\Config\Config;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class ConfigSectionAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Config $config,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $response->getBody()->write($this->twig->render('admin/config.html', [
            'active' => 'config',
            'sections' => $this->config->toArray(),
        ]));

        return $response;
    }
}
