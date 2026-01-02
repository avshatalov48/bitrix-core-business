<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionMessage;
use Bitrix\Calendar\Synchronization\Public\Command\Section\Google\DeleteSectionCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Section\Google\SendSectionCommand;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionCreatedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionDeletedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionUpdatedMessage;
use Bitrix\Main\Command\CommandInterface;

class GoogleSectionReceiver extends AbstractSectionReceiver
{
	protected function buildSendCommand(Section $section, SectionMessage $message): ?CommandInterface
	{
		if ($message instanceof SectionCreatedMessage || $message instanceof SectionUpdatedMessage)
		{
			return new SendSectionCommand($section, $message instanceof SectionCreatedMessage);
		}

		return null;
	}

	protected function buildDeleteCommand(SectionDeletedMessage $message): ?CommandInterface
	{
		return new DeleteSectionCommand($message->vendorId, $message->userId);
	}
}
