<?php

namespace Bitrix\Im\V2\Notification\Group;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Notification\Group\SystemGroup\SystemGroupItemFactory;
use Bitrix\Im\V2\Notification\Group\UserGroup\UserGroupItemFactory;

class GroupItemFactory
{
	use ContextCustomer;

	public function makeForCurrentContextUser(): GroupItemCollection
	{
		$userId = $this->getContext()->getUserId();
		$isAdmin = $this->getContext()->getUser()->isAdmin();

		$systemItems = (new SystemGroupItemFactory())->makeAllSystemItemsForUser($isAdmin, $userId);
		$userItems = (new UserGroupItemFactory())->makeItemsForUser($userId);

		return new GroupItemCollection(...array_merge($systemItems->getAll(), $userItems->getAll()));
	}
}