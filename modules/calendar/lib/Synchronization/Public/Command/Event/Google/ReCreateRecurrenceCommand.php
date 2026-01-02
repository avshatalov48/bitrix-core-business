<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Event\Google;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Synchronization\Internal\Exception\LogicException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\Result;

class ReCreateRecurrenceCommand extends AbstractCommand
{
	public function __construct(public readonly Event $masterEvent)
	{
	}

	/**
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 * @throws LogicException
	 */
	protected function execute(): Result
	{
		(new ReCreateRecurrenceCommandHandler())($this);

		return new Result();
	}
}
