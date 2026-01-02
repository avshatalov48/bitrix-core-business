<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Section\Office365;

use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push\SubscribeSectionMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Queue;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Office365SectionSynchronizer;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Messenger\Entity\ProcessingParam\ItemIdParam;
use Bitrix\Main\Messenger\Internals\Exception\Broker\SendFailedException;
use Bitrix\Main\Repository\Exception\PersistenceException;

class SendSectionCommandHandler
{
	private Office365SectionSynchronizer $sectionSynchronizer;

	public function __construct()
	{
		$this->sectionSynchronizer = ServiceLocator::getInstance()->get(Office365SectionSynchronizer::class);
	}

	/**
	 * @throws ConfigurationException
	 * @throws PersistenceException
	 * @throws SendFailedException
	 * @throws SynchronizerException
	 */
	public function __invoke(SendSectionCommand $command): void
	{
		$this->sectionSynchronizer->sendSection($command->section);

		if ($command->subscribeToPush)
		{
			$message = new SubscribeSectionMessage($command->section->getId());

			$message->send(Queue::Office365Push->value, [new ItemIdParam($command->section->getId())]);
		}
	}
}
