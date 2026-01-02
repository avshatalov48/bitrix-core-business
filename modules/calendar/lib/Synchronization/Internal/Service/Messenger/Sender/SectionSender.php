<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Sender;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\Messenger\MessageSendingException;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionCreatedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionDeletedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionUpdatedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Queue;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Entity\EntityCollection;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Messenger\Entity\ProcessingParam\ItemIdParam;
use Bitrix\Main\Messenger\Internals\Exception\Broker\SendFailedException;
use Bitrix\Main\SystemException;

class SectionSender
{
	public function __construct(
		private readonly SectionConnectionRepository $sectionConnectionRepository,
		private readonly ConnectionProvider $connectionProvider,

	)
	{
	}

	/**
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	public function sendCreatedMessage(Section $section): void
	{
		try
		{
			$connections = $this->connectionProvider->getActiveConnections(
				(int)$section->getOwnerId(),
				'user',
				$this->getVendorCodes()
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

		$message = new SectionCreatedMessage($section->getId());

		foreach ($connections as $connection)
		{
			$this->sendMessageForVendor($section, $message, $connection->getAccountType());
		}
	}

	/**
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	public function sendUpdatedMessage(Section $section): void
	{
		if ($section->isVirtual())
		{
			return;
		}

		$sectionConnections = $this->getSectionConnections($section);

		$message = new SectionUpdatedMessage($section->getId());

		/** @var SectionConnection $sectionConnection */
		foreach ($sectionConnections as $sectionConnection)
		{
			$this->sendMessageForVendor($section, $message, $sectionConnection->getConnection()->getAccountType());
		}
	}

	/**
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	public function sendDeletedMessage(int $sectionId): void
	{
		/** @var Section $section */
		$section = (new \Bitrix\Calendar\Core\Mappers\Section())->getById($sectionId);

		if (!$section || $section->isVirtual())
		{
			return;
		}

		$sectionConnections = $this->getSectionConnections($section);

		/** @var SectionConnection $sectionConnection */
		foreach ($sectionConnections as $sectionConnection)
		{
			$message = new SectionDeletedMessage(
				$section->getId(),
				(int)$section->getOwnerId(),
				$sectionConnection->getVendorSectionId()
			);

			$this->sendMessageForVendor($section, $message, $sectionConnection->getConnection()->getAccountType());
		}
	}

	/**
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 */
	private function sendMessageForVendor(Section $section, SectionMessage $message, string $vendorCode): void
	{
		try
		{
			if ($queue = $this->getQueue($vendorCode))
			{
				$message->send(
					$queue->value,
					[
						new ItemIdParam($section->getId())
					]
				);
			}
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

	private function getQueue(string $vendorCode): ?Queue
	{
		$dictionary = [
			\Bitrix\Calendar\Sync\Google\Factory::SERVICE_NAME => Queue::GoogleSectionSync,
			\Bitrix\Calendar\Sync\ICloud\Factory::SERVICE_NAME => Queue::ICloudSectionSync,
			\Bitrix\Calendar\Sync\Office365\Factory::SERVICE_NAME => Queue::Office365SectionSync,
		];

		return $dictionary[$vendorCode] ?? null;
	}

	private function getSectionConnections(Section $section): EntityCollection
	{
		return $this->sectionConnectionRepository->getActiveBySectionAndServices(
			$section->getId(),
			$this->getVendorCodes()
		);
	}

	/**
	 * @return string[]
	 */
	private function getVendorCodes(): array
	{
		return [
			\Bitrix\Calendar\Sync\Google\Factory::SERVICE_NAME,
			\Bitrix\Calendar\Sync\ICloud\Factory::SERVICE_NAME,
			\Bitrix\Calendar\Sync\Office365\Factory::SERVICE_NAME,
		];
	}
}
