<?php

namespace Bitrix\Socialnetwork\Space\List\Item;

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Helper\AvatarManager;
use Bitrix\Socialnetwork\Internals\Space\Counter;
use Bitrix\Socialnetwork\Space\List\Dictionary;
use Bitrix\Socialnetwork\Space\List\UserRoleManager;

final class Builder
{
	private AvatarManager $avatarManager;
	public function __construct(private int $userId)
	{
		$this->avatarManager = new AvatarManager();
	}

	/** @return array<Space> */
	public function buildSpacesFromQueryResult(array $queryResult): array
	{
		$spaces = [];
		$counter = Counter::getInstance($this->userId);

		foreach ($queryResult as $value)
		{
			$imageId = (int) ($value['IMAGE_ID'] ?? 0);
			if ($imageId)
			{
				$avatar = $this->avatarManager->getImageAvatar($imageId);
			}
			else
			{
				$avatar = $this->avatarManager->getIconAvatar($value['AVATAR_TYPE'] ?? '');
			}

			$visibilityType = Dictionary::SPACE_VISIBILITY_TYPES['open'];
			if ($value['OPENED'] === 'N')
			{
				$visibilityType = Dictionary::SPACE_VISIBILITY_TYPES['closed'];
			}
			if ($value['VISIBLE'] === 'N')
			{
				$visibilityType = Dictionary::SPACE_VISIBILITY_TYPES['secret'];
			}

			$userRole = (new UserRoleManager())->getUserRole($value['ROLE'], $value['ROLE_INIT_BY_TYPE']);

			$spaces[] =
				(new Space())
					->setId($value['ID'])
					->setName($value['NAME'])
					->setIsPinned((int)($value['PIN_ID']) > 0 && $userRole === Dictionary::USER_ROLES['member'])
					->setDateActivity($value['DATE_ACTIVITY'])
					->setAvatar($avatar)
					->setVisibilityType($visibilityType)
					->setCounter($counter->getTotal($value['ID']))
					->setLastActivityDescription('')
					->setUserRole($userRole)
					->setFollow(\CSocNetSubscription::isUserSubscribed($this->userId, 'SG' . $value['ID']))
			;
		}

		return $spaces;
	}

	public function buildCommonSpace(): Space
	{
		$counter = Counter::getInstance($this->userId);

		return
			(new Space())
				->setId(0)
				->setName(Loc::getMessage('SOCIALNETWORK_SPACES_LIST_COMMON_SPACE_NAME'))
				->setIsPinned(false)
				->setDateActivity(new \Bitrix\Main\Type\DateTime())
				->setAvatar($this->avatarManager->getIconAvatar('common-space'))
				->setVisibilityType(Dictionary::SPACE_VISIBILITY_TYPES['open'])
				->setCounter($counter->getTotal(0))
				->setLastActivityDescription('')
				->setUserRole(Dictionary::USER_ROLES['member'])
				->setFollow(true)
			;
	}
}