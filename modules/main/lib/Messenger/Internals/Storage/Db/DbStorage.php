<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Storage\Db;

use Bitrix\Main\Application;
use Bitrix\Main\Messenger\Entity\MessageBox;
use Bitrix\Main\Messenger\Entity\MessageBoxCollection;
use Bitrix\Main\Messenger\Internals\Exception\Storage\PersistenceException;
use Bitrix\Main\Messenger\Internals\Storage\Db\Model\MessageStatus;
use Bitrix\Main\Messenger\Internals\Storage\StorageInterface;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\SystemException;

class DbStorage implements StorageInterface
{
	private const LOCK_KEY = 'queueLock';

	private const LOCK_TIMEOUT = 0;

	private MessageRepository $repository;

	private static bool $locked;

	public function __construct(Entity $tableEntity)
	{
		$this->repository = new MessageRepository($tableEntity);
	}

	public function __destruct()
	{
		$this->unlock();
	}

	/**
	 * @throws PersistenceException
	 * @throws SystemException
	 */
	public function getOneByQueue(string $queueId): ?MessageBox
	{
		if (!$this->lock())
		{
			return null;
		}

		$messageBox = $this->repository->getOneByQueue($queueId);

		if ($messageBox !== null)
		{
			$this->repository->updateStatus(new MessageBoxCollection($messageBox), MessageStatus::Processing);
		}

		return $messageBox;
	}

	/**
	 * @throws PersistenceException
	 * @throws SystemException
	 */
	public function getReadyMessagesOfQueue(string $queueId, int $limit = 500): iterable
	{
		if (!$this->lock())
		{
			return [];
		}

		$messageBoxes = $this->repository->getReadyMessagesOfQueue($queueId, $limit);

		$this->repository->updateStatus($messageBoxes, MessageStatus::Processing);

		return $messageBoxes;
	}

	/**
	 * @throws PersistenceException
	 */
	public function save(MessageBox $messageBox): void
	{
		$this->repository->save($messageBox);
	}

	/**
	 * @throws PersistenceException
	 */
	public function delete(MessageBox $messageBox): void
	{
		$this->repository->delete($messageBox);
	}

	private function lock(): bool
	{
		if (isset(self::$locked))
		{
			return self::$locked;
		}

		return self::$locked = Application::getConnection()->lock(self::LOCK_KEY, self::LOCK_TIMEOUT);
	}

	private function unlock(): void
	{
		Application::getConnection()->unlock(self::LOCK_KEY);

		self::$locked = false;
	}

	/**
	 * @internal
	 */
	public function unlockStaleMessages(): void
	{
		$messageBoxes = $this->repository->getStaleMessages();

		$this->repository->updateStatus($messageBoxes, MessageStatus::New);
	}
}
