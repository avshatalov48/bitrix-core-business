<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Entity;

use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Messenger\Entity\ProcessingParam\ProcessingParamInterface;
use Bitrix\Main\Messenger\Internals\Exception\Broker\SendFailedException;
use Bitrix\Main\Messenger\Internals\MessageBus;

abstract class AbstractMessage implements MessageInterface
{
	/**
	 * @param string $queueId
	 * @param ProcessingParamInterface[] $params
	 *
	 * @throws ConfigurationException
	 * @throws SendFailedException
	 */
	public function send(string $queueId, array $params = []): void
	{
		$bus = ServiceLocator::getInstance()->get(MessageBus::class);

		$bus->send($this, $queueId, $params);
	}
}
