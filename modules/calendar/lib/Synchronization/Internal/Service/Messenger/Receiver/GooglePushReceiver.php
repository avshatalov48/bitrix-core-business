<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver;

use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push\SubscribeSectionMessage;
use Bitrix\Calendar\Synchronization\Public\Command\Push\Google\SubscribeSectionCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Push\Google\SubscribeToPushCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Push\HandlePushCommand;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push\PushMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push\SubscribeToPushMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Queue;
use Bitrix\Main\Command\CommandInterface;
use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\Messenger\Internals\Exception\Receiver\UnprocessableMessageException;

class GooglePushReceiver extends AbstractPushReceiver
{
	/**
	 * {@inheritDoc}
	 */
	protected function buildCommand(MessageInterface $message): ?CommandInterface
	{
		if ($message instanceof PushMessage)
		{
			return new HandlePushCommand($message->channelId, $message->resourceId, Queue::GooglePush);
		}

		if ($message instanceof SubscribeToPushMessage)
		{
			$connection = $this->getConnection($message->connectionId);

			if (!$connection)
			{
				return null;
			}

			return new SubscribeToPushCommand($connection);
		}

		if ($message instanceof SubscribeSectionMessage)
		{
			$sectionConnection = $this->getSectionConnection(
				$message->sectionId,
				\Bitrix\Calendar\Sync\Google\Factory::SERVICE_NAME,
			);

			if (!$sectionConnection)
			{
				return null;
			}

			return new SubscribeSectionCommand($sectionConnection);
		}

		throw new UnprocessableMessageException($message);
	}
}
