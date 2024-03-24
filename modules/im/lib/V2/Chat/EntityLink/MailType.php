<?php

namespace Bitrix\Im\V2\Chat\EntityLink;

use Bitrix\Im\V2\Chat\EntityLink;
use Bitrix\Main\Loader;

class MailType extends EntityLink
{
	protected const HAS_URL = true;

	protected function getUrl(): string
	{
		if (!Loader::includeModule('mail'))
		{
			return '';
		}

		return \Bitrix\Mail\Integration\Intranet\Secretary::getMessageUrlForChat((int)$this->entityId, $this->chatId) ?? '';
	}
}