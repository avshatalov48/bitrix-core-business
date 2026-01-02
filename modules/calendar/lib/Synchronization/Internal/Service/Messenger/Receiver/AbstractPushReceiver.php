<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver;

use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Main\Command\CommandInterface;
use Bitrix\Main\Command\Exception\CommandException;
use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\Messenger\Internals\Exception\Receiver\UnprocessableMessageException;
use Bitrix\Main\Messenger\Receiver\AbstractReceiver;

abstract class AbstractPushReceiver extends AbstractReceiver
{
	use ExceptionProcessorTrait;

	public function __construct(
		private readonly ConnectionProvider $connectionProvider,
		private readonly SectionConnectionRepository $sectionConnectionRepository
	)
	{
	}

	protected function getConnection(int $connectionId): ?Connection
	{
		return $this->connectionProvider->getById($connectionId);
	}

	protected function getSectionConnection(int $sectionId, string $vendorCode): ?SectionConnection
	{
		$items = $this->sectionConnectionRepository->getActiveBySectionAndServices($sectionId, [$vendorCode]);

		return $items->getIterator()->current();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function process(MessageInterface $message): void
	{
		$command = $this->buildCommand($message);

		if (!$command)
		{
			return;
		}

		try
		{
			$command->run();
		}
		catch (CommandException $e)
		{
			$this->processException($e);
		}
	}

	/**
	 * @throws UnprocessableMessageException
	 */
	abstract protected function buildCommand(MessageInterface $message): ?CommandInterface;
}
