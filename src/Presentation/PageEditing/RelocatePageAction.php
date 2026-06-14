<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\PageEditing;

use Knowledgeroot\Application\PageEditing\AccessDenied;
use Knowledgeroot\Application\PageEditing\InvalidPageData;
use Knowledgeroot\Application\PageEditing\PageNotFound;
use Knowledgeroot\Application\PageEditing\RelocatePage;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RelocatePageAction
{
    public function __construct(
        private readonly RelocatePage $relocatePage,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $pageId = (int) $args['id'];
        $target = (int) (((array) $request->getParsedBody())['target'] ?? 0);

        try {
            $this->relocatePage->execute($pageId, $target, $this->session->userId(), $this->session->isAdmin());
        } catch (PageNotFound $e) {
            return $response->withStatus(404);
        } catch (AccessDenied $e) {
            return $response->withStatus(403);
        } catch (InvalidPageData $e) {
            $this->session->flash($this->translator->_($e->getMessage()));

            return $response->withHeader('Location', $this->url->to('page/' . $pageId))->withStatus(302);
        }

        $this->session->flash($this->translator->_('Page was saved!'));

        return $response->withHeader('Location', $this->url->to('page/' . $pageId))->withStatus(302);
    }
}
