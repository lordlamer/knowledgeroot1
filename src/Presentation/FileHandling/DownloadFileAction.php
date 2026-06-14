<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\FileHandling;

use Knowledgeroot\Application\FileHandling\DownloadFile;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DownloadFileAction
{
    public function __construct(
        private readonly DownloadFile $downloadFile,
        private readonly LegacySession $session,
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $download = $this->downloadFile->execute((int) $args['id'], $this->session->userId());

        if ($download === null) {
            return $response->withStatus(404);
        }

        // strip characters that could break the header / span directories
        $safeName = str_replace(['"', "\r", "\n", '\\', '/'], '', $download->filename);

        $response->getBody()->write($download->bytes);

        return $response
            ->withHeader('Content-Type', $download->mimeType)
            ->withHeader('Content-Disposition', 'attachment; filename="' . $safeName . '"')
            ->withHeader('Content-Length', (string) strlen($download->bytes))
            ->withHeader('X-Content-Type-Options', 'nosniff');
    }
}
