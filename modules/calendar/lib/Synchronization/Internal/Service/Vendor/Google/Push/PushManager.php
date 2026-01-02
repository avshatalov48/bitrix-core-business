<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Push;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\Push;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\PushId;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Repository\PushRepository;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleGatewayProvider;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\AbstractPushManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;

class PushManager extends AbstractPushManager
{
	public function __construct(
		PushRepository $pushRepository,
		SectionConnectionRepository $sectionConnectionRepository,
		protected readonly GoogleGatewayProvider $gatewayProvider
	)
	{
		parent::__construct($pushRepository, $sectionConnectionRepository);
	}

	/**
	 * @param Connection $connection
	 *
	 * @return void
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws DtoValidationException
	 * @throws Exception
	 * @throws LoaderException
	 * @throws NotAuthorizedException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws PersistenceException
	 * @throws SystemException
	 */
	public function subscribeConnection(Connection $connection): void
	{
		$push = $this->pushRepository->getByConnectionId($connection->getId());

		if ($push && !$push->isExpired())
		{
			// Google has no renew method
			return;
		}

		$userId = $connection->getOwner()?->getId();

		if (!$userId)
		{
			return;
		}

		$gateway = $this->gatewayProvider->getPushGateway($connection->getOwner()->getId());

		if (!$gateway)
		{
			return;
		}

		$response = $gateway->addConnectionPush($connection);

		if ($push)
		{
			$this->updatePushData($push, $response);

			$this->pushRepository->update($push);
		}
		else
		{
			$push = new Push();

			$push->setId(PushId::buildForConnection($connection->getId()));

			$this->updatePushData($push, $response);

			$this->pushRepository->add($push);
		}
	}

	/**
	 * @param SectionConnection $sectionConnection
	 *
	 * @return void
	 *
	 * @throws ApiException
	 * @throws Exception
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws PersistenceException
	 * @throws SystemException
	 */
	public function subscribeSection(SectionConnection $sectionConnection): void
	{
		$push = $this->pushRepository->getBySectionConnectionId($sectionConnection->getId());

		if ($push && !$push->isExpired())
		{
			// Google has no renew method
			return;
		}

		if (!$sectionConnection->getSection())
		{
			return;
		}

		$userId = $sectionConnection->getSection()?->getOwner()?->getId();

		if (!$userId)
		{
			return;
		}

		$gateway = $this->gatewayProvider->getPushGateway($userId);

		if (!$gateway)
		{
			return;
		}

		$response = $gateway->addSectionPush($sectionConnection);

		$this->addSectionPush($response, $push, $sectionConnection);
	}
}
