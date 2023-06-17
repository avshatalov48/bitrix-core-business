<?php

namespace Bitrix\Socialnetwork\Component\WorkgroupList;

use Bitrix\Main\Entity\Query;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\Helper;

class User
{
	public static function fillUsers(array $params = []): array
	{
		$result = [];

		if (
			!isset($params['groupIdList'])
			|| !is_array($params['groupIdList'])
		)
		{
			return $result;
		}

		$groupIdList = Util::filterNumericIdList($params['groupIdList']);
		if (empty($groupIdList))
		{
			return $result;
		}

		$scrumMasterIdList = ($params['scrumMasterIdList'] ?? []);

		$query = new Query(UserToGroupTable::getEntity());
		$records = $query
			->setSelect([
				'GROUP_ID',
				'USER_ID',
				'ROLE',
				'INITIATED_BY_TYPE',
				'AUTO_MEMBER',
				'NAME' => 'USER.NAME',
				'LAST_NAME' => 'USER.LAST_NAME',
				'SECOND_NAME' => 'USER.SECOND_NAME',
				'LOGIN' => 'USER.LOGIN',
				'PERSONAL_PHOTO' => 'USER.PERSONAL_PHOTO',
			])
			->whereIn('GROUP_ID', $groupIdList)
			->whereIn('ROLE', UserToGroupTable::getRolesMember())
			->exec()->fetchCollection();

		$imageIdList = [];
		foreach ($records as $record)
		{
			$user = $record->get('USER');
			$imageIdList[$record->get('USER_ID')] = $user->get('PERSONAL_PHOTO');
			$members[] = $record;
		}

		$imageIdList = array_filter(
			$imageIdList,
			static function ($id) {
				return (int)$id > 0;
			}
		);

		$avatars = Helper\UI\Grid\Workgroup\Members::getUserAvatars($imageIdList);
		$membersData = [];

		foreach ($members as $member)
		{
			$memberId = (int)$member['USER_ID'];
			$groupId = (int)$member['GROUP_ID'];

			$isScrumProject = isset($scrumMasterIdList[$groupId]);

			$isOwner = ($member['ROLE'] === UserToGroupTable::ROLE_OWNER);
			$isModerator = ($member['ROLE'] === UserToGroupTable::ROLE_MODERATOR);
			$isScrumMaster = ($isScrumProject && $scrumMasterIdList[$groupId] === $memberId);
			$isHead = ($isOwner || $isModerator);

			if (!isset($membersData[$groupId]))
			{
				$membersData[$groupId] = [];
			}

			$membersData[$groupId][($isHead ? 'HEADS' : 'MEMBERS')][$memberId] = [
				'ID' => $memberId,
				'IS_OWNER' => ($isOwner ? 'Y' : 'N'),
				'IS_MODERATOR' => ($isModerator ? 'Y' : 'N'),
				'IS_SCRUM_MASTER' => ($isScrumMaster ? 'Y' : 'N'),
				'IS_AUTO_MEMBER' => $member['AUTO_MEMBER'],
				'PHOTO' => ($avatars[($imageIdList[$memberId] ?? '')] ?? ''),
			];
		}

		foreach ($groupIdList as $groupId)
		{
			$result[$groupId] = [
				'HEADS' => ($membersData[$groupId]['HEADS'] ?? []),
				'MEMBERS' => ($membersData[$groupId]['MEMBERS'] ?? []),
			];
		}

		return $result;
	}
}
