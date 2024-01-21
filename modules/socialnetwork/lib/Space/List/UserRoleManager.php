<?php

namespace Bitrix\Socialnetwork\Space\List;

use Bitrix\Socialnetwork\UserToGroupTable;

final class UserRoleManager
{
	public function getUserRole(?string $groupRole, ?string $initiatedBy): string
	{
		$userRole = Dictionary::USER_ROLES['nonMember'];

		$memberRoles = [
			UserToGroupTable::ROLE_OWNER,
			UserToGroupTable::ROLE_MODERATOR,
			UserToGroupTable::ROLE_USER,
		];

		if ($groupRole === UserToGroupTable::ROLE_REQUEST)
		{
			if ($initiatedBy === UserToGroupTable::INITIATED_BY_USER)
			{
				$userRole = Dictionary::USER_ROLES['applicant'];
			}
			elseif ($initiatedBy === UserToGroupTable::INITIATED_BY_GROUP)
			{
				$userRole = Dictionary::USER_ROLES['invited'];
			}
		}
		elseif (in_array($groupRole, $memberRoles))
		{
			$userRole = Dictionary::USER_ROLES['member'];
		}

		return $userRole;
	}
}