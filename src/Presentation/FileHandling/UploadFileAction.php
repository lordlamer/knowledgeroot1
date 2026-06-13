<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\FileHandling;

use Knowledgeroot\Application\FileHandling\AccessDenied;
use Knowledgeroot\Application\FileHandling\FileError;
use Knowledgeroot\Application\FileHandling\UploadedFileData;
use Knowledgeroot\Application\FileHandling\UploadFile;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

class UploadFileAction
{
    public function __construct(
        private readonly UploadFile $uploadFile,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $contentId = (int) $args['id'];
        $pageId = (int) (((array) $request->getParsedBody())['page_id'] ?? 0);
        $uploaded = $request->getUploadedFiles()['file'] ?? null;

        if (!$uploaded instanceof UploadedFileInterface || $uploaded->getError() !== UPLOAD_ERR_OK) {
            $this->session->flash($this->translator->_('No file was uploaded!'));

            return $this->back($response, $pageId);
        }

        $file = new UploadedFileData(
            filename: (string) $uploaded->getClientFilename(),
            mimeType: (string) ($uploaded->getClientMediaType() ?: 'application/octet-stream'),
            size: (int) $uploaded->getSize(),
            bytes: (string) $uploaded->getStream(),
        );

        try {
            $pageId = $this->uploadFile->execute($contentId, $this->session->userId(), $file);
        } catch (AccessDenied $e) {
            return $response->withStatus(403);
        } catch (FileError $e) {
            $this->session->flash($this->translator->_($e->getMessage()));

            return $this->back($response, $pageId);
        }

        $this->session->flash($this->translator->_('File was added!'));

        return $response->withHeader('Location', $this->url->to('page/' . $pageId))->withStatus(302);
    }

    private function back(Response $response, int $pageId): Response
    {
        $target = $pageId > 0 ? 'page/' . $pageId : 'index.php';

        return $response->withHeader('Location', $this->url->to($target))->withStatus(302);
    }
}
