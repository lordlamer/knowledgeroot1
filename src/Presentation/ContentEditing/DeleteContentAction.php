<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\ContentEditing;

use Knowledgeroot\Application\ContentEditing\AccessDenied;
use Knowledgeroot\Application\ContentEditing\ContentNotFound;
use Knowledgeroot\Application\ContentEditing\DeleteContent;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteContentAction
{
    public function __construct(
        private readonly DeleteContent $deleteContent,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $pageId = $this->deleteContent->execute((int) $args['id'], $this->session->userId());
        } catch (ContentNotFound $e) {
            return $response->withStatus(404);
        } catch (AccessDenied $e) {
            return $response->withStatus(403);
        }

        $this->session->flash($this->translator->_('Content was deleted!'));

        return $response->withHeader('Location', $this->url->to('page/' . $pageId))->withStatus(302);
    }
}
