<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\ContentEditing;

use Knowledgeroot\Application\ContentEditing\AccessDenied;
use Knowledgeroot\Application\ContentEditing\ContentNotFound;
use Knowledgeroot\Application\ContentEditing\RelocateContent;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RelocateContentAction
{
    public function __construct(
        private readonly RelocateContent $relocateContent,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $target = (int) (((array) $request->getParsedBody())['target'] ?? 0);

        try {
            $pageId = $this->relocateContent->execute((int) $args['id'], $target, $this->session->userId());
        } catch (ContentNotFound $e) {
            return $response->withStatus(404);
        } catch (AccessDenied $e) {
            return $response->withStatus(403);
        }

        $this->session->flash($this->translator->_('content moved'));

        return $response->withHeader('Location', $this->url->to('page/' . $pageId))->withStatus(302);
    }
}
