<?php

namespace Bitrix\Main\UserField\Access\Permission;

use Bitrix\Main\Access\AccessCode;

class UserFieldPermission extends EO_UserFieldPermission
{
	private $parsedAC;

	public function getMemberPrefix()
	{
		return $this->getParsedAccessCode()->getEntityPrefix();
	}

	public function getMemberId()
	{
		return $this->getParsedAccessCode()->getEntityId();
	}

	private function getParsedAccessCode()
	{
		if (!$this->parsedAC)
		{
			$this->parsedAC = new AccessCode($this->getAccessCode());
		}
		return $this->parsedAC;
	}
}