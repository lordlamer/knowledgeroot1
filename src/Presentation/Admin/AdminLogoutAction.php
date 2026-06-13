<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Admin;

use Knowledgeroot\Infrastructure\Admin\AdminGate;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminLogoutAction
{
    public function __construct(
        private readonly AdminGate $gate,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $this->gate->logout();

        return $response->withHeader('Location', $this->url->to('admin/login'))->withStatus(302);
    }
}
