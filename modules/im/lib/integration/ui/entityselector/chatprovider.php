<?php
namespace Bitrix\Im\Integration\UI\EntitySelector;

use Bitrix\Im\Chat;
use Bitrix\Im\Model\ChatIndexTable;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\EO_Chat;
use Bitrix\Im\Model\EO_Chat_Collection;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\User;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Filter;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class ChatProvider extends BaseProvider
{
	protected const ENTITY_ID = 'im-chat';

	protected const SEARCHABLE_CHAT_TYPES = [
		Chat::TYPE_GROUP,
		Chat::TYPE_OPEN_LINE,
		Chat::TYPE_OPEN,
	];

	protected const MAX_CHATS_IN_SAMPLE = 100;
	protected const MAX_CHATS_IN_RECENT_TAB = 50;

	public function __construct(array $options = [])
	{
		parent::__construct();

		if (isset($options['searchableChatTypes']) && is_array($options['searchableChatTypes']))
		{
			foreach ($options['searchableChatTypes'] as $chatType)
			{
				if (in_array($chatType, self::SEARCHABLE_CHAT_TYPES, true))
				{
					$this->options['searchableChatTypes'][] = $chatType;
				}
			}

			$this->options['searchableChatTypes'] = $options['searchableChatTypes'];
		}

		$this->options['fillDialog'] = true;
		if (isset($options['fillDialog']) && is_bool($options['fillDialog']))
		{
			$this->options['fillDialog'] = $options['fillDialog'];
		}
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$items = $this->getChatItems([
			'searchQuery' => $searchQuery->getQuery(),
			'limit' => self::MAX_CHATS_IN_SAMPLE
		]);

		$isLimitExceeded = self::MAX_CHATS_IN_SAMPLE <= count($items);
		$isTooSmallToken = mb_strlen($searchQuery->getQuery()) < Filter\Helper::getMinTokenSize();
		if ($isLimitExceeded || $isTooSmallToken)
		{
			$searchQuery->setCacheable(false);
		}

		$dialog->addItems($items);
	}

	public function getItems(array $ids): array
	{
		if (!$this->shouldFillDialog())
		{
			return [];
		}

		return $this->getChatItems([
			'chatIds' => $ids,
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

	public function makeChatItems(EO_Chat_Collection $chats, array $options = []): array
	{
		return self::makeItems($chats, array_merge($this->getOptions(), $options));
	}

	public function getChatCollection(array $options = []): EO_Chat_Collection
	{
		$options = array_merge($this->getOptions(), $options);

		return self::getChats($options);
	}

	public static function getChats(array $options = []): EO_Chat_Collection
	{
		$chats = new EO_Chat_Collection();

		if (!isset($options['searchableChatTypes']) || !is_array($options['searchableChatTypes']))
		{
			return $chats;
		}

		global $USER;
		$currentUserId = $USER->getId();

		$query = ChatTable::query();

		$query->addSelect('*');
		$query->addSelect('RELATION.*');
		$query->addSelect('ALIAS.*');

		$query->registerRuntimeField(
			'CHAT_INDEX',
			new Reference(
				'CHAT_INDEX',
				ChatIndexTable::class,
				Join::on('this.ID', 'ref.CHAT_ID'),
			)
		);

		$query->registerRuntimeField(
			'RELATION',
			new Reference(
				'RELATION',
				RelationTable::class,
				Join::on('this.ID', 'ref.CHAT_ID')
					->where('ref.USER_ID', $currentUserId),
			)
		);

		$filter = Query::filter()->logic('or');

		if (self::shouldSearchChatType(Chat::TYPE_GROUP, $options))
		{
			$groupChatFilter =
				Query::filter()
					->logic('and')
					->where('TYPE', '=', Chat::TYPE_GROUP)
					->where('RELATION.USER_ID', '=', $currentUserId)
			;

			if (isset($options['searchQuery']) && $options['searchQuery'] !== '')
			{
				$groupChatFilter->whereLike('CHAT_INDEX.SEARCH_TITLE', $options['searchQuery'] . '%');
			}

			$filter->where($groupChatFilter);
		}
		else
		{
			$query->where('TYPE', '!=', Chat::TYPE_GROUP);
		}

		if (self::shouldSearchChatType(Chat::TYPE_OPEN_LINE, $options))
		{
			$openLineFilter =
				Query::filter()
					->logic('and')
					->where('TYPE', '=', Chat::TYPE_OPEN_LINE)
					->where('RELATION.USER_ID', '=', $currentUserId)
			;

			if (isset($options['searchQuery']) && $options['searchQuery'] !== '')
			{
				$openLineFilter->whereLike('CHAT_INDEX.SEARCH_TITLE', $options['searchQuery'] . '%');
			}

			$filter->where($openLineFilter);
		}
		else
		{
			$query->where('TYPE', '!=', Chat::TYPE_OPEN_LINE);
		}

		if (self::shouldSearchChatType(Chat::TYPE_OPEN, $options))
		{
			$channelFilter =
				Query::filter()
					->logic('and')
					->where('TYPE', '=', Chat::TYPE_OPEN)
			;

			if (isset($options['searchQuery']) && $options['searchQuery'] !== '')
			{
				$channelFilter->whereLike('CHAT_INDEX.SEARCH_TITLE', $options['searchQuery'] . '%');
			}

			$filter->where($channelFilter);
		}
		else
		{
			$query->where('TYPE', '!=', Chat::TYPE_OPEN);
		}

		$query->where($filter);

		if (isset($options['chatIds']) && is_array($options['chatIds']))
		{
			$query->whereIn('ID', $options['chatIds']);
		}

		if (isset($options['order']) && is_array($options['order']))
		{
			$query->setOrder($options['order']);
		}
		else
		{
			$query->setOrder(['LAST_MESSAGE_ID' => 'DESC']);
		}

		$query->setLimit($options['limit']);

		return $query->exec()->fetchCollection();
	}

	public static function makeItems(EO_Chat_Collection $chats, array $options = []): array
	{
		$result = [];
		foreach ($chats as $chat)
		{
			$result[] = self::makeItem($chat, $options);
		}

		return $result;
	}

	public static function makeItem(EO_Chat $chat, array $options = []): Item
	{
		return new Item([
			'id' => $chat->getId(),
			'entityId' => self::ENTITY_ID,
			'entityType' => Helper\Chat::getSelectorEntityType($chat),
			'title' => $chat->getTitle(),
			'avatar' => \CIMChat::GetAvatarImage($chat->getAvatar(), 200, false),
			'customData' => [
				'imChat' => Chat::formatChatData($chat->collectValues()),
			],
		]);
	}

	protected static function shouldSearchChatType(string $chatType, array $options = []): bool
	{
		if (
			!isset($options['searchableChatTypes'])
			|| !is_array($options['searchableChatTypes'])
		)
		{
			return false;
		}

		$isExtranetUserRequest = User::getInstance()->isExtranet();
		if (
			$isExtranetUserRequest
			&& in_array($chatType, [Chat::TYPE_OPEN_LINE, Chat::TYPE_OPEN], true)
		)
		{
			return false;
		}

		return in_array($chatType, $options['searchableChatTypes'], true);
	}

	public function shouldFillDialog(): bool
	{
		return $this->getOption('fillDialog', true);
	}

	public function fillDialog(Dialog $dialog): void
	{
		if (!$this->shouldFillDialog())
		{
			return;
		}

		// Preload chats ('doSearch' method has to have the same filter).
		$preloadedChats = $this->getPreloadedChatsCollection();
		$recentChats = new EO_Chat_Collection();

		// Recent Items
		$recentItems = $dialog->getRecentItems()->getEntityItems(static::ENTITY_ID);
		$recentIds = array_map('intval', array_keys($recentItems));
		$this->fillRecentChats($recentChats, $recentIds, $preloadedChats);

		// Global Recent Items
		if ($recentChats->count() < self::MAX_CHATS_IN_RECENT_TAB)
		{
			$recentGlobalItems = $dialog->getGlobalRecentItems()->getEntityItems(static::ENTITY_ID);
			$recentGlobalIds = [];

			if (!empty($recentGlobalItems))
			{
				$recentGlobalIds = array_map('intval', array_keys($recentGlobalItems));
				$recentGlobalIds = array_values(array_diff($recentGlobalIds, $recentChats->getIdList()));
				$recentGlobalIds = array_slice(
					$recentGlobalIds,
					0,
					self::MAX_CHATS_IN_RECENT_TAB - $recentChats->count()
				);
			}

			$this->fillRecentChats($recentChats, $recentGlobalIds, $preloadedChats);
		}

		// The rest of preloaded chats
		foreach ($preloadedChats as $preloadedChat)
		{
			$recentChats->add($preloadedChat);
		}

		$dialog->addRecentItems($this->makeChatItems($recentChats));
	}

	protected function getPreloadedChatsCollection(): EO_Chat_Collection
	{
		return $this->getChatCollection([
			'order' => ['ID' => 'DESC'],
			'limit' => self::MAX_CHATS_IN_RECENT_TAB
		]);
	}

	private function fillRecentChats(
		EO_Chat_Collection $recentChats,
		array $recentIds,
		EO_Chat_Collection $preloadedChats
	): void
	{
		if (count($recentIds) < 1)
		{
			return;
		}

		$chatIds = array_values(array_diff($recentIds, $preloadedChats->getIdList()));
		if (!empty($chatIds))
		{
			$chats = $this->getChatCollection(['chatIds' => $chatIds]);
			foreach ($chats as $chat)
			{
				$preloadedChats->add($chat);
			}
		}

		foreach ($recentIds as $recentId)
		{
			$chat = $preloadedChats->getByPrimary($recentId);
			if ($chat)
			{
				$recentChats->add($chat);
			}
		}
	}
}