<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\PageEditing;

use Knowledgeroot\Application\PageEditing\AccessDenied;
use Knowledgeroot\Application\PageEditing\CreatePage;
use Knowledgeroot\Application\PageEditing\InvalidPageData;
use Knowledgeroot\Application\PageEditing\PageFormData;
use Knowledgeroot\Application\PageEditing\PageNotFound;
use Knowledgeroot\Application\PageEditing\UpdatePage;
use Knowledgeroot\Domain\Group\GroupRepository;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

/**
 * Persists the page editor form (create root / create child / update).
 */
class SavePageAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly CreatePage $createPage,
        private readonly UpdatePage $updatePage,
        private readonly GroupRepository $groups,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $editing = isset($args['id']);
        $body = (array) $request->getParsedBody();
        $userId = $this->session->userId();
        $canEditRights = $this->session->canEditRights();
        $parentId = isset($args['parentId']) ? (int) $args['parentId'] : 0;

        $form = new PageFormData(
            title: trim((string) ($body['title'] ?? '')),
            tooltip: trim((string) ($body['tooltip'] ?? '')),
            symlink: (int) ($body['symlink'] ?? 0),
            icon: trim((string) ($body['icon'] ?? '')),
            defaultContentPosition: isset($body['defaultcontentposition']) ? 1 : 0,
            contentCollapsed: isset($body['contentcollapsed']),
            group: (int) ($body['group'] ?? 0),
            userRights: (int) ($body['userrights'] ?? 2),
            groupRights: (int) ($body['grouprights'] ?? 2),
            otherRights: (int) ($body['otherrights'] ?? 2),
        );

        try {
            if ($editing) {
                $pageId = (int) $args['id'];
                $this->updatePage->execute($pageId, $userId, $canEditRights, $form);
                $this->session->flash($this->translator->_('Page was saved!'));
                $target = 'page/' . $pageId;
            } else {
                $newId = $this->createPage->execute($parentId, $userId, $this->session->isAdmin(), $canEditRights, $form);
                $this->session->flash($this->translator->_('Page was created!'));
                $target = 'page/' . $newId;
            }
        } catch (PageNotFound $e) {
            return $response->withStatus(404);
        } catch (AccessDenied $e) {
            return $response->withStatus(403);
        } catch (InvalidPageData $e) {
            $response->getBody()->write($this->twig->render('page/form.html', [
                'editing' => $editing,
                'error' => $this->translator->_($e->getMessage()),
                'page_id' => $editing ? (int) $args['id'] : null,
                'parent_id' => $parentId,
                'form' => [
                    'title' => $form->title, 'tooltip' => $form->tooltip, 'icon' => $form->icon,
                    'symlink' => $form->symlink, 'defaultcontentposition' => $form->defaultContentPosition,
                    'contentcollapsed' => $form->contentCollapsed, 'group' => $form->group,
                    'userrights' => $form->userRights, 'grouprights' => $form->groupRights, 'otherrights' => $form->otherRights,
                ],
                'can_edit_rights' => $canEditRights,
                'all_groups' => $this->groups->findAll(),
            ]));

            return $response->withStatus(422);
        }

        return $response->withHeader('Location', $this->url->to($target))->withStatus(302);
    }
}
