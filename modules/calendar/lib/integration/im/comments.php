<?php

namespace Bitrix\Calendar\Integration\Im;

use Bitrix\Im\Model\ChatTable;

class Comments
{
	public static function getCounts(array $messageIds): array
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
		{
			return [];
		}

		$comments = ChatTable::query()
			->setSelect([
				'MESSAGE_COUNT',
				'MESSAGE_ID' => 'PARENT_MID',
			])
			->whereIn('MESSAGE_ID', $messageIds)
			->fetchAll()
		;

		return array_column($comments, 'MESSAGE_COUNT', 'MESSAGE_ID');
	}
}