<?php

namespace Bitrix\Im\V2\Recent\Initializer\Source;

use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Recent\Initializer\BaseSource;
use Bitrix\Im\V2\Recent\Initializer\InitialiazerResult;
use Bitrix\Im\V2\Recent\Initializer\SourceType;
use Bitrix\Im\V2\Recent\Initializer\Stage;
use Bitrix\Im\V2\Recent\Initializer\StageType;
use Bitrix\Main\ORM\Query\Query;

class Collab extends BaseCollabSource
{
	public static function getType(): SourceType
	{
		return SourceType::Collab;
	}

	protected function getBaseQuery(string $pointer, int $limit): Query
	{
		$lastId = (int)$pointer;
		$query = RelationTable::query()
			->setDistinct()
			->setSelect(['ID', self::USER_ID_FIELD_NAME => 'USER_ID'])
			->where('CHAT_ID', $this->sourceId)
			->whereNotNull('USER.LAST_ACTIVITY_DATE')
			->setLimit($limit)
			->setOrder(['ID' => 'DESC'])
		;

		if ($lastId)
		{
			$query->where('ID', '<', $lastId);
		}

		return $query;
	}

	protected function getResultByRaw(array $raw, int $limit): InitialiazerResult
	{
		$selectedItemsCount = count($raw);
		$userIds = [];
		$nextId = null;
		$hasNextStep = $selectedItemsCount >= $limit;

		foreach ($raw as $row)
		{
			$id = (int)($row['ID']);
			$userId = (int)($row[self::USER_ID_FIELD_NAME] ?? 0);
			$userIds[$userId] = $userId;
			if ($id < $nextId || $nextId === null)
			{
				$nextId = $id;
			}
		}

		return (new InitialiazerResult())
			->setItems($userIds)
			->setNextPointer((string)$nextId)
			->setHasNextStep($hasNextStep)
			->setSelectedItemsCount($selectedItemsCount)
		;
	}
}
