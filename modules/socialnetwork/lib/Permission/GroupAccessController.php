<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Permission;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Socialnetwork\Permission\Model\GroupModel;
use Bitrix\Socialnetwork\Permission\Trait\AccessErrorTrait;
use Bitrix\Socialnetwork\Permission\Trait\AccessUserTrait;

class GroupAccessController extends AbstractAccessController
{
	protected static array $cache = [];

	public function getModel(array|Arrayable $data): AccessModelInterface
	{
		return GroupModel::createFromArray($data);
	}

	public function getDictionary(): AccessDictionaryInterface
	{
		return GroupDictionary::getInstance();
	}

	protected function loadItem(int $itemId = null): ?AccessibleItem
	{
		$itemId = (int)$itemId;
		if ($itemId === 0)
		{
			return new GroupModel();
		}

		$key = 'GROUP_' . $itemId;
		if (!isset(static::$cache[$key]))
		{
			static::$cache[$key] = GroupModel::createFromId($itemId);
		}

		return static::$cache[$key];
	}
}