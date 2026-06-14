<?php

declare(strict_types=1);

namespace Knowledgeroot\Presentation\Admin;

use Knowledgeroot\Application\Admin\InstallExtension;
use Knowledgeroot\Application\Admin\ListExtensions;
use Knowledgeroot\Application\Admin\SetExtensionActive;
use Knowledgeroot\Application\Admin\UninstallExtension;
use Knowledgeroot\Infrastructure\Session\LegacySession;
use Knowledgeroot\Infrastructure\Translation\Translator;
use Knowledgeroot\Presentation\Http\UrlHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class ExtensionsSectionAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ListExtensions $list,
        private readonly InstallExtension $install,
        private readonly UninstallExtension $uninstall,
        private readonly SetExtensionActive $setActive,
        private readonly LegacySession $session,
        private readonly Translator $translator,
        private readonly UrlHelper $url,
    ) {
    }

    public function show(Request $request, Response $response): Response
    {
        $response->getBody()->write($this->twig->render('admin/extensions.html', [
            'active' => 'extensions',
            'extensions' => $this->list->execute(),
            'flashes' => $this->session->consumeFlashes(),
        ]));

        return $response;
    }

    public function action(Request $request, Response $response, array $args): Response
    {
        $keyname = (string) (((array) $request->getParsedBody())['keyname'] ?? '');
        $action = (string) $args['action'];

        $done = match ($action) {
            'install' => $this->install->execute($keyname),
            'uninstall' => $this->uninstall->execute($keyname),
            'enable' => $this->setActive->execute($keyname, true),
            'disable' => $this->setActive->execute($keyname, false),
            default => false,
        };

        $this->session->flash($done
            ? $this->translator->_('Extension updated')
            : $this->translator->_('Could not update extension'));

        return $response->withHeader('Location', $this->url->to('admin/extensions'))->withStatus(302);
    }
}
