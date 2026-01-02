<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Push;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\Mutex;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\ProcessingStatus;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\Push;
use Bitrix\Calendar\Synchronization\Internal\Exception\PushException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Repository\PushRepository;
use Bitrix\Main\Repository\Exception\PersistenceException;

class PushStorageManager
{
	public function __construct(private readonly PushRepository $pushRepository)
	{
	}

	/**
	 * @throws PushException
	 */
	public function getConnectionPush(int $connectionId): ?Push
	{
		try
		{
			return $this->pushRepository->getByConnectionId($connectionId);
		}
		catch (RepositoryReadException $e)
		{
			throw new PushException(
				sprintf('Unable to get push: "%s"', $e->getMessage()),
				previous: $e
			);
		}
	}

	/**
	 * @throws PushException
	 * @throws RepositoryReadException
	 */
	public function blockConnectionPush(int $connectionId): ?Push
	{
		$push = $this->pushRepository->getByConnectionId($connectionId);

		if ($this->setBlockPush($push))
		{
			return $push;
		}

		return null;
	}

	/**
	 * @throws PushException
	 */
	public function setBlockPush(?Push $push): bool
	{
		if (!$push || $push->isProcessed())
		{
			return false;
		}

		try
		{
			return $this->pushRepository->blockPush($push);
		}
		catch (PersistenceException $e)
		{
			throw new PushException('Unable to block push', $e->getCode(), $e);
		}
	}

	/**
	 * @throws PushException
	 */
	public function setUnblockPush(?Push $push): void
	{
		if (!$push)
		{
			return;
		}

		try
		{
			$this->pushRepository->unBlockPush($push);
		}
		catch (PersistenceException $e)
		{
			throw new PushException('Unable to unblock push', $e->getCode(), $e);
		}
	}

	/**
	 * @param Push $push
	 * @param bool $success
	 *
	 * @return void
	 *
	 * @throws PersistenceException
	 */
	public function markPushSuccess(Push $push, bool $success): void
	{
		if (!$success)
		{
			$push->setProcessingStatus(ProcessingStatus::Unblocked);

			$this->pushRepository->update($push);
		}
		elseif (!$push->getFirstPushDate())
		{
			$push->setFirstPushDate(new Date());

			$this->pushRepository->update($push);
		}
	}

	/**
	 * @param Push $push
	 *
	 * @return void
	 *
	 * @throws PersistenceException
	 */
	public function deletePush(Push $push): void
	{
		$this->pushRepository->delete($push->getId());
	}


	public function lockConnection(Connection $connection, int $time = 30): bool
	{
		return $this->getMutex($connection)->lock($time);
	}

	public function unLockConnection(Connection $connection): bool
	{
		return $this->getMutex($connection)->unlock();
	}

	private function getMutex(Connection $connection): Mutex
	{
		$key = 'lockPushForConnection_' . $connection->getId();

		return new Mutex($key);
	}
}
