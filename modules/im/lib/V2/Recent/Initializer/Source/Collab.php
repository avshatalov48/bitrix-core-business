<?php

namespace Bitrix\Im\V2\Recent\Initializer\Source;

use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Recent\Initializer\BaseSource;
use Bitrix\Im\V2\Recent\Initializer\InitialiazerResult;
use Bitrix\Im\V2\Recent\Initializer\SourceType;

class Collab extends BaseSource
{
	public function __construct(int $targetId, int $collabChatId)
	{
		parent::__construct($targetId, $collabChatId);
	}

	public static function getType(): SourceType
	{
		return SourceType::Collab;
	}

	protected function getUsersInternal(string $pointer, int $limit): InitialiazerResult
	{
		$lastId = (int)$pointer;
		$query = RelationTable::query()
			->setDistinct()
			->setSelect(['ID', 'USER_ID'])
			->where('CHAT_ID', $this->sourceId)
			->whereNotNull('USER.LAST_ACTIVITY_DATE')
			->setLimit($limit)
			->setOrder(['ID' => 'DESC'])
		;

		if ($lastId)
		{
			$query->where('ID', '<', $lastId);
		}

		return $this->getResultByRaw($query->fetchAll(), $limit);
	}

	private function getResultByRaw(array $raw, int $limit): InitialiazerResult
	{
		$selectedItemsCount = count($raw);
		$userIds = [];
		$nextId = null;
		$hasNextStep = $selectedItemsCount >= $limit;

		foreach ($raw as $row)
		{
			$id = (int)($row['ID']);
			$userId = (int)($row['USER_ID'] ?? 0);
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
