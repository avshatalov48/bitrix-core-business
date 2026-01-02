<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Broker;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Messenger\Broker\BrokerInterface;
use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\Messenger\Entity\MessageBox;
use Bitrix\Main\Messenger\Entity\ProcessingParam\ProcessingParamInterface;
use Bitrix\Main\Messenger\Internals\Exception\Broker\AckFailedException;
use Bitrix\Main\Messenger\Internals\Exception\Broker\BrokerReadException;
use Bitrix\Main\Messenger\Internals\Exception\Broker\RejectFailedException;
use Bitrix\Main\Messenger\Internals\Exception\Broker\SendFailedException;
use Bitrix\Main\Messenger\Internals\Exception\Storage\PersistenceException;
use Bitrix\Main\Messenger\Internals\Config\QueueConfigRegistry;
use Bitrix\Main\Messenger\Internals\Retry\MultiplierRetryStrategy;
use Bitrix\Main\Messenger\Internals\Retry\RetryStrategyInterface;
use Bitrix\Main\Messenger\Internals\Storage\Db\DbStorage;
use Bitrix\Main\Messenger\Internals\Storage\StorageInterface;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\SystemException;

final class DbBroker implements BrokerInterface
{
	public const TYPE_CODE = 'db';

	private readonly StorageInterface $storage;

	private MultiplierRetryStrategy $retryStrategy;

	public function __construct(
		Entity $tableEntity,
		RetryStrategyInterface $retryStrategy = new MultiplierRetryStrategy()
	)
	{
		$this->storage = new DbStorage($tableEntity);
		$this->retryStrategy = $retryStrategy;

		$this->storage->unlockStaleMessages();
	}

	/**
	 * @param MessageInterface $message
	 * @param string $queueId
	 * @param ProcessingParamInterface[] $params
	 *
	 * @throws SendFailedException
	 * @throws ArgumentException
	 */
	public function send(MessageInterface $message, string $queueId, array $params = []): void
	{
		$retryStrategy = $this->getRetryStrategy($queueId);

		$messageBox = new MessageBox($message);

		$messageBox
			->setQueueId($queueId)
			->setTtl($retryStrategy->getMaxRetryCount())
		;

		$this->addProcessingParams($messageBox, $params);

		try
		{
			$this->retry([$this->storage, 'save'], $messageBox);
		}
		catch (PersistenceException $e)
		{
			throw new SendFailedException($messageBox, $e);
		}
	}

	public function get(string $queueId, int $limit = 50): iterable
	{
		try
		{
			return $this->storage->getReadyMessagesOfQueue($queueId, $limit);
		}
		catch (PersistenceException $e)
		{
			throw new BrokerReadException(
				'Unable to lock messages: ' . $e->getMessage(),
				$e->getCode(),
				$e
			);
		}
		catch (SystemException $e)
		{
			throw new BrokerReadException(
				'Unable to read messages: ' . $e->getMessage(),
				$e->getCode(),
				$e
			);
		}
	}

	public function getOne(string $queueId): ?MessageBox
	{
		try
		{
			return $this->storage->getOneByQueue($queueId);
		}
		catch (PersistenceException $e)
		{
			throw new BrokerReadException(
				'Unable to lock message: ' . $e->getMessage(),
				$e->getCode(),
				$e
			);
		}
		catch (SystemException $e)
		{
			throw new BrokerReadException(
				'Unable to read message: ' . $e->getMessage(),
				$e->getCode(),
				$e
			);
		}
	}

	/**
	 * @throws AckFailedException
	 */
	public function ack(MessageBox $message): void
	{
		try
		{
			$this->retry([$this->storage, 'delete'], $message);
		}
		catch (PersistenceException $e)
		{
			throw new AckFailedException($message, $e);
		}
	}

	/**
	 * @throws RejectFailedException
	 */
	public function reject(MessageBox $message): void
	{
		$message->reject();

		try
		{
			if ($message->isDied())
			{
				$this->retry([$this->storage, 'delete'], $message);

				return;
			}

			$this->retry([$this->storage, 'save'], $message);
		}
		catch (PersistenceException $e)
		{
			throw new RejectFailedException($message, $e);
		}
	}

	/**
	 * @throws PersistenceException
	 */
	private function retry(callable $callable, MessageBox $messageBox): void
	{
		$retry = 1;

		while (true)
		{
			try
			{
				call_user_func($callable, $messageBox);

				return;
			}
			catch (PersistenceException $e)
			{
				if ($retry > $this->retryStrategy->getMaxRetryCount() - 1)
				{
					throw $e;
				}

				usleep($this->retryStrategy->getWaitingTime($retry) * 1000);

				$retry++;
			}
		}
	}

	/**
	 * @param MessageBox $messageBox
	 * @param ProcessingParamInterface[] $params
	 *
	 * @throws ArgumentException
	 */
	private function addProcessingParams(MessageBox $messageBox, array $params): void
	{
		foreach ($params as $param)
		{
			if (!$param instanceof ProcessingParamInterface)
			{
				throw new ArgumentException(
					sprintf('The type of message processing params should be "%s"', ProcessingParamInterface::class)
				);
			}

			$messageBox = $param->applyTo($messageBox);
		}
	}

	private function getRetryStrategy(string $queueId): RetryStrategyInterface
	{
		return ServiceLocator::getInstance()
			->get(QueueConfigRegistry::class)
			->getQueueConfig($queueId)
			->retryStrategy;
	}
}
