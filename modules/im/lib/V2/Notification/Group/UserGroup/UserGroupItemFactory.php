<?php

namespace Bitrix\Im\V2\Notification\Group\UserGroup;

use Bitrix\Im\Model\EO_NotifyGroup;
use Bitrix\Im\Model\NotifyGroupTable;
use Bitrix\Im\V2\Notification\Group\Condition\ConditionFactory;
use Bitrix\Im\V2\Notification\Group\GroupItem;
use Bitrix\Im\V2\Notification\Group\GroupItemCollection;

class UserGroupItemFactory
{
	private const MAX_ITEMS_LIMIT = 1000;

	private readonly ConditionFactory $conditionFactory;

	public function __construct()
	{
		$this->conditionFactory = new ConditionFactory();
	}

	public function makeItemsForUser(int $userId): GroupItemCollection
	{
		$groups =  NotifyGroupTable::query()
			->where(NotifyGroupTable::FIELD_USER_ID, $userId)
			->setSelect([
				NotifyGroupTable::FIELD_ID,
				NotifyGroupTable::FIELD_TITLE,
				NotifyGroupTable::RELATION_CONDITIONS,
			])
			->setLimit(self::MAX_ITEMS_LIMIT)
			->addOrder(NotifyGroupTable::FIELD_ID)
			->fetchCollection()
		;

		$groupItems = array_map(
			fn(EO_NotifyGroup $group): GroupItem => new GroupItem(
				tag: (string)$group->getId(),
				title: (string)$group->getTitle(),
				conditions: $this->conditionFactory->convertCollectionToConditionsDto($group->getConditions()),
				isEditable: true
			),
			$groups->getAll(),
		);

		return new GroupItemCollection(...$groupItems);
	}
}