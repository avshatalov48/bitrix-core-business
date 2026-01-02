<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionMessage;
use Bitrix\Calendar\Synchronization\Public\Command\Section\ICloud\DeleteSectionCommand;
use Bitrix\Calendar\Synchronization\Public\Command\Section\ICloud\SendSectionCommand;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionCreatedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionDeletedMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\SectionUpdatedMessage;
use Bitrix\Main\Command\CommandInterface;

class ICloudSectionReceiver extends AbstractSectionReceiver
{
	protected function buildSendCommand(Section $section, SectionMessage $message): ?CommandInterface
	{
		if ($message instanceof SectionCreatedMessage || $message instanceof SectionUpdatedMessage)
		{
			return new SendSectionCommand($section);
		}

		return null;
	}

	protected function buildDeleteCommand(SectionDeletedMessage $message): ?CommandInterface
	{
		return new DeleteSectionCommand($message->vendorId, $message->userId);
	}
}
