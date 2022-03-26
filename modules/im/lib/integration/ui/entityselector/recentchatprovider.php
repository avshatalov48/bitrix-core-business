<?php
namespace Bitrix\Im\Integration\UI\EntitySelector;

use Bitrix\Main\ModuleManager;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\Integration\UI\EntitySelector\UserProvider;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

class RecentChatProvider extends BaseProvider
{
	protected const ITEM_TYPE_CHAT = 'chat';
	protected const ITEM_TYPE_USER = 'user';

	public function __construct(array $options = [])
	{
		parent::__construct();

		if (isset($options['limit']) && is_int($options['limit']))
		{
			$this->options['limit'] = $options['limit'];
		}
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function fillDialog(Dialog $dialog): void
	{
		$recentList = \Bitrix\Im\Recent::getList(null, [
			'LIMIT' => $this->getOption('limit', 50),
			'OFFSET' => 0,
		]);

		$items = (is_array($recentList) && isset($recentList['ITEMS']) && is_array($recentList['ITEMS']))
			? $recentList['ITEMS']
			: []
		;

		$dialog->addRecentItems($this->makeRecentChatItems($items));
	}

	public function makeRecentChatItems(array $items, array $options = []): array
	{
		return self::makeItems($items, array_merge($this->getOptions(), $options));
	}

	public function getItems(array $ids): array
	{
		return [];
	}

	public function getSelectedItems(array $ids): array
	{
		return [];
	}

	public static function makeItems(array $items, array $options = []): array
	{
		$result = [];
		foreach ($items as $item)
		{
			$result[] = static::makeItem($item, $options);
		}

		return $result;
	}

	public static function makeItem(array $item, array $options = []): Item
	{
		$itemOptions = [
			'id' => $item['ID'],
			'entityId' => self::getEntityIdByItem($item),
			'entityType' => self::getEntityTypeByItem($item),
			'title' => $item['TITLE'],
			'avatar' => $item['AVATAR']['URL'],
			'customData' => self::getCustomDataByItem($item),
			'saveable' => false,
		];

		return new Item($itemOptions);
	}

	protected static function getEntityIdByItem(array $item): string
	{
		if ($item['TYPE'] === self::ITEM_TYPE_USER && !$item['USER']['BOT'])
		{
			return $item['TYPE'];
		}

		if ($item['TYPE'] === self::ITEM_TYPE_USER && $item['USER']['BOT'])
		{
			return 'im-bot';
		}

		return 'im-' . $item['TYPE'];
	}

	protected static function getEntityTypeByItem(array $item): string
	{
		if ($item['TYPE'] === self::ITEM_TYPE_CHAT)
		{
			return self::getEntityTypeByChat($item['CHAT']);
		}

		if ($item['TYPE'] === self::ITEM_TYPE_USER)
		{
			return self::getEntityTypeByUser($item['USER']);
		}

		return '';
	}

	protected static function getCustomDataByItem(array $item): array
	{
		$customData = [];
		if ($item['TYPE'] === self::ITEM_TYPE_CHAT)
		{
			$customData['imChat'] = $item['CHAT'] ?: [];

			return $customData;
		}

		if ($item['TYPE'] === self::ITEM_TYPE_USER)
		{
			$customData['imUser'] = $item['USER'] ?: [];

			if ($item['USER'] && $item['USER']['BOT'])
			{
				$customData['imBot'] = \Bitrix\Im\Bot::getCache($item['ID']) ?: [];
			}
		}

		return $customData;
	}

	protected static function getEntityTypeByChat(array $chat): string
	{
		$entityType = $chat['ENTITY_TYPE'];
		if ($entityType !== '' && $entityType !== null)
		{
			return $entityType;
		}

		$type = $chat['MESSAGE_TYPE'];
		switch ($type)
		{
			case \Bitrix\Im\Chat::TYPE_GROUP:
				return 'GROUP';

			case \Bitrix\Im\Chat::TYPE_OPEN:
				return 'CHANNEL';
		}

		return '';
	}

	protected static function getEntityTypeByUser(array $user): string
	{
		if (!$user['ACTIVE'])
		{
			$type = 'inactive';
		}
		else if ($user['EXTERNAL_AUTH_ID'] === 'email')
		{
			$type = 'email';
		}
		else if ($user['EXTERNAL_AUTH_ID'] === 'replica')
		{
			$type = 'network';
		}
		else if (!in_array($user['EXTERNAL_AUTH_ID'], UserTable::getExternalUserTypes(), true))
		{
			if (ModuleManager::isModuleInstalled('intranet'))
			{
				if (UserProvider::isIntegrator($user['ID']))
				{
					$type = 'integrator';
				}
				else
				{
					$ufDepartment = $user['DEPARTMENTS'];
					if (
						empty($ufDepartment)
						|| (
							is_array($ufDepartment)
							&& count($ufDepartment) === 1
							&& (int)$ufDepartment[0] === 0
						)
					)
					{
						$type = 'extranet';
					}
					else
					{
						$type = 'employee';
					}
				}
			}
			else
			{
				$type = 'user';
			}
		}
		else
		{
			$type = 'unknown';
		}

		return $type;
	}
}