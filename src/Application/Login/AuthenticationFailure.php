<?php

declare(strict_types=1);

namespace Knowledgeroot\Application\Login;

enum AuthenticationFailure
{
    /** wrong user name or password */
    case InvalidCredentials;

    /** too many failed attempts in a row, retry after the configured delay */
    case RetryDelay;

    /** maximum number of failed attempts reached, account is blocked */
    case Blocked;
}
