<?php

namespace Bitrix\Im\V2\Recent\Initializer\Source;

use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Recent\Initializer\BaseSource;
use Bitrix\Im\V2\Recent\Initializer\InitialiazerResult;
use Bitrix\Im\V2\Recent\Initializer\SourceType;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class Collabs extends BaseSource
{
	protected function getUsersInternal(string $pointer, int $limit): InitialiazerResult
	{
		$lastUserId = (int)$pointer;

		$query = RelationTable::query()
			->setDistinct()
			->setSelect(['OTHER_USER_ID' => 'OTHER.USER_ID'])
			->where('USER_ID', $this->targetId)
			->where('MESSAGE_TYPE', Chat::IM_TYPE_COLLAB)
			->registerRuntimeField($this->getSelfJoin())
			->whereNotNull('OTHER.USER.LAST_ACTIVITY_DATE')
			->setLimit($limit)
			->setOrder(['OTHER_USER_ID' => 'DESC'])
		;

		if ($lastUserId)
		{
			$query->where('OTHER_USER_ID', '<', $lastUserId);
		}

		return $this->getResultByRaw($query->fetchAll(), $limit);
	}

	public static function getType(): SourceType
	{
		return SourceType::Collabs;
	}

	private function getResultByRaw(array $raw, int $limit): InitialiazerResult
	{
		$selectedItemsCount = count($raw);
		$userIds = [];
		$nextId = null;
		$hasNextStep = $selectedItemsCount >= $limit;

		foreach ($raw as $row)
		{
			$userId = (int)($row['OTHER_USER_ID'] ?? 0);
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
