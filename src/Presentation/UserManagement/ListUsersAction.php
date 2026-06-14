<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\UserManagement;

use Knowledgeroot\Application\UserManagement\GetUserOverview;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class ListUsersAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly GetUserOverview $getUserOverview,
        private readonly LegacySession $session,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $overview = $this->getUserOverview->execute();

        $response->getBody()->write($this->twig->render('users/index.html', [
            'users' => $overview->users,
            'groups' => $overview->groups,
            'group_names' => $overview->groupNames,
            'flashes' => $this->session->consumeFlashes(),
        ]));

        return $response;
    }
}
