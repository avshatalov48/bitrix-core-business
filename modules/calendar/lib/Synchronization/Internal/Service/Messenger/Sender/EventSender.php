<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Sender;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\Messenger\MessageSendingException;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventCreatedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventDateExcludedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventDeletedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\EventUpdatedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\RecreateRecurrenceEventMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Queue;
use Bitrix\Calendar\Synchronization\Internal\Repository\EventConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Entity\EntityCollection;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Messenger\Entity\ProcessingParam\DelayParam;
use Bitrix\Main\Messenger\Entity\ProcessingParam\ItemIdParam;
use Bitrix\Main\Messenger\Internals\Exception\Broker\SendFailedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\SystemException;

class EventSender
{
	public function __construct(
		private readonly EventConnectionRepository $eventConnectionRepository,
		private readonly SectionConnectionRepository $sectionConnectionRepository,
		private readonly Factory $mapperFactory,
		private readonly ConnectionProvider $connectionProvider,
		private readonly RequestLogger $logger,
	)
	{
	}

	/**
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	public function sendCreatedMessage(Event $event, ?string $excludedVendorCode = null): void
	{
		$message = new EventCreatedMessage($event->getId());

		$this->sendMessage($event, $message, $excludedVendorCode);
	}

	/**
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	public function sendUpdatedMessage(Event $event, ?string $excludedVendorCode = null): void
	{
		$message = new EventUpdatedMessage($event->getId());

		$this->sendMessage($event, $message, $excludedVendorCode);
	}

	/**
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	public function sendUpdatedMessageForVendor(Event $event, string $vendorCode): void
	{
		$message = new EventUpdatedMessage($event->getId());

		$this->sendMessageForVendor($event, $message, $vendorCode);
	}

	/**
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	public function sendDeletedMessage(Event $event, array $excludedVendorCodes = []): void
	{
		if (!$event->getOwner())
		{
			return;
		}

		$eventConnections = $this->getEventConnections($event, $excludedVendorCodes);
		$sectionConnectionMap = $this->getSectionConnectionMap($event->getSection(), $excludedVendorCodes);

		/** @var EventConnection $eventConnection */
		foreach ($eventConnections as $eventConnection)
		{
			$vendorCode = $eventConnection->getConnection()->getAccountType();

			$message = new EventDeletedMessage(
				$event->getId(),
				$event->getOwner()->getId(),
				$eventConnection->getVendorEventId(),
				$sectionConnectionMap[$vendorCode]->getVendorSectionId()
			);

			$this->sendMessageForVendor($event, $message, $vendorCode);
		}
	}

	/**
	 * @throws ConfigurationException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function sendInstanceDeletedMessage(Event $event, ?string $excludedVendorCode, Context $context): void
	{
		$eventConnections = $this->getEventConnections($event);
		$sectionConnectionMap = $this->getSectionConnectionMap($event->getSection());

		/** @var EventConnection $eventConnection */
		foreach ($eventConnections as $eventConnection)
		{
			$vendorCode = $eventConnection->getConnection()->getAccountType();

			if ($vendorCode === $excludedVendorCode)
			{
				continue;
			}

			if ($vendorCode === \Bitrix\Calendar\Sync\ICloud\Factory::SERVICE_NAME)
			{
				$this->processICloudInstanceDeletedMessage($event, $vendorCode, $context);

				continue;
			}

			if ($event->isInstance())
			{
				$this->sendDeletedMessageForVendor(
					$event,
					$eventConnection->getVendorEventId(),
					$sectionConnectionMap[$vendorCode]->getVendorSectionId(),
					$vendorCode
				);
			}
			else
			{
				$this->sendDateExcludedMessageForVendor($event, $vendorCode, $context);
			}
		}
	}

	/**
	 * @throws ConfigurationException
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws SystemException
	 */
	private function processICloudInstanceDeletedMessage(Event $event, string $vendorCode, Context $context): void
	{
		if (!$event->isInstance())
		{
			$this->sendDateExcludedMessageForVendor($event, $vendorCode, $context);

			return;
		}

		$masterEvent = $this->mapperFactory
			->getEvent()
			->getMap([
				'=PARENT_ID' => $event->getRecurrenceId(),
				'=OWNER_ID' => (int)$event->getOwner()?->getId(),
			])
			->fetch()
		;

		if ($masterEvent)
		{
			$this->sendDateExcludedMessageForVendor($masterEvent, $vendorCode, $context);
		}
	}

	/**
	 * @param Event $masterEvent The master event of the recurrence events chain
	 * @param string|null $excludedVendorCode
	 *
	 * @return void
	 *
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	public function sendReCreateRecurrenceEventMessage(Event $masterEvent, ?string $excludedVendorCode = null): void
	{
		$excludedVendorCodes = $excludedVendorCode ? [$excludedVendorCode] : [];

		$eventConnections = $this->getEventConnections($masterEvent, $excludedVendorCodes);

		/** @var EventConnection $eventConnection */
		foreach ($eventConnections as $eventConnection)
		{
			$vendorCode = $eventConnection->getConnection()->getAccountType();

			if ($vendorCode === \Bitrix\Calendar\Sync\Icloud\Factory::SERVICE_NAME)
			{
				$message = new EventUpdatedMessage($masterEvent->getId());
			}
			else
			{
				$message = new RecreateRecurrenceEventMessage($masterEvent->getId());
			}

			$this->sendMessageForVendor($masterEvent, $message, $vendorCode);
		}
	}

	/**
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	private function sendMessage(Event $event, EventMessage $message, ?string $excludedVendorCode = null): void
	{
		if (!$event->getOwner())
		{
			return;
		}

		$excludedVendorCodes = $excludedVendorCode ? [$excludedVendorCode] : [];

		try
		{
			$connections = $this->connectionProvider->getActiveConnections(
				$event->getOwner()->getId(),
				'user',
				$this->getVendorCodes($excludedVendorCodes)
			);
		}
		catch (SystemException|LoaderException $e)
		{
			throw new MessageSendingException(
				sprintf('Unable to select user connections: %s""', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}

		foreach ($connections as $connection)
		{
			$vendorCode = $connection->getAccountType();

			$this->sendMessageForVendor($event, $message, $vendorCode);
		}
	}

	/**
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	private function sendMessageForVendor(Event $event, EventMessage $message, string $vendorCode): void
	{
		if ($queue = $this->getQueue($vendorCode))
		{
			$itemIdParam = new ItemIdParam($event->getId());
			$delayParam = new DelayParam(15);

			try
			{
				$message->send($queue->value, [$itemIdParam, $delayParam]);
			}
			catch (SendFailedException $e)
			{
				throw new MessageSendingException(
					sprintf('Unable to send message: %s""', $e->getMessage()),
					$e->getCode(),
					$e
				);
			}
		}
	}

	/**
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	private function sendDeletedMessageForVendor(
		Event $event,
		string $vendorEventId,
		string $vendorSectionId,
		string $vendorCode
	): void
	{
		$message = new EventDeletedMessage(
			$event->getId(),
			$event->getOwner()->getId(),
			$vendorEventId,
			$vendorSectionId
		);

		$this->sendMessageForVendor($event, $message, $vendorCode);
	}

	/**
	 * @throws ConfigurationException
	 * @throws ObjectException
	 * @throws MessageSendingException
	 */
	private function sendDateExcludedMessageForVendor(Event $event, string $vendorCode, Context $context): void
	{
		// For preventing: "Call to a member function toString() on array (0)".
		// Because excludeDate can be an empty array
		// bitrix/services/main/ajax.php?action=calendar.api.calendarajax.excludeRecursionDate
		if (empty($context->sync['excludeDate']))
		{
			$this->logger->warning(
				sprintf('Unable to send date excluded message for event "%d": excludeDate is empty', $event->getId()),
				[
					'entityId' => $event->getId(),
					'type' => 'sender',
					'vendorCode' => $vendorCode,
					'excludeDate' => $context->sync['excludeDate'],
				]
			);

			return;
		}

		$message = new EventDateExcludedMessage(
			$event->getId(),
			$context->sync['excludeDate']->toString()
		);

		$this->sendMessageForVendor($event, $message, $vendorCode);
	}

	private function getQueue(string $vendorCode): ?Queue
	{
		$dictionary = [
			\Bitrix\Calendar\Sync\Google\Factory::SERVICE_NAME => Queue::GoogleEventSync,
			\Bitrix\Calendar\Sync\Icloud\Factory::SERVICE_NAME => Queue::ICloudEventSync,
			\Bitrix\Calendar\Sync\Office365\Factory::SERVICE_NAME => Queue::Office365EventSync,
		];

		return $dictionary[$vendorCode] ?? null;
	}

	private function getEventConnections(Event $event, array $excludedVendorCodes = []): EntityCollection
	{
		return $this->eventConnectionRepository->getActiveByEventAndServices(
			$event->getId(),
			$this->getVendorCodes($excludedVendorCodes)
		);
	}

	/**
	 * @return array<string, SectionConnection>
	 */
	private function getSectionConnectionMap(Section $section, array $excludedVendorCodes = []): array
	{
		$result = [];

		$items = $this->sectionConnectionRepository->getActiveBySectionAndServices(
			$section->getId(),
			$this->getVendorCodes($excludedVendorCodes)
		);

		/** @var SectionConnection $item */
		foreach ($items as $item)
		{
			$result[$item->getConnection()->getVendor()->getCode()] = $item;
		}

		return $result;
	}

	/**
	 * @return string[]
	 */
	private function getVendorCodes(array $excludedVendorCodes = []): array
	{
		$vendorCodes = [
			\Bitrix\Calendar\Sync\Google\Factory::SERVICE_NAME,
			\Bitrix\Calendar\Sync\ICloud\Factory::SERVICE_NAME,
			\Bitrix\Calendar\Sync\Office365\Factory::SERVICE_NAME,
		];

		if ($excludedVendorCodes)
		{
			return array_diff($vendorCodes, $excludedVendorCodes);
		}

		return $vendorCodes;
	}
}
