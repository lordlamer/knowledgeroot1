<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\ContentEditing;

use Knowledgeroot\Application\ContentEditing\AccessDenied;
use Knowledgeroot\Application\ContentEditing\ContentFormData;
use Knowledgeroot\Application\ContentEditing\ContentNotFound;
use Knowledgeroot\Application\ContentEditing\CreateContent;
use Knowledgeroot\Application\ContentEditing\UpdateContent;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Persists the content editor form, for create (route carries pageId)
 * and update (route carries the content id). On success it redirects to
 * the page; the editor form itself is rendered by ContentEditorAction.
 */
class SaveContentAction
{
    public function __construct(
        private readonly CreateContent $createContent,
        private readonly UpdateContent $updateContent,
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

        $form = new ContentFormData(
            title: trim((string) ($body['title'] ?? '')),
            html: (string) ($body['content'] ?? ''),
            group: (int) ($body['group'] ?? 0),
            userRights: (int) ($body['userrights'] ?? 2),
            groupRights: (int) ($body['grouprights'] ?? 2),
            otherRights: (int) ($body['otherrights'] ?? 2),
        );

        try {
            if ($editing) {
                $pageId = $this->updateContent->execute((int) $args['id'], $userId, $canEditRights, $form);
                $this->session->flash($this->translator->_('Content was saved!'));
            } else {
                $pageId = (int) $args['pageId'];
                $this->createContent->execute($pageId, $userId, $canEditRights, $form);
                $this->session->flash($this->translator->_('Content was created!'));
            }
        } catch (ContentNotFound $e) {
            return $response->withStatus(404);
        } catch (AccessDenied $e) {
            return $response->withStatus(403);
        }

        return $response->withHeader('Location', $this->url->to('page/' . $pageId))->withStatus(302);
    }
}
