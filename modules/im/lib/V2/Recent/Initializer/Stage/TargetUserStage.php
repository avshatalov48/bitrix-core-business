<?php

namespace Bitrix\Im\V2\Recent\Initializer\Stage;

use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Recent\Initializer\BaseStage;
use Bitrix\Im\V2\Recent\Initializer\InitialiazerResult;
use Bitrix\Im\V2\Recent\Initializer\StageType;
use Bitrix\Main\Type\DateTime;

class TargetUserStage extends BaseStage
{
	public static function getType(): StageType
	{
		return StageType::Target;
	}

	protected function getPullRecipients(array $items): array
	{
		return [$this->targetId];
	}

	protected function getPullParams(array $items): array
	{
		$date = new DateTime();
		$result = ['items' => []];
		foreach ($items as $item)
		{
			$userId = $item['ITEM_ID'];
			$result['items'][] = [
				'user' => User::getInstance($userId)->toRestFormat(['WITHOUT_ONLINE' => true]),
				'date' => $date,
			];
		}

		return $result;
	}

	protected function getUsersWithExistingItems(array $users): array
	{
		$result = [];
		$raw = RecentTable::query()
			->setSelect(['ITEM_ID'])
			->where('USER_ID', $this->targetId)
			->where('ITEM_TYPE', 'P')
			->whereIn('ITEM_ID', $users)
			->fetchAll()
		;

		foreach ($raw as $row)
		{
			$id = (int)($row['ITEM_ID'] ?? 0);
			$result[$id] = $id;
		}

		return $result;
	}

	protected function getItemByTargetAndUser(int $targetUserId, int $otherUserId, DateTime $date): array
	{
		return $this->getItem($targetUserId, $otherUserId, $date);
	}

	protected function hasNextStep(InitialiazerResult $result): bool
	{
		return false;
	}
}
