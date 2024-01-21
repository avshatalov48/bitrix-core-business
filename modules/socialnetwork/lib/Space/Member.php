<?php

namespace Bitrix\Socialnetwork\Space;

use Bitrix\Socialnetwork\EO_UserToGroup;
use Bitrix\Socialnetwork\UserToGroupTable;

class Member extends EO_UserToGroup
{
	public function isInvited(): bool
	{
		return $this->getRole() === UserToGroupTable::ROLE_REQUEST
			&& $this->getInitiatedByType() === UserToGroupTable::INITIATED_BY_GROUP;
	}

	public function isAwaiting(): bool
	{
		return $this->getRole() === UserToGroupTable::ROLE_REQUEST
			&& $this->getInitiatedByType() === UserToGroupTable::INITIATED_BY_USER;
	}

	public function isMember(): bool
	{
		return in_array(
			$this->getRole(),
			UserToGroupTable::getRolesMember(),
			true
		);
	}
}