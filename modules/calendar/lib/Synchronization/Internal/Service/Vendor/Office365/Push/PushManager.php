<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Push;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\PushException;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AuthorizationException;
use Bitrix\Calendar\Synchronization\Internal\Repository\PushRepository;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Office365GatewayProvider;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\AbstractPushManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Repository\Exception\PersistenceException;

class PushManager extends AbstractPushManager
{
	public function __construct(
		PushRepository $pushRepository,
		SectionConnectionRepository $sectionConnectionRepository,
		protected readonly Office365GatewayProvider $gatewayProvider
	)
	{
		parent::__construct($pushRepository, $sectionConnectionRepository);
	}

	/**
	 * @throws AuthorizationException
	 * @throws PushException
	 */
	public function subscribeSection(SectionConnection $sectionConnection): void
	{
		if (!$sectionConnection->getSection())
		{
			return;
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$push = $this->pushRepository->getBySectionConnectionId($sectionConnection->getId());

		$userId = $sectionConnection->getSection()?->getOwner()?->getId();

		if (!$userId)
		{
			return;
		}

		$gateway = $this->gatewayProvider->getPushGateway($userId);

		if ($push && !$push->isExpired())
		{
			try
			{
				$response = $gateway->renewPush($push->getResourceId());

				$push->setExpireDate(new Date($response->getExpirationDateTime()));

				$this->pushRepository->update($push);
			}
			catch (DtoValidationException|ApiException|ObjectException $e)
			{
				throw new PushException(
					sprintf('Unable to renew push: "%s" (%s)', $e->getMessage(), $e->getCode()),
					$e->getCode(),
					$e,
					isRecoverable: false,
				);
			}
			catch (PersistenceException $e)
			{
				throw new PushException(
					sprintf('Unable to save push: "%s" (%s)', $e->getMessage(), $e->getCode()),
					$e->getCode(),
					$e,
				);
			}

			return;
		}

		try
		{
			$response = $gateway->addSectionPush($sectionConnection);

			$this->addSectionPush($response, $push, $sectionConnection);
		}
		catch (DtoValidationException|ApiException $e)
		{
			throw new PushException(
				sprintf('Unable to subscribe to section push: "%s" (%s)', $e->getMessage(), $e->getCode()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ArgumentException|ObjectException|PersistenceException $e)
		{
			throw new PushException(
				sprintf('Unable to save push: "%s" (%s)', $e->getMessage(), $e->getCode()),
				$e->getCode(),
				$e,
			);
		}
	}
}
