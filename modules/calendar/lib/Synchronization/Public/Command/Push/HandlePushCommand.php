<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Push;

use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Queue;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

class HandlePushCommand extends AbstractCommand
{
	public function __construct(
		public readonly string $channelId,
		public readonly string $resourceId,
		public readonly Queue $queue
	)
	{
	}

	/**
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function execute(): Result
	{
		(new HandlePushCommandHandler())($this);

		return new Result();
	}
}
