<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\ContentEditing;

use Knowledgeroot\Application\ContentEditing\AccessDenied;
use Knowledgeroot\Application\ContentEditing\ContentNotFound;
use Knowledgeroot\Application\ContentEditing\MoveContent;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MoveContentAction
{
    public function __construct(
        private readonly MoveContent $moveContent,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $direction = $args['direction'] === MoveContent::UP ? MoveContent::UP : MoveContent::DOWN;

        try {
            $pageId = $this->moveContent->execute((int) $args['id'], $this->session->userId(), $direction);
        } catch (ContentNotFound $e) {
            return $response->withStatus(404);
        } catch (AccessDenied $e) {
            return $response->withStatus(403);
        }

        $this->session->flash($this->translator->_('content moved'));

        return $response->withHeader('Location', $this->url->to('page/' . $pageId) . '#' . $args['id'])->withStatus(302);
    }
}
