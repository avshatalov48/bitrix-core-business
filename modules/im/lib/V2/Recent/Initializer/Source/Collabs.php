<?php

namespace Bitrix\Im\V2\Recent\Initializer\Source;

use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Recent\Initializer\BaseSource;
use Bitrix\Im\V2\Recent\Initializer\InitialiazerResult;
use Bitrix\Im\V2\Recent\Initializer\SourceType;
use Bitrix\Im\V2\Recent\Initializer\Stage;
use Bitrix\Im\V2\Recent\Initializer\StageType;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;

class Collabs extends BaseCollabSource
{
	protected function getBaseQuery(string $pointer, int $limit): Query
	{
		$lastUserId = (int)$pointer;

		$query = RelationTable::query()
			->setDistinct()
			->setSelect([self::USER_ID_FIELD_NAME => 'OTHER.USER_ID'])
			->where('USER_ID', $this->targetId)
			->where('MESSAGE_TYPE', Chat::IM_TYPE_COLLAB)
			->registerRuntimeField($this->getSelfJoin())
			->whereNotNull('OTHER.USER.LAST_ACTIVITY_DATE')
			->setLimit($limit)
			->setOrder([self::USER_ID_FIELD_NAME => 'DESC'])
		;

		if ($lastUserId)
		{
			$query->where(self::USER_ID_FIELD_NAME, '<', $lastUserId);
		}

		return $query;
	}

	public static function getType(): SourceType
	{
		return SourceType::Collabs;
	}

	protected function getResultByRaw(array $raw, int $limit): InitialiazerResult
	{
		$selectedItemsCount = count($raw);
		$userIds = [];
		$nextId = null;
		$hasNextStep = $selectedItemsCount >= $limit;

		foreach ($raw as $row)
		{
			$userId = (int)($row[self::USER_ID_FIELD_NAME] ?? 0);
			$userIds[$userId] = $userId;
			if ($userId < $nextId || $nextId === null)
			{
				$nextId = $userId;
			}
		}

		return (new InitialiazerResult())
			->setItems($userIds)
			->setNextPointer((string)$nextId)
			->setHasNextStep($hasNextStep)
			->setSelectedItemsCount($selectedItemsCount)
		;
	}

	private function getSelfJoin(): Reference
	{
		return new Reference(
			'OTHER',
			RelationTable::class,
			Join::on('this.CHAT_ID', 'ref.CHAT_ID')
				->whereColumn('this.USER_ID', '!=', 'ref.USER_ID')
				->where('this.MESSAGE_TYPE', Chat::IM_TYPE_COLLAB)
				->where('ref.MESSAGE_TYPE', Chat::IM_TYPE_COLLAB)
				->where('this.USER_ID', $this->targetId),
			['join_type' => Join::TYPE_INNER]
		);
	}
}
