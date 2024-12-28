<?php

namespace Bitrix\Socialnetwork\Collab\Integration\IM\Message;

use Bitrix\Main\ObjectException;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageSender;

trait GetMessageSenderTrait
{
	protected function getMessageSender(int $collabId, int $senderId): ?ActionMessageSender
	{
		try
		{
			return new ActionMessageSender($collabId, $senderId);
		}
		catch (ObjectException)
		{
			return null;
		}
	}
}