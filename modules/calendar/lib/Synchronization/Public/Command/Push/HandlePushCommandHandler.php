<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Push;

use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Queue;
use Bitrix\Calendar\Synchronization\Internal\Repository\PushRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\PushProcessorInterface;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class HandlePushCommandHandler
{
	private PushRepository $pushRepository;

	public function __construct()
	{
		$this->pushRepository = ServiceLocator::getInstance()->get(PushRepository::class);
	}

	/**
	 * @param HandlePushCommand $command
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function __invoke(HandlePushCommand $command): void
	{
		$push = $this->pushRepository->getByChannelAndResource($command->channelId, $command->resourceId);

		if (!$push || $push->isBlocked())
		{
			return;
		}

		if ($pushProcessor = $this->getPushProcessor($command->queue))
		{
			$pushProcessor->processPush($push);
		}
	}

	private function getPushProcessor(Queue $queue): ?PushProcessorInterface
	{
		$class = null;

		if ($queue === Queue::GooglePush)
		{
			$class = \Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Push\PushProcessor::class;
		}

		if ($queue === Queue::Office365Push)
		{
			$class = \Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Push\PushProcessor::class;
		}

		return $class ? ServiceLocator::getInstance()->get($class) : null;
	}
}
