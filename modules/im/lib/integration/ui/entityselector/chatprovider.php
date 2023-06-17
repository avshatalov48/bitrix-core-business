<?php
namespace Bitrix\Im\Integration\UI\EntitySelector;

use Bitrix\Im\Chat;
use Bitrix\Im\Model\ChatIndexTable;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\User;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Filter;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Search\Content;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class ChatProvider extends BaseProvider
{
	protected const ENTITY_ID = 'im-chat';

	protected const MAX_CHATS_IN_SAMPLE = 100;
	protected const MAX_CHATS_IN_RECENT_TAB = 50;


	protected static function getSearchableChatTypes(): array
	{
		return [
			Chat::TYPE_GROUP,
			Chat::TYPE_OPEN_LINE,
			Chat::TYPE_OPEN,
		];
	}

	protected static function getEntityId(): string
	{
		return 'im-chat';
	}

	public function __construct(array $options = [])
	{
		parent::__construct();

		if (isset($options['searchableChatTypes']) && is_array($options['searchableChatTypes']))
		{
			foreach ($options['searchableChatTypes'] as $chatType)
			{
				if (in_array($chatType, static::getSearchableChatTypes(), true))
				{
					$this->options['searchableChatTypes'][] = $chatType;
				}
			}
		}

		$this->options['fillDialog'] = true;
		if (isset($options['fillDialog']) && is_bool($options['fillDialog']))
		{
			$this->options['fillDialog'] = $options['fillDialog'];
		}

		$this->options['fillDialogWithDefaultValues'] = true;
		if (isset($options['fillDialogWithDefaultValues']) && is_bool($options['fillDialogWithDefaultValues']))
		{
			$this->options['fillDialogWithDefaultValues'] = $options['fillDialogWithDefaultValues'];
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
			'limit' => static::MAX_CHATS_IN_SAMPLE
		]);

		$isLimitExceeded = static::MAX_CHATS_IN_SAMPLE <= count($items);
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

	public function makeChatItems(array $chats, array $options = []): array
	{
		return static::makeItems($chats, array_merge($this->getOptions(), $options));
	}

	public function getChatCollection(array $options = []): array
	{
		$options = array_merge($this->getOptions(), $options);

		return static::getChats($options);
	}

	public static function getChats(array $options = []): array
	{
		if (isset($options['chatIds']) && is_array($options['chatIds']))
		{
			return static::getChatsByIds($options);
		}

		$groupChatAndOpenLineIds = static::getChatIds($options, [Chat::TYPE_GROUP, Chat::TYPE_OPEN_LINE]);
		$openChatIds = static::getChatIds($options, [Chat::TYPE_OPEN]);
		$options['chatIds'] = array_merge($groupChatAndOpenLineIds, $openChatIds);

		return static::getChatsByIds($options);
	}

	private static function getChatsByIds(array $options = []): array
	{
		if (empty($options['chatIds']))
		{
			return [];
		}

		$currentUserId = User::getInstance()->getId();

		$query = ChatTable::query();
		$query
			->addSelect('*')
			->addSelect('RELATION.USER_ID', 'RELATION_USER_ID')
			->addSelect('RELATION.NOTIFY_BLOCK', 'RELATION_NOTIFY_BLOCK')
			//->addSelect('RELATION.COUNTER', 'RELATION_COUNTER')
			->addSelect('RELATION.START_COUNTER', 'RELATION_START_COUNTER')
			//->addSelect('RELATION.LAST_ID', 'RELATION_LAST_ID')
			//->addSelect('RELATION.STATUS', 'RELATION_STATUS')
			//->addSelect('RELATION.UNREAD_ID', 'RELATION_UNREAD_ID')
			->addSelect('ALIAS.ALIAS', 'ALIAS_NAME')
		;

		$query->registerRuntimeField(
			'RELATION',
			new Reference(
				'RELATION',
				RelationTable::class,
				Join::on('this.ID', 'ref.CHAT_ID')
					->where('ref.USER_ID', $currentUserId),
			)
		);
		$query->whereIn('ID', $options['chatIds']);
		$query->where(Query::filter()
			->logic('or')
			->where(Query::filter()
				->logic('and')
				->where('TYPE','O')
				->where('USER_COUNT', '>', 0)
			)
			->where('RELATION.USER_ID', $currentUserId)
		);

		if (isset($options['order']) && is_array($options['order']))
		{
			$query->setOrder($options['order']);
		}
		else
		{
			$query->setOrder(['LAST_MESSAGE_ID' => 'DESC']);
		}

		if (isset($options['limit']))
		{
			$query->setLimit($options['limit']);
		}

		$chatsRaw = $query->exec()->fetchAll();

		return Chat::fillCounterData($chatsRaw);
	}

	private static function getChatIds(array $options, array $chatTypes): array
	{
		if (!isset($options['searchableChatTypes']) || !is_array($options['searchableChatTypes']))
		{
			return [];
		}

		$currentUserId = User::getInstance()->getId();

		$query = ChatTable::query();
		$query->addSelect('ID');

		$searchQuery = $options['searchQuery'] ?? '';
		if (self::isValidSearchQuery($searchQuery))
		{
			$query->registerRuntimeField(
				'CHAT_INDEX',
				new Reference(
					'CHAT_INDEX',
					ChatIndexTable::class,
					Join::on('this.ID', 'ref.CHAT_ID'),
					['join_type' => Join::TYPE_INNER]
				)
			);
		}

		$filteredChatTypes = [];
		$relationJoinType = Join::TYPE_INNER;
		if (
			count($chatTypes) === 1
			&& in_array(Chat::TYPE_OPEN, $chatTypes, true)
			&& static::shouldSearchChatType(Chat::TYPE_OPEN, $options)
		)
		{
			$relationJoinType = Join::TYPE_LEFT;
			$filteredChatTypes[] = Chat::TYPE_OPEN;
		}
		$query->registerRuntimeField(
			'RELATION',
			new Reference(
				'RELATION',
				RelationTable::class,
				Join::on('this.ID', 'ref.CHAT_ID')->where('ref.USER_ID', $currentUserId),
				['join_type' => $relationJoinType]
			)
		);

		if (self::isValidSearchQuery($searchQuery))
		{
			$filter = Query::filter()->logic('and');
			static::addFilterBySearchQuery($filter, $searchQuery);
			$query->where($filter);
		}

		$chatTypesFilter = Query::filter()->logic('or');
		if (
			static::shouldSearchChatType(Chat::TYPE_GROUP, $options)
			&& in_array(Chat::TYPE_GROUP, $chatTypes, true)
		)
		{
			$groupChatFilter =
				Query::filter()
					->logic('and')
					->where('TYPE', '=', Chat::TYPE_GROUP)
					->where(Query::filter()
						->logic('or')
						->where('ENTITY_TYPE', '!=', 'SUPPORT24_QUESTION')
						->whereNull('ENTITY_TYPE')
					)
					->where('RELATION.USER_ID', '=', $currentUserId)
			;

			$chatTypesFilter->where($groupChatFilter);
			$filteredChatTypes[] = Chat::TYPE_GROUP;
		}


		if (
			static::shouldSearchChatType(Chat::TYPE_OPEN_LINE, $options)
			&& in_array(Chat::TYPE_OPEN_LINE, $chatTypes, true)
		)
		{
			$openLineFilter =
				Query::filter()
					->logic('and')
					->where('TYPE', '=', Chat::TYPE_OPEN_LINE)
					->where('RELATION.USER_ID', '=', $currentUserId)
			;

			$chatTypesFilter->where($openLineFilter);
			$filteredChatTypes[] = Chat::TYPE_OPEN_LINE;
		}

		if (
			static::shouldSearchChatType(Chat::TYPE_OPEN, $options)
			&& in_array(Chat::TYPE_OPEN, $chatTypes, true)
		)
		{
			$channelFilter =
				Query::filter()
					->logic('and')
					->where('TYPE', '=', Chat::TYPE_OPEN)
			;

			$chatTypesFilter->where($channelFilter);
			$filteredChatTypes[] = Chat::TYPE_OPEN;
		}
		if (empty($filteredChatTypes))
		{
			return [];
		}

		$query->where($chatTypesFilter);

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

		if (isset($options['limit']))
		{
			$query->setLimit($options['limit']);
		}

		$chatIdList = [];
		foreach ($query->exec() as $chat)
		{
			$chatIdList[] = $chat['ID'];
		}

		return $chatIdList;
	}

	public static function makeItems(array $chats, array $options = []): array
	{
		$result = [];
		foreach ($chats as $chat)
		{
			$result[] = static::makeItem($chat, $options);
		}

		return $result;
	}

	public static function makeItem(array $chat, array $options = []): Item
	{
		return new Item([
			'id' => (int)$chat['ID'],
			'entityId' => static::getEntityId(),
			'entityType' => Helper\Chat::getSelectorEntityType($chat),
			'title' => $chat['TITLE'],
			'avatar' => \CIMChat::GetAvatarImage($chat['AVATAR'], 200, false),
			'customData' => [
				'imChat' => Chat::formatChatData($chat),
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

		if (!$this->getOption('fillDialogWithDefaultValues', true))
		{
			$recentChats = [];
			$recentItems = $dialog->getRecentItems()->getEntityItems(static::getEntityId());
			$recentIds = array_map('intval', array_keys($recentItems));
			$recentChats = $this->fillRecentChats($recentChats, $recentIds, []);

			$dialog->addRecentItems($this->makeChatItems($recentChats));

			return;
		}

		// Preload chats ('doSearch' method has to have the same filter).
		$preloadedChats = $this->getPreloadedChatsCollection();
		$recentChats = [];

		// Recent Items
		$recentItems = $dialog->getRecentItems()->getEntityItems(static::getEntityId());
		$recentIds = array_map('intval', array_keys($recentItems));
		$recentChats = $this->fillRecentChats($recentChats, $recentIds, $preloadedChats);

		// Global Recent Items
		if (count($recentChats) < self::MAX_CHATS_IN_RECENT_TAB)
		{
			$recentGlobalItems = $dialog->getGlobalRecentItems()->getEntityItems(static::getEntityId());
			$recentGlobalIds = [];

			if (!empty($recentGlobalItems))
			{
				$recentGlobalIds = array_map('intval', array_keys($recentGlobalItems));
				$recentChatsIdList = array_column($recentChats, 'ID');
				$recentGlobalIds = array_values(array_diff($recentGlobalIds, $recentChatsIdList));
				$recentGlobalIds = array_slice(
					$recentGlobalIds,
					0,
					self::MAX_CHATS_IN_RECENT_TAB - count($recentChats)
				);
			}

			$recentChats = $this->fillRecentChats($recentChats, $recentGlobalIds, $preloadedChats);
		}

		// The rest of preloaded chats
		foreach ($preloadedChats as $preloadedChat)
		{
			$recentChats[] = $preloadedChat;
		}

		$dialog->addRecentItems($this->makeChatItems($recentChats));
	}

	protected function getPreloadedChatsCollection(): array
	{
		return $this->getChatCollection([
			'order' => ['ID' => 'DESC'],
			'limit' => self::MAX_CHATS_IN_RECENT_TAB
		]);
	}

	private function fillRecentChats(array $recentChats, array $recentIds, array $preloadedChats): array
	{
		if (count($recentIds) < 1)
		{
			return [];
		}

		$chatIds = array_values(array_diff($recentIds, array_column($preloadedChats, 'ID')));
		if (!empty($chatIds))
		{
			$chats = $this->getChatCollection(['chatIds' => $chatIds]);
			foreach ($chats as $chat)
			{
				$preloadedChats[] = $chat;
			}
		}

		foreach ($recentIds as $recentId)
		{
			$chat = $preloadedChats[$recentId] ?? null;
			if ($chat)
			{
				$recentChats[] = $chat;
			}
		}

		return $recentChats;
	}

	protected static function isFulltextIndexExist(): bool
	{
		$isFulltextIndexExist =	Option::get('im', 'search_title_fulltext_index_created', 'N') === 'Y';

		if ($isFulltextIndexExist)
		{
			return true;
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$result = $connection->query("SHOW INDEX FROM b_im_chat_index where Index_type = 'FULLTEXT' and Column_name = 'SEARCH_TITLE'");

		if ($result->fetch())
		{
			Option::set('im', 'search_title_fulltext_index_created', 'Y');

			return true;
		}

		return false;
	}


	protected static function addFilterBySearchQuery(Filter\ConditionTree $filter, string $searchQuery): void
	{
		$searchQuery = trim($searchQuery);

		if (empty($searchQuery) || mb_strlen($searchQuery) < Filter\Helper::getMinTokenSize())
		{
			return;
		}

		if (!static::isFulltextIndexExist())
		{
			$filter->whereLike('CHAT_INDEX.SEARCH_TITLE', $searchQuery . '%');

			return;
		}

		$searchText = Filter\Helper::matchAgainstWildcard(Content::prepareStringToken($searchQuery));
		$filter->whereMatch('CHAT_INDEX.SEARCH_TITLE', $searchText);
	}

	private static function isValidSearchQuery(string $searchQuery): bool
	{
		$searchQuery = trim($searchQuery);
		if ($searchQuery === '')
		{
			return false;
		}

		if (mb_strlen($searchQuery) < Filter\Helper::getMinTokenSize())
		{
			return false;
		}

		return true;
	}
}