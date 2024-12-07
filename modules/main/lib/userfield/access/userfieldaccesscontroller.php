<?php

namespace Bitrix\Main\UserField\Access;

use Bitrix\Main\Access\Exception\UnknownActionException;
use Bitrix\Main\Access\User\AccessibleUser;
use Bitrix\Main\Access\BaseAccessController;
use Bitrix\Main\UserField\Access\Model\UserFieldModel;
use Bitrix\Main\UserField\Access\Model\UserModel;
use Bitrix\Main\Access\AccessibleItem;

class UserFieldAccessController extends BaseAccessController
{
	public static function getAccessibleFields($userId, string $action, $itemId = null, $params = null): array
	{
		$userId = (int) $userId;
		$controller = new static($userId);
		return $controller->checkByItemsId($action, $itemId, $params);
	}

	public function checkByItemsId(string $action, iterable $itemId = null, $params = null): array
	{
		$items = $this->loadItems($itemId);
		return $this->massCheck($action, $items, $params);
	}

	protected function loadItems(iterable $itemsId = null): array
	{
		$userFieldModels = [];
		foreach ($itemsId as $itemId){
			$userFieldModels[] = UserFieldModel::createFromId($itemId);
		}

		return $userFieldModels;
	}

	public function massCheck(string $action, array $items = null, $params = null): array
	{
		$ruleName = $this->getRuleName($action);

		if (!$ruleName || !class_exists($ruleName))
		{
			throw new UnknownActionException($action);
		}

		return (new $ruleName($this))->executeMass($items, $params);
	}

	protected function loadItem(int $itemId = null): AccessibleItem
	{
		if ($itemId)
		{
			return UserFieldModel::createFromId($itemId);
		}

		return UserFieldModel::createNew();
	}

	protected function loadUser(int $userId): AccessibleUser
	{
		return UserModel::createFromId($userId);
	}
}
