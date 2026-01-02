<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Sender;

use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push\PushMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Queue;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\AbstractGoogleSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\AbstractOffice365Synchronizer;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Messenger\Entity\ProcessingParam\ItemIdParam;
use Bitrix\Main\Messenger\Internals\Exception\Broker\SendFailedException;

class PushSender
{
	public function __construct(private readonly RequestLogger $logger)
	{
	}

	/**
	 * @throws ConfigurationException
	 * @throws SendFailedException
	 */
	public function sendGooglePushMessage(string $channelId, string $resourceId): void
	{
		$message = new PushMessage($channelId, $resourceId);

		$message->send(Queue::GooglePush->value, [new ItemIdParam($resourceId)]);

		$this->logger->debug(
			sprintf('Google push received. Channel: %s, Resource ID: %s', $channelId, $resourceId),
			[
				'type' => AbstractGoogleSynchronizer::VENDOR_CODE,
				'entityId' => $resourceId,
				'channelId' => $channelId,
				'resourceId' => $resourceId,
			]
		);
	}

	/**
	 * @throws ConfigurationException
	 * @throws SendFailedException
	 */
	public function sendOffice365PushMessage(string $channelId, string $resourceId): void
	{
		$message = new PushMessage($channelId, $resourceId);

		$message->send(Queue::Office365Push->value, [new ItemIdParam($resourceId)]);

		$this->logger->debug(
			sprintf('Office 365 push received. Channel: %s, Resource ID: %s', $channelId, $resourceId),
			[
				'type' => AbstractOffice365Synchronizer::VENDOR_CODE,
				'entityId' => $resourceId,
				'channelId' => $channelId,
				'resourceId' => $resourceId,
			]
		);
	}
}
