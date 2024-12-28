<?php

declare(strict_types=1);

namespace Bitrix\SocialNetwork\Collab\Access;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Socialnetwork\Permission\AbstractAccessController;
use Bitrix\Socialnetwork\Permission\AccessDictionaryInterface;
use Bitrix\Socialnetwork\Permission\AccessModelInterface;
use Bitrix\SocialNetwork\Collab\Access\Model\CollabModel;

class CollabAccessController extends AbstractAccessController
{
	protected static array $cache = [];

	public function getModel(array|Arrayable $data): AccessModelInterface
	{
		return CollabModel::createFromArray($data);
	}

	public function getDictionary(): AccessDictionaryInterface
	{
		return CollabDictionary::getInstance();
	}

	protected function loadItem(int $itemId = null): ?AccessibleItem
	{
		$itemId = (int)$itemId;
		if ($itemId === 0)
		{
			return new CollabModel();
		}

		$key = 'COLLAB_' . $itemId;
		if (!isset(static::$cache[$key]))
		{
			static::$cache[$key] = CollabModel::createFromId($itemId);
		}

		return static::$cache[$key];
	}
}