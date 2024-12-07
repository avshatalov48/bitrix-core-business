<?php

namespace Bitrix\Socialnetwork\Space\List\Item;

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Helper\AvatarManager;
use Bitrix\Socialnetwork\Helper\Workgroup\Access;
use Bitrix\Socialnetwork\Internals\Space\Counter;
use Bitrix\Socialnetwork\Space\List\Dictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Item\RecentActivityData;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Service;
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

			$recentActivityData =
				(new RecentActivityData())
					->setSpaceId($value['ID'])
					->setUserId($this->userId)
					->setTypeId($value['RECENT_ACTIVITY_TYPE_ID'] ?? null)
					->setEntityId($value['RECENT_ACTIVITY_ENTITY_ID'] ?? null)
					->setDateTime($value['RECENT_ACTIVITY_DATE'] ?? null)
					->setSecondaryEntityId($value['RECENT_ACTIVITY_SECONDARY_ENTITY_ID'] ?? null)
			;

			$permissions = [
				'canLeave' => Access::canLeave(['groupId' => $value['ID']])
			];

			$spaces[] =
				(new Space())
					->setId($value['ID'])
					->setName($value['NAME'])
					->setIsPinned((int)($value['PIN_ID']) > 0 && $userRole === Dictionary::USER_ROLES['member'])
					->setAvatar($avatar)
					->setVisibilityType($visibilityType)
					->setCounter($counter->getTotal($value['ID']))
					->setUserRole($userRole)
					->setFollow(\CSocNetSubscription::isUserSubscribed($this->userId, 'SG' . $value['ID']))
					->setRecentActivityData($recentActivityData)
					->setPermissions($permissions)
			;
		}

		return $this->fillRecentActivityDescription($spaces);
	}

	private function fillRecentActivityDescription(array $spaces): array
	{
		$collector = new Collector(Collector::getDefaultProviders());
		/** @var array<Space> $spaces */
		foreach ($spaces as $space)
		{
			$data = $space->getRecentActivityData();
			if ($data instanceof RecentActivityData)
			{
				$collector->addRecentActivityData($data);
			}
		}

		$collector->fillData();

		return $spaces;
	}

	public function buildCommonSpace(): Space
	{
		$counter = Counter::getInstance($this->userId);

		$commonSpace =
			(new Space())
				->setId(0)
				->setName(Loc::getMessage('SOCIALNETWORK_SPACES_LIST_COMMON_SPACE_NAME'))
				->setIsPinned(false)
				->setAvatar($this->avatarManager->getIconAvatar('common-space'))
				->setVisibilityType(Dictionary::SPACE_VISIBILITY_TYPES['open'])
				->setCounter($counter->getTotal(0))
				->setUserRole(Dictionary::USER_ROLES['member'])
				->setFollow(true)
				->setRecentActivityData($this->getCommonSpaceRecentActivityData())
		;

		if ($commonSpace->getRecentActivityData()->getId() <= 0)
		{
			$commonSpace->getRecentActivityData()->setDateTime(new \Bitrix\Main\Type\DateTime());
		}

		return $this->fillRecentActivityDescription([$commonSpace])[0];
	}

	private function getCommonSpaceRecentActivityData(): RecentActivityData
	{
		return (new Service())->get($this->userId, 0);
	}
}