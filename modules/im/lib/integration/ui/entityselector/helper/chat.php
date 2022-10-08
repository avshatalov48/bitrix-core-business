<?php

namespace Bitrix\Im\Integration\UI\EntitySelector\Helper;

class Chat
{
	public static function getSelectorEntityType(array $chat): string
	{
		$entityType = $chat['ENTITY_TYPE'];
		if ($entityType !== '' && $entityType !== null)
		{
			return $entityType;
		}

		$type = $chat['TYPE'];
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