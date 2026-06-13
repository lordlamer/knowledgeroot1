<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\ContentEditing;

use Knowledgeroot\Domain\Content\ContentRepository;
use Knowledgeroot\Domain\Group\GroupRepository;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

/**
 * Renders the content editor, both for creating a block on a page
 * (route carries pageId) and for editing an existing one (route carries
 * the content id).
 */
class ContentEditorAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ContentRepository $contents,
        private readonly GroupRepository $groups,
        private readonly LegacySession $session,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $editing = isset($args['id']);

        $form = ['title' => '', 'html' => '', 'group' => 0, 'userrights' => 2, 'grouprights' => 2, 'otherrights' => 2];
        $pageId = (int) ($args['pageId'] ?? 0);
        $contentId = null;

        if ($editing) {
            $content = $this->contents->findEditable((int) $args['id']);
            if ($content === null) {
                return $response->withStatus(404);
            }

            $contentId = $content->id;
            $pageId = $content->pageId;
            $form = [
                'title' => $content->title,
                'html' => $content->html,
                'group' => $content->group,
                'userrights' => $content->userRights,
                'grouprights' => $content->groupRights,
                'otherrights' => $content->otherRights,
            ];
        }

        $response->getBody()->write($this->twig->render('content/form.html', [
            'editing' => $editing,
            'error' => null,
            'page_id' => $pageId,
            'content_id' => $contentId,
            'form' => $form,
            'can_edit_rights' => $this->session->canEditRights(),
            'all_groups' => $this->groups->findAll(),
        ]));

        return $response;
    }
}
