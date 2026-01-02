<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Section\Office365;

use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\Result;

class DeleteSectionCommand extends AbstractCommand
{
	public function __construct(
		public readonly string $vendorId,
		public readonly int $userId,
	)
	{
	}

	/**
	 * @throws SynchronizerException
	 */
	protected function execute(): Result
	{
		(new DeleteSectionCommandHandler())($this);

		return new Result();
	}
}
