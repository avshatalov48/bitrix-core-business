<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Section\Google;

use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Synchronization\Internal\Exception\PushException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push\SubscribeSectionMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Queue;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleSectionSynchronizer;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Messenger\Entity\ProcessingParam\ItemIdParam;
use Bitrix\Main\Messenger\Internals\Exception\Broker\SendFailedException;
use Bitrix\Main\Repository\Exception\PersistenceException;

class SendSectionCommandHandler
{
	private GoogleSectionSynchronizer $sectionSynchronizer;

	public function __construct()
	{
		$this->sectionSynchronizer = Container::getGoogleSectionSynchronizer();
	}

	/**
	 * @throws ConfigurationException
	 * @throws NotAuthorizedException
	 * @throws PersistenceException
	 * @throws SendFailedException
	 * @throws SynchronizerException
	 * @throws PushException
	 */
	public function __invoke(SendSectionCommand $command): void
	{
		$this->sectionSynchronizer->sendSection($command->section);

		if ($command->subscribeToPush)
		{
			$message = new SubscribeSectionMessage($command->section->getId());

			$message->send(Queue::GooglePush->value, [new ItemIdParam($command->section->getId())]);
		}
	}
}
