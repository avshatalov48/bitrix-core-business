<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals;

use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\Messenger\Entity\ProcessingParam\ProcessingParamInterface;
use Bitrix\Main\Messenger\Internals\Exception\Broker\SendFailedException;
use Bitrix\Main\SystemException;

/**
 * @internal
 */
class MessageBus
{
	public function __construct(private readonly BrokerManager $brokerManager)
	{
	}

	/**
	 * @param MessageInterface $message
	 * @param string $queueId
	 * @param ProcessingParamInterface[] $params
	 *
	 * @throws ConfigurationException
	 * @throws SendFailedException
	 * @throws LoaderException
	 * @throws SystemException
	 */
	public function send(MessageInterface $message, string $queueId, array $params = []): void
	{
		$broker = $this->brokerManager->getBroker($queueId);

		$broker->send($message, $queueId, $params);
	}
}
