<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Config;

use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Messenger\Internals\BrokerManager;
use Bitrix\Main\Messenger\Internals\Retry\RetryStrategyInterface;
use Bitrix\Main\Messenger\Receiver\ReceiverInterface;
use Bitrix\Main\SystemException;

class QueueConfig
{
	public function __construct(
		public readonly string $queueId,
		public readonly string $handler,
		public readonly string $moduleId,
		public readonly ?string $brokerCode,
		public readonly RetryStrategyInterface $retryStrategy
	)
	{
	}

	/**
	 * @throws ConfigurationException
	 * @throws LoaderException
	 * @throws SystemException
	 */
	public function createReceiver(): ReceiverInterface
	{
		Loader::requireModule($this->moduleId);

		if (!class_exists($this->handler))
		{
			throw new ConfigurationException(
				sprintf('The class "%s" does not exist', $this->handler)
			);
		}

		if (!is_subclass_of($this->handler, ReceiverInterface::class))
		{
			throw new ConfigurationException(
				sprintf('The class "%s" does not implement "%s"', $this->handler, ReceiverInterface::class)
			);
		}

		$brokerManager = ServiceLocator::getInstance()->get(BrokerManager::class);

		/** @var ReceiverInterface $receiver */
		$receiver = ServiceLocator::getInstance()->get($this->handler);

		$receiver
			->setQueueId($this->queueId)
			->setBroker($brokerManager->getBroker($this->queueId))
		;

		return $receiver;
	}
}
