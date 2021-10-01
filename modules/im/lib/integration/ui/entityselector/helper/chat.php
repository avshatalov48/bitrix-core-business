<?php

namespace Bitrix\Im\Integration\UI\EntitySelector\Helper;

use Bitrix\Im\Model\EO_Chat;

class Chat
{
	public static function getSelectorEntityType(EO_Chat $chat): string
	{
		$entityType = $chat->getEntityType();
		if ($entityType !== '' && $entityType !== null)
		{
			return $entityType;
		}

		$type = $chat->getType();
		switch ($type)
		{
			case \Bitrix\Im\Chat::TYPE_GROUP:
				return 'GROUP';

			case \Bitrix\Im\Chat::TYPE_OPEN:
				return 'CHANNEL';
		}

		return '';
	}
}