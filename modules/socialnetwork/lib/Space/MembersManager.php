<?php

namespace Bitrix\Socialnetwork\Space;

use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Internals\Member\MemberEntityCollection;
use Bitrix\Socialnetwork\MemberToGroupTable;

final class MembersManager
{
	/**
	 * @param int $spaceId
	 * @param array<int> $usersIds
	 */
	public function updateInvitedUsers(int $spaceId, array $usersIds): void
	{
		$groupInviteCollection = $this->getGroupMembersCollection($spaceId);
		$invitedUsers = [];
		foreach ($groupInviteCollection as $member)
		{
			if ($member->isInvited())
			{
				$invitedUsers[] = $member->getUserId();
			}
		}

		$inviteUsers = array_diff($usersIds, $invitedUsers);
		$rejectInviteUsers = array_diff($invitedUsers, $usersIds);

		$userId = Helper\User::getCurrentUserId();
		$isAdmin = Helper\Workgroup::isCurrentUserModuleAdmin();

		foreach ($inviteUsers as $inviteUserId)
		{
			$canInviteUser = \CSocNetUserPerms::CanPerformOperation($userId, $inviteUserId, 'invitegroup', $isAdmin);

			if ($canInviteUser)
			{
				\CSocNetUserToGroup::SendRequestToJoinGroup(
					$userId,
					$inviteUserId,
					$spaceId,
					'',
				);
			}
		}

		foreach ($rejectInviteUsers as $rejectInviteUserId)
		{
			$userToGroup = [
				'userId' => $rejectInviteUserId,
				'groupId' => $spaceId,
			];

			if (Helper\Workgroup\Access::canDeleteOutgoingRequest($userToGroup))
			{
				Helper\Workgroup::deleteOutgoingRequest($userToGroup);
			}
		}
	}

	public function getGroupMembersList(int $spaceId): array
	{
		$groupMembersList = [];

		$groupInviteCollection = $this->getGroupMembersCollection($spaceId);
		foreach ($groupInviteCollection as $member)
		{
			$groupMembersList[] = [
				'id' => $member->getUserId(),
				'invited' => $member->isInvited(),
				'isAwaiting' => $member->isAwaiting(),
				'isMember' => $member->isMember(),
			];
		}

		return $groupMembersList;
	}

	public function getGroupMembersCollection(int $spaceId): MemberEntityCollection
	{
		return MemberToGroupTable::query()
			->setSelect(['USER_ID', 'ROLE', 'INITIATED_BY_TYPE'])
			->where('GROUP_ID', $spaceId)
			->exec()->fetchCollection();
	}

	public function canInviteUsers(int $spaceId): bool
	{
		$groupPermissions = Helper\Workgroup::getPermissions([
			'groupId' => $spaceId,
		]);

		return $groupPermissions['UserCanInitiate']
			|| $groupPermissions['UserCanModifyGroup']
			|| Helper\Workgroup::isCurrentUserModuleAdmin();
	}

	public function isUserMember(int $userId, int $spaceId): bool
	{
		$groupPermissions = Helper\Workgroup::getPermissions([
			'userId' => $userId,
			'groupId' => $spaceId,
		]);

		return (bool) $groupPermissions['UserIsMember'];
	}
}