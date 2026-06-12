<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Mail;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;

/**
 * Builds a Symfony Mailer from the [email] section of app.ini
 * (host, port, ssl, auth, username, password). Without a configured
 * host the native transport (PHP mail/sendmail) is used - same
 * behaviour as the old Zend_Mail default transport.
 */
class MailerFactory
{
    /**
     * @param object $config the [email] config section
     */
    public static function fromConfig(object $config): MailerInterface
    {
        $host = (string) ($config->host ?? '');

        if ($host === '') {
            return new Mailer(Transport::fromDsn('native://default'));
        }

        $scheme = ((string) ($config->ssl ?? '')) === 'ssl' ? 'smtps' : 'smtp';

        $dsn = $scheme . '://';

        $username = (string) ($config->username ?? '');
        if ($username !== '') {
            $dsn .= rawurlencode($username) . ':' . rawurlencode((string) ($config->password ?? '')) . '@';
        }

        $dsn .= $host;

        $port = (string) ($config->port ?? '');
        if ($port !== '') {
            $dsn .= ':' . $port;
        }

        return new Mailer(Transport::fromDsn($dsn));
    }
}
