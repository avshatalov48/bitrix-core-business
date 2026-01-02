<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Push;

use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\EntityType;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\Push;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\EventSynchronizerInterface;
use Bitrix\Calendar\Synchronization\Internal\Service\SectionSynchronizerInterface;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;
use Exception;

abstract class AbstractPushProcessor implements PushProcessorInterface
{
	public function __construct(
		private readonly SectionConnectionRepository $sectionConnectionRepository,
		private readonly PushStorageManager $pushStorageManager,
		private readonly ConnectionProvider $connectionProvider,
		private readonly ConnectionManager $connectionManager,
		private readonly EventSynchronizerInterface $eventSynchronizer,
		private readonly SectionSynchronizerInterface $sectionSynchronizer,
	)
	{
	}

	public function processPush(Push $push): void
	{
		if (!$this->pushStorageManager->setBlockPush($push))
		{
			return;
		}

		try
		{
			if ($push->getEntityType() === EntityType::SectionConnection->value)
			{
				$this->processSectionPush($push);
			}
			elseif ($push->getEntityType() === EntityType::Connection->value)
			{
				$this->processConnectionPush($push);
			}
		}
		catch (\Throwable)
		{}
		finally
		{
			$this->pushStorageManager->setUnblockPush($push);
		}
	}

	/**
	 * @param Push $push
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws PersistenceException
	 * @throws SystemException
	 */
	private function processSectionPush(Push $push): void
	{
		$sectionConnection = $this->sectionConnectionRepository->getById($push->getEntityId());

		if (!$sectionConnection)
		{
			$this->pushStorageManager->deletePush($push);

			return;
		}

		try
		{
			if (!$this->pushStorageManager->lockConnection($sectionConnection->getConnection(), 20))
			{
				return;
			}

			$this->eventSynchronizer->importSectionEvents($sectionConnection);

			$this->connectionManager->updateConnection($sectionConnection->getConnection());

			$this->pushStorageManager->markPushSuccess($push, true);
		}
		catch (Exception)
		{
			$this->pushStorageManager->markPushSuccess($push, false);
		}
		finally
		{
			$this->pushStorageManager->unLockConnection($sectionConnection->getConnection());
		}
	}

	private function processConnectionPush(Push $push): void
	{
		try
		{
			$connection = $this->connectionProvider->getById($push->getEntityId());

			if (!$connection || $connection->isDeleted())
			{
				return;
			}
		}
		catch (ArgumentException)
		{
			return;
		}

		try
		{
			if (!$this->pushStorageManager->lockConnection($connection, 20))
			{
				return;
			}

			$this->sectionSynchronizer->importSections($connection->getOwner()->getId(), $connection->getToken());

			$this->connectionManager->updateConnection($connection);
		}
		catch(\Exception)
		{
		}
		finally
		{
			$this->pushStorageManager->unLockConnection($connection);
		}

	}
}
