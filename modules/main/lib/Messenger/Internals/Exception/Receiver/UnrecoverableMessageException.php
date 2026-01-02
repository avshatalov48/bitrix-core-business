<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Exception\Receiver;

use Bitrix\Main\Messenger\Internals\Exception\RuntimeException;

/**
 * If something goes wrong while handling a message and the message should not be retried,
 * a handler can throw this exception
 */
class UnrecoverableMessageException extends RuntimeException
{
}
