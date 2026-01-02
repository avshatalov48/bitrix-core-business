<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver;

use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push\SubscribeSectionMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\AbstractOffice365Synchronizer;
use Bitrix\Calendar\Synchronization\Public\Command\Push\HandlePushCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Push\Office365\SubscribeSectionCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Push\Office365\SubscribeToPushCommand;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push\PushMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push\SubscribeToPushMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Queue;
use Bitrix\Main\Command\CommandInterface;
use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\Messenger\Internals\Exception\Receiver\UnprocessableMessageException;

class Office365PushReceiver extends AbstractPushReceiver
{
	/**
	 * {@inheritDoc}
	 */
	protected function buildCommand(MessageInterface $message): ?CommandInterface
	{
		if ($message instanceof PushMessage)
		{
			return new HandlePushCommand($message->channelId, $message->resourceId, Queue::Office365Push);
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
				AbstractOffice365Synchronizer::VENDOR_CODE,
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
