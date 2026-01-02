<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Event\ICloud;

use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\Result;

class DeleteEventCommand extends AbstractCommand
{
	public function __construct(
		public readonly string $vendorEventId,
		public readonly string $vendorSectionId,
		public readonly int $userId
	)
	{
	}

	public function toArray(): array
	{
		return [
			'vendorEventId' => $this->vendorEventId,
			'vendorSectionId' => $this->vendorSectionId,
			'userId' => $this->userId,
		];
	}

	/**
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 */
	protected function execute(): Result
	{
		(new DeleteEventCommandHandler())($this);

		return new Result();
	}
}
