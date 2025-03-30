<?php

namespace Bitrix\Im\V2\Recent\Initializer\Stage;

use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Recent\Initializer\BaseStage;
use Bitrix\Im\V2\Recent\Initializer\StageType;
use Bitrix\Main\Type\DateTime;

class OtherUsersStage extends BaseStage
{
	public static function getType(): StageType
	{
		return StageType::Other;
	}

	protected function getPullRecipients(array $items): array
	{
		return array_map(static fn (array $item) => $item['USER_ID'], $items);
	}

	protected function getPullParams(array $items): array
	{
		return [
			'items' => [
				[
					'user' => User::getInstance($this->targetId)->toRestFormat(['WITHOUT_ONLINE' => true]),
					'date' => new DateTime(),
				]
			]
		];
	}

	protected function getUsersWithExistingItems(array $users): array
	{
		if (empty($users))
		{
			return [];
		}

		$result = [];
		$raw = RecentTable::query()
			->setSelect(['USER_ID'])
			->whereIn('USER_ID', $users)
			->where('ITEM_TYPE', 'P')
			->where('ITEM_ID', $this->targetId)
			->fetchAll()
		;

		foreach ($raw as $row)
		{
			$id = (int)($row['USER_ID'] ?? 0);
			$result[$id] = $id;
		}

		return $result;
	}

	protected function getItemByTargetAndUser(int $targetUserId, int $otherUserId): array
	{
		return $this->getItem($otherUserId, $targetUserId);
	}
}
