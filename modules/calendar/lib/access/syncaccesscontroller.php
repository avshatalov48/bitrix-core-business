<?php

namespace Bitrix\Calendar\Access;

use Bitrix\Calendar\Access\Model\SyncModel;
use Bitrix\Calendar\Access\Model\UserModel;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\BaseAccessController;
use Bitrix\Main\Access\User\AccessibleUser;
use Bitrix\Main\Loader;

final class SyncAccessController extends BaseAccessController
{
	public static array $itemCache = [];
	public static array $userCache = [];

	protected function loadItem(int $itemId = null): ?AccessibleItem
	{
		if (!array_key_exists($itemId, self::$itemCache))
		{
			self::$itemCache[$itemId] = $this->getSyncModel($itemId);
		}

		return self::$itemCache[$itemId];
	}

	protected function loadUser(int $userId): AccessibleUser
	{
		if (!array_key_exists($userId, self::$userCache))
		{
			self::$userCache[$userId] = UserModel::createFromId($userId);
		}

		return self::$userCache[$userId];
	}

	private function getSyncModel(int $itemId = null): ?AccessibleItem
	{
		if (empty($itemId) || !Loader::includeModule('dav'))
		{
			return null;
		}

		$syncConnection = \CDavConnection::GetList(
			['ID' => 'ASC'],
			['ID' => $itemId],
		);

		if (!$syncConnection)
		{
			return null;
		}

		return SyncModel::createFromArray($syncConnection);
	}
}
