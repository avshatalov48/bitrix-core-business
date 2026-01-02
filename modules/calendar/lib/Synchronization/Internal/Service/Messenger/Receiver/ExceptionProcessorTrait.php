<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver;

use Bitrix\Calendar\Synchronization\Internal\Exception\LogicException;
use Bitrix\Calendar\Synchronization\Internal\Exception\NoLogSynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\RateLimitExceededException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Command\Exception\CommandException;
use Bitrix\Main\Messenger\Internals\Exception\Receiver\RecoverableMessageException;
use Bitrix\Main\Messenger\Internals\Exception\Receiver\UnrecoverableMessageException;

trait ExceptionProcessorTrait
{
	/**
	 * @throws CommandException
	 * @throws RecoverableMessageException
	 * @throws UnrecoverableMessageException
	 */
	private function processException(CommandException $e): void
	{
		if ($previous = $e->getPrevious())
		{
			if ($previous instanceof ArgumentException)
			{
				throw new UnrecoverableMessageException($e->getMessage(), $e->getCode(), $e);
			}

			if ($previous instanceof LogicException)
			{
				throw new UnrecoverableMessageException($e->getMessage(), $e->getCode(), $e);
			}

			if ($previous instanceof SynchronizerException)
			{
				if (!$previous->isRecoverable())
				{
					throw new UnrecoverableMessageException($e->getMessage(), $e->getCode(), $e);
				}

				if ($previous instanceof NoLogSynchronizerException)
				{
					throw new RecoverableMessageException($e->getMessage(), $e->getCode(), $e, 300);
				}

				$vendorException = $previous->getPrevious();

				if ($vendorException instanceof RateLimitExceededException)
				{
					throw new RecoverableMessageException($e->getMessage(), $e->getCode(), $e, 60);
				}
			}
		}

		throw $e;
	}
}
