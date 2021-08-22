<?php
namespace Bitrix\Im\Integration\UI\EntitySelector;

use Bitrix\Im\Chat;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;
use Bitrix\UI\EntitySelector\Tab;

class ChatProvider extends BaseProvider
{
	public function __construct(array $options = [])
	{
		parent::__construct();
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$limit = 100;

		$items = $this->getChatItems([
			'searchQuery' => $searchQuery->getQuery(),
			'limit' => $limit
		]);

		$limitExceeded = $limit <= count($items);

		//When the searchQuery is less than 3 chars, Chat::getListParams returns an empty array.
		//In this case, we do not cache the request.
		$getListParamsFindLimit = mb_strlen($searchQuery->getQuery()) < 3;

		if ($limitExceeded || $getListParamsFindLimit)
		{
			$searchQuery->setCacheable(false);
		}

		$dialog->addItems($items);
	}

	public function getItems(array $ids): array
	{
		return $this->getChatItems([
			'chatId' => $ids,
		]);
	}

	public function getSelectedItems(array $ids): array
	{
		return $this->getItems($ids);
	}

	public function getChatItems(array $options = []): array
	{
		return $this->makeChatItems($this->getChatCollection($options), $options);
	}

	public function makeChatItems(array $chats, array $options = []): array
	{
		return self::makeItems($chats, array_merge($this->getOptions(), $options));
	}

	public function getChatCollection(array $options = []): array
	{
		$options = array_merge($this->getOptions(), $options);

		return self::getChats($options);
	}

	public static function getChats(array $options = []): array
	{
		$params = [
			'FILTER' => [
				'SEARCH' => $options['searchQuery'],
			],
		];

		$ormParams = Chat::getListParams($params);
		if (is_null($ormParams))
		{
			return [];
		}

		$ormParams['select'] = [
			'CNT' => new ExpressionField('CNT', 'COUNT(1)'),
		];

		$counter = ChatTable::getList($ormParams)->fetch();

		$result = [];
		if ($counter && $counter['CNT'] > 0)
		{
			$params['ORDER'] = [
				'ID' => 'DESC',
			];

			$result = Chat::getList($params);
		}

		return $result;
	}

	public static function makeItems(array $chats, array $options = []): array
	{
		$result = [];
		foreach ($chats as $chat)
		{
			$result[] = self::makeItem($chat, $options);
		}

		return $result;
	}

	public static function makeItem(array $chat, array $options = []): Item
	{
		return new Item([
			'id' => $chat['ID'],
			'entityId' => 'im-chat',
			'title' => $chat['NAME'],
			'avatar' => $chat['AVATAR'],
			'customData' => $chat,
		]);
	}

	public function fillDialog(Dialog $dialog): void
	{
		$maxChatsInRecentTab = 50;

		// Preload first 50 users ('doSearch' method has to have the same filter).
		$preloadedChats = $this->getChatCollection([
			'order' => ['ID' => 'asc'],
			'limit' => $maxChatsInRecentTab
		]);

		if (count($preloadedChats) < $maxChatsInRecentTab)
		{
			// Turn off the user search
			$entity = $dialog->getEntity('im-chat');
			if ($entity)
			{
				$entity->setDynamicSearch(false);
			}
		}

		$recentChats = [];

		// Recent Items
		$recentItems = $dialog->getRecentItems()->getEntityItems('im-chat');
		$recentIds = array_map('intval', array_keys($recentItems));
		$recentChats = $this->fillRecentChats($recentChats, $recentIds, $preloadedChats);

		// Global Recent Items
		if (count($recentChats) < $maxChatsInRecentTab)
		{
			$recentGlobalItems = $dialog->getGlobalRecentItems()->getEntityItems('im-chat');
			$recentGlobalIds = [];

			if (!empty($recentGlobalItems))
			{
				$recentGlobalIds = array_map('intval', array_keys($recentGlobalItems));
				$recentGlobalIds = array_values(array_diff($recentGlobalIds, array_column($recentChats, 'ID')));
				$recentGlobalIds = array_slice($recentGlobalIds, 0, $maxChatsInRecentTab - $recentChats->count());
			}

			$recentChats = $this->fillRecentChats($recentChats, $recentGlobalIds, $preloadedChats);
		}

		// The rest of preloaded users
		foreach ($preloadedChats as $preloadedChat)
		{
			$recentChats[] = $preloadedChat;
		}

		$dialog->addRecentItems($this->makeChatItems($recentChats));
	}

	private function fillRecentChats(array $recentChats, array $recentIds, array $preloadedChats): array
	{
		if (count($recentIds) < 1)
		{
			return $recentChats;
		}

		$ids = array_values(array_diff($recentIds, array_column($preloadedChats, 'ID')));

		if (!empty($ids))
		{
			$chats = $this->getChatCollection([
				'userId' => $ids,
			]);

			foreach ($chats as $chat)
			{
				$preloadedChats[] = $chat;
			}
		}

		foreach ($recentIds as $recentId)
		{
			$chatIndex = array_search($recentId, array_column($preloadedChats, 'ID'),true);
			if ($chatIndex !== false)
			{
				$recentChats[] = $preloadedChats[$chatIndex];
			}
		}

		return $recentChats;
	}
}