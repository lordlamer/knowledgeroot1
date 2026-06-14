<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Admin;

use Knowledgeroot\Infrastructure\Config\Config;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class InfoSectionAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Config $config,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $info = [
            'Knowledgeroot' => (string) $this->config->base->version,
            'PHP' => PHP_VERSION,
            'Database adapter' => (string) $this->config->db->adapter,
            'Server' => (string) ($_SERVER['SERVER_SOFTWARE'] ?? ''),
            'Loaded extensions' => implode(', ', get_loaded_extensions()),
        ];

        $response->getBody()->write($this->twig->render('admin/info.html', [
            'active' => 'info',
            'info' => $info,
        ]));

        return $response;
    }
}
