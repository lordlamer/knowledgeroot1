<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\FileHandling;

use Knowledgeroot\Application\FileHandling\AccessDenied;
use Knowledgeroot\Application\FileHandling\DeleteFile;
use Knowledgeroot\Application\FileHandling\FileError;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteFileAction
{
    public function __construct(
        private readonly DeleteFile $deleteFile,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $pageId = $this->deleteFile->execute((int) $args['id'], $this->session->userId());
        } catch (AccessDenied $e) {
            return $response->withStatus(403);
        } catch (FileError $e) {
            return $response->withStatus(404);
        }

        $this->session->flash($this->translator->_('File was deleted!'));

        $target = $pageId > 0 ? 'page/' . $pageId : 'index.php';

        return $response->withHeader('Location', $this->url->to($target))->withStatus(302);
    }
}
