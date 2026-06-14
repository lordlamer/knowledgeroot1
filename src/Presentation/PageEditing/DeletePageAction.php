<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\PageEditing;

use Knowledgeroot\Application\PageEditing\AccessDenied;
use Knowledgeroot\Application\PageEditing\DeletePage;
use Knowledgeroot\Application\PageEditing\PageNotEmpty;
use Knowledgeroot\Application\PageEditing\PageNotFound;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeletePageAction
{
    public function __construct(
        private readonly DeletePage $deletePage,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $pageId = (int) $args['id'];

        try {
            $parentId = $this->deletePage->execute($pageId, $this->session->userId());
        } catch (PageNotFound $e) {
            return $response->withStatus(404);
        } catch (AccessDenied $e) {
            return $response->withStatus(403);
        } catch (PageNotEmpty $e) {
            $this->session->flash($this->translator->_($e->getMessage()));

            return $response->withHeader('Location', $this->url->to('page/' . $pageId))->withStatus(302);
        }

        $this->session->flash($this->translator->_('Page was deleted!'));

        $target = $parentId > 0 ? 'page/' . $parentId : 'index.php';

        return $response->withHeader('Location', $this->url->to($target))->withStatus(302);
    }
}
