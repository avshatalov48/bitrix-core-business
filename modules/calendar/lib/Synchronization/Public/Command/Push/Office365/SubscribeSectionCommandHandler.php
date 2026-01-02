<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Push\Office365;

use Bitrix\Calendar\Synchronization\Internal\Exception\PushException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AuthorizationException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Push\PushManager;
use Bitrix\Main\DI\ServiceLocator;

class SubscribeSectionCommandHandler
{
	private PushManager $pushManager;

	public function __construct()
	{
		$locator = ServiceLocator::getInstance();

		$this->pushManager = $locator->get(PushManager::class);
	}

	/**
	 * @throws PushException
	 * @throws AuthorizationException
	 */
	public function __invoke(SubscribeSectionCommand $command): void
	{
		$this->pushManager->subscribeSection($command->sectionConnection);
	}
}
