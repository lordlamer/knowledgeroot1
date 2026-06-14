<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\PageEditing;

use Knowledgeroot\Domain\Group\GroupRepository;
use Knowledgeroot\Domain\Page\PageRepository;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

/**
 * Renders the page editor: create a child page (route carries
 * parentId), create a root page (no params), or edit an existing page
 * (route carries the page id).
 */
class PageEditorAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly PageRepository $pages,
        private readonly GroupRepository $groups,
        private readonly LegacySession $session,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $editing = isset($args['id']);

        $form = [
            'title' => '', 'tooltip' => '', 'icon' => '', 'symlink' => 0,
            'defaultcontentposition' => 0, 'contentcollapsed' => false,
            'group' => 0, 'userrights' => 2, 'grouprights' => 2, 'otherrights' => 2,
        ];
        $pageId = null;
        $parentId = isset($args['parentId']) ? (int) $args['parentId'] : 0;

        if ($editing) {
            $page = $this->pages->findEditable((int) $args['id']);
            if ($page === null) {
                return $response->withStatus(404);
            }

            $pageId = $page->id;
            $parentId = $page->belongsTo;
            $form = [
                'title' => $page->title,
                'tooltip' => $page->tooltip,
                'icon' => $page->icon,
                'symlink' => $page->symlink,
                'defaultcontentposition' => $page->defaultContentPosition,
                'contentcollapsed' => $page->contentCollapsed,
                'group' => $page->group,
                'userrights' => $page->userRights,
                'grouprights' => $page->groupRights,
                'otherrights' => $page->otherRights,
            ];
        }

        $response->getBody()->write($this->twig->render('page/form.html', [
            'editing' => $editing,
            'error' => null,
            'page_id' => $pageId,
            'parent_id' => $parentId,
            'form' => $form,
            'can_edit_rights' => $this->session->canEditRights(),
            'all_groups' => $this->groups->findAll(),
        ]));

        return $response;
    }
}
