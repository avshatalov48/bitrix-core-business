<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Event\Office365;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\Result;

class ExcludeEventDateCommand extends AbstractCommand
{
	public function __construct(
		public readonly Event $event,
		public readonly Date $excludedDate,
	)
	{
	}

	/**
	 * @throws RepositoryReadException
	 * @throws PersistenceException
	 * @throws ArgumentException
	 * @throws SynchronizerException
	 */
	protected function execute(): Result
	{
		(new ExcludeEventDateCommandHandler())($this);

		return new Result();
	}
}
