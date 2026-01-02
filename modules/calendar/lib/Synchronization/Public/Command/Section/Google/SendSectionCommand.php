<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Section\Google;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Synchronization\Internal\Exception\PushException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Messenger\Internals\Exception\Broker\SendFailedException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\Result;

class SendSectionCommand extends AbstractCommand
{
	public function __construct(
		public readonly Section $section,
		public readonly bool $subscribeToPush = true
	)
	{
	}

	/**
	 * @throws PushException
	 * @throws SynchronizerException
	 * @throws NotAuthorizedException
	 * @throws ConfigurationException
	 * @throws SendFailedException
	 * @throws PersistenceException
	 */
	protected function execute(): Result
	{
		(new SendSectionCommandHandler())($this);

		return new Result();
	}
}
