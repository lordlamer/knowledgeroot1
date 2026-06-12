<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\UserManagement;

use Knowledgeroot\Domain\Group\GroupRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

/**
 * Renders the group form, for /groups/new as well as /groups/{id}/edit.
 */
class ShowGroupFormAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly GroupRepository $groups,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $group = null;
        if (isset($args['id'])) {
            $group = $this->groups->findById((int) $args['id']);

            if ($group === null) {
                return $response->withStatus(404);
            }
        }

        $response->getBody()->write($this->twig->render('groups/form.html', [
            'mode' => $group === null ? 'add' : 'edit',
            'error' => null,
            'group_id' => $group?->id,
            'name' => $group?->name ?? '',
        ]));

        return $response;
    }
}
