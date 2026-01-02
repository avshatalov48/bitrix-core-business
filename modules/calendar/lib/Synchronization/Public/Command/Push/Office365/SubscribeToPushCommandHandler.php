<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Push\Office365;

use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Push\PushManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class SubscribeToPushCommandHandler
{
	private PushManager $pushManager;

	public function __construct()
	{
		$locator = ServiceLocator::getInstance();

		$this->pushManager = $locator->get(PushManager::class);
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function __invoke(SubscribeToPushCommand $command): void
	{
		$this->pushManager->resubscribeConnectionFully($command->connection);
	}
}
