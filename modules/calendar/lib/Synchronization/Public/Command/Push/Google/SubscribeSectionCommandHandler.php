<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Push\Google;

use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Push\PushManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;

class SubscribeSectionCommandHandler
{
	private PushManager $pushManager;

	public function __construct()
	{
		$this->pushManager = ServiceLocator::getInstance()->get(PushManager::class);
	}

	/**
	 * @throws ApiException
	 * @throws DtoValidationException
	 * @throws Exception
	 * @throws NotAuthorizedException
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws PersistenceException
	 * @throws SystemException
	 */
	public function __invoke(SubscribeSectionCommand $command): void
	{
		$this->pushManager->subscribeSection($command->sectionConnection);
	}
}
