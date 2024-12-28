<?php

namespace Bitrix\Im\V2\Entity\User;

class UserCollaber extends UserExtranet
{
	public function getType(): UserType
	{
		return UserType::COLLABER;
	}
}
