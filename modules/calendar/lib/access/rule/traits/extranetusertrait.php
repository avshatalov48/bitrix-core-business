<?php

namespace Bitrix\Calendar\Access\Rule\Traits;

use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Model\UserModel;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Main\Access\User\AccessibleUser;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\UserToGroupTable;

trait ExtranetUserTrait
{
	private function canSeeOwnerIfExtranetUser(SectionModel $sectionModel, AccessibleUser $userModel): bool
	{
		$result = true;
		if ($userModel->isExtranetUser() && Loader::includeModule('socialnetwork'))
		{
			if (
				$sectionModel->getType() === Dictionary::CALENDAR_TYPE['user']
				&& $sectionModel->getOwnerId() !== $userModel->getUserId()
			)
			{
				$result = false;
			}
			elseif ($sectionModel->getType() === Dictionary::CALENDAR_TYPE['group'])
			{
				$userRole = \CSocNetUserToGroup::GetUserRole($userModel->getUserId(), $sectionModel->getOwnerId());

				$result = $userRole && in_array($userRole, UserToGroupTable::getRolesMember(), true);
			}
			elseif($sectionModel->getType() === Dictionary::CALENDAR_TYPE['company'])
			{
				$result = false;
			}
		}

		return $result;
	}
}
