<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionCreatedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionDeletedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionUpdatedMessage;
use Bitrix\Main\Command\CommandInterface;
use Bitrix\Main\Command\Exception\CommandException;
use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\Messenger\Internals\Exception\Receiver\UnprocessableMessageException;
use Bitrix\Main\Messenger\Receiver\AbstractReceiver;

abstract class AbstractSectionReceiver extends AbstractReceiver
{
	use ExceptionProcessorTrait;

	public function __construct(protected readonly \Bitrix\Calendar\Core\Mappers\Section $sectionRepository)
	{
	}

	abstract protected function buildSendCommand(Section $section, SectionMessage $message): ?CommandInterface;

	abstract protected function buildDeleteCommand(SectionDeletedMessage $message): ?CommandInterface;

	/**
	 * {@inheritDoc}
	 */
	protected function process(MessageInterface $message): void
	{
		if (!$message instanceof SectionMessage)
		{
			throw new UnprocessableMessageException($message);
		}

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

	private function buildCommand(SectionMessage $message): ?CommandInterface
	{
		if ($message instanceof SectionCreatedMessage || $message instanceof SectionUpdatedMessage)
		{
			$section = $this->sectionRepository->getById($message->sectionId);

			if (!$section || !$section->isActive())
			{
				return null;
			}

			return $this->buildSendCommand($section, $message);
		}

		if ($message instanceof SectionDeletedMessage)
		{
			return $this->buildDeleteCommand($message);
		}

		return null;
	}
}
