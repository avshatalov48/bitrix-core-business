<?php
namespace Bitrix\Socialnetwork\Copy\Implement;

use Bitrix\Socialnetwork\Item\UserToGroup as UserToGroup;
use Bitrix\Socialnetwork\UserToGroupTable;

class UserGroupHelper
{
	private $executiveUserId;
	private $moderatorsIds = [];

	public function __construct($executiveUserId, array $moderatorsIds)
	{
		$this->executiveUserId = $executiveUserId;
		$this->moderatorsIds = $moderatorsIds;
	}

	public function changeModerators($groupId)
	{
		$currentModeratorsIds = $this->getCurrentModerators($groupId);

		$addIds = array_diff($this->moderatorsIds, $currentModeratorsIds);
		$deleteIds = array_diff($currentModeratorsIds, $this->moderatorsIds);

		if ($addIds)
		{
			UserToGroup::addModerators([
				"group_id" => $groupId,
				"user_id" => $addIds,
				"current_user_id" => $this->executiveUserId
			]);
		}

		if ($deleteIds)
		{
			$resRelation = UserToGroupTable::getList([
				"filter" => [
					"GROUP_ID" => $groupId,
					"@USER_ID" => $deleteIds
				],
				"select" => ["ID"]
			]);
			while ($relation = $resRelation->fetch())
			{
				\CSocNetUserToGroup::delete($relation["ID"]);
			}
		}
	}

	private function getCurrentModerators($groupId)
	{
		$ids = [];

		$queryObject = UserToGroupTable::getList([
			"filter" => [
				"ROLE" => UserToGroupTable::ROLE_MODERATOR,
				"GROUP_ID" => $groupId,
				"=USER.ACTIVE" => "Y"
			],
			"select" => ["USER_ID"]
		]);
		while ($relation = $queryObject->fetch())
		{
			$ids[] = $relation["USER_ID"];
		}

		return $ids;
	}
}