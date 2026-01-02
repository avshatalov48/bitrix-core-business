<?php

namespace Bitrix\Im\V2\Notification\Group\SystemGroup;

use Bitrix\Im\V2\Notification\Group\Condition\ConditionFactory;
use Bitrix\Im\V2\Notification\Group\GroupItem;
use Bitrix\Im\V2\Notification\Group\GroupItemCollection;
use Bitrix\Main\Localization\Loc;

class SystemGroupItemFactory
{
	private readonly ConditionFactory $conditionFactory;

	public function __construct()
	{
		$this->conditionFactory = new ConditionFactory();
	}

	private function makeConfirm(int $userId): GroupItem
	{
		return $this->make(SystemGroup::Confirm, $userId);
	}

	private function makeMention(int $userId): GroupItem
	{
		return $this->make(SystemGroup::Mention, $userId);
	}

	private function makeAdmin(int $userId): GroupItem
	{
		return $this->make(SystemGroup::Admin, $userId);
	}

	private function make(SystemGroup $systemGroup, int $userId): GroupItem
	{
		$tag = $systemGroup->value;

		return new GroupItem(
			tag: $tag,
			title: $this->getTitle($systemGroup),
			conditions: $this->conditionFactory->makeByTag($tag, $userId),
			isEditable: false,
		);
	}

	private function getTitle(SystemGroup $systemGroup): string
	{
		return match ($systemGroup)
		{
			SystemGroup::Confirm => Loc::getMessage('IM_V2_NOTIFICATION_GROUP_SYSTEM_GROUP_FACTORY_CONFIRM') ?? '',
			SystemGroup::Mention => Loc::getMessage('IM_V2_NOTIFICATION_GROUP_SYSTEM_GROUP_FACTORY_MENTION') ?? '',
			SystemGroup::Admin => Loc::getMessage('IM_V2_NOTIFICATION_GROUP_SYSTEM_GROUP_FACTORY_ADMIN') ?? '',
		};
	}

	public function makeAllSystemItemsForUser(bool $isAdmin, int $userId): GroupItemCollection
	{
		$groups = [
			$this->makeConfirm($userId),
			$this->makeMention($userId),
		];

		if ($isAdmin)
		{
			$groups[] = $this->makeAdmin($userId);
		}

		return new GroupItemCollection(...$groups);
	}
}