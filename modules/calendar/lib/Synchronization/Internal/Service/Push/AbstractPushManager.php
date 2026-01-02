<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Push;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\Push;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\PushId;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Repository\PushRepository;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\Dto\AbstractPushResponse;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;

abstract class AbstractPushManager implements PushManagerInterface
{
	public function __construct(
		protected readonly PushRepository $pushRepository,
		protected readonly SectionConnectionRepository $sectionConnectionRepository,
	)
	{
	}

	/**
	 * @param Connection $connection
	 *
	 * @return void
	 */
	public function subscribeConnection(Connection $connection): void
	{
	}

	abstract public function subscribeSection(SectionConnection $sectionConnection): void;

	/**
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function resubscribeConnectionFully(Connection $connection): void
	{
		$sectionConnections = $this->sectionConnectionRepository->getActiveByConnection($connection->getId());

		/** @var SectionConnection $sectionConnection */
		foreach ($sectionConnections as $sectionConnection)
		{
			$this->subscribeSection($sectionConnection);
		}

		$this->subscribeConnection($connection);
	}

	/**
	 * @param AbstractPushResponse|null $response
	 * @param Push|null $push
	 * @param SectionConnection $sectionConnection
	 *
	 * @return void
	 *
	 * @throws ObjectException
	 * @throws ArgumentException
	 * @throws PersistenceException
	 */
	protected function addSectionPush(
		?AbstractPushResponse $response,
		?Push $push,
		SectionConnection $sectionConnection
	): void
	{
		if (!$response)
		{
			return;
		}

		if ($push)
		{
			$this->updatePushData($push, $response);

			$this->pushRepository->update($push);
		}
		else
		{
			$push = new Push();

			$push->setId(PushId::buildForSectionConnection($sectionConnection->getId()));

			$this->updatePushData($push, $response);

			$this->pushRepository->add($push);
		}
	}

	/**
	 * @throws ObjectException
	 */
	protected function updatePushData(Push $push, AbstractPushResponse $response): void
	{
		$push
			->setChannelId($response->id)
			->setResourceId($response->resourceId)
			->setExpireDate(new Date($response->getExpirationDateTime()))
		;
	}
}
