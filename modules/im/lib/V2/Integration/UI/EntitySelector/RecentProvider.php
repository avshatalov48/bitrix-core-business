<?php

namespace Bitrix\Im\V2\Integration\UI\EntitySelector;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\Model\UserTable;
use Bitrix\Im\V2\Application\Features;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\Background\Background;
use Bitrix\Im\V2\Chat\TextField\TextFieldEnabled;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Entity\User\UserBot;
use Bitrix\Im\V2\Entity\User\UserCollection;
use Bitrix\Im\V2\Integration\AI\RoleManager;
use Bitrix\Im\V2\Integration\HumanResources\Department\Department;
use Bitrix\Im\V2\Integration\Socialnetwork\Group;
use Bitrix\Im\V2\Permission\ActionGroup;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Filter\Helper;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Search\Content;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserIndexTable;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\RecentItem;
use Bitrix\UI\EntitySelector\SearchQuery;

class RecentProvider extends BaseProvider
{
	use ContextCustomer;

	protected const ENTITY_ID = 'im-recent-v2';

	protected const LIMIT = 30;
	protected const TECHNICAL_LIMIT = 1000;
	private const ENTITY_TYPE_USER = 'im-user';
	private const ENTITY_TYPE_CHAT = 'im-chat';
	private const WITH_CHAT_BY_USERS_OPTION = 'withChatByUsers';
	private const ONLY_WITH_MANAGE_MESSAGES_RIGHT_OPTION = 'onlyWithManageMessagesRight';
	protected const ONLY_WITH_MANAGE_USERS_ADD_RIGHT_OPTION = 'onlyWithManageUsersAddRight';
	protected const ONLY_WITH_OWNER_RIGHT_OPTION = 'onlyWithOwnerRight';
	protected const ONLY_WITH_NULL_ENTITY_TYPE_OPTION = 'onlyWithNullEntityType';
	private const EXCLUDE_FROM_RECENT_OPTION = 'excludeFromRecent';
	protected const INCLUDE_ONLY_OPTION = 'includeOnly';
	private const EXCLUDE_OPTION = 'exclude';
	private const SEARCH_FLAGS_OPTION = 'searchFlags';
	protected const FLAG_USERS = 'users';
	protected const FLAG_CHATS = 'chats';
	protected const FLAG_BOTS = 'bots';
	private const ALLOWED_SEARCH_FLAGS = [self::FLAG_USERS, self::FLAG_CHATS, self::FLAG_BOTS];
	protected const SEARCH_CHAT_TYPES_OPTION = 'searchChatTypes';
	protected const EXCLUDE_IDS_OPTION = 'excludeIds';
	protected const ALLOWED_SEARCH_CHAT_TYPES = [
		Chat::IM_TYPE_CHAT,
		Chat::IM_TYPE_OPEN,
		Chat::IM_TYPE_CHANNEL,
		Chat::IM_TYPE_OPEN_CHANNEL,
		Chat::IM_TYPE_COLLAB,
	];
	private const WITH_CHAT_BY_USERS_DEFAULT = false;
	private const ONLY_WITH_MANAGE_MESSAGE_RIGHT_DEFAULT = false;
	private const ONLY_WITH_OWNER_RIGHT_DEFAULT = false;
	private const ONLY_WITH_NULL_ENTITY_TYPE_DEFAULT = false;
	private const SEARCH_FLAGS_DEFAULT = [
		self::FLAG_USERS => true,
		self::FLAG_CHATS => true,
	];
	private string $preparedSearchString;
	private string $originalSearchString;
	private array $userIds;
	private array $chatIds;
	private bool $sortEnable = true;

	public function __construct(array $options = [])
	{
		$this->options[self::WITH_CHAT_BY_USERS_OPTION] = self::WITH_CHAT_BY_USERS_DEFAULT;
		$this->options[self::ONLY_WITH_MANAGE_MESSAGES_RIGHT_OPTION] = self::ONLY_WITH_MANAGE_MESSAGE_RIGHT_DEFAULT;
		$this->options[self::ONLY_WITH_OWNER_RIGHT_OPTION] = self::ONLY_WITH_OWNER_RIGHT_DEFAULT;
		$this->options[self::ONLY_WITH_NULL_ENTITY_TYPE_OPTION] = self::ONLY_WITH_NULL_ENTITY_TYPE_DEFAULT;
		if (isset($options[self::WITH_CHAT_BY_USERS_OPTION]) && is_bool($options[self::WITH_CHAT_BY_USERS_OPTION]))
		{
			$this->options[self::WITH_CHAT_BY_USERS_OPTION] = $options[self::WITH_CHAT_BY_USERS_OPTION];
		}
		if (isset($options[self::ONLY_WITH_MANAGE_MESSAGES_RIGHT_OPTION]) && is_bool($options[self::ONLY_WITH_MANAGE_MESSAGES_RIGHT_OPTION]))
		{
			$this->options[self::ONLY_WITH_MANAGE_MESSAGES_RIGHT_OPTION] = $options[self::ONLY_WITH_MANAGE_MESSAGES_RIGHT_OPTION];
		}
		if (isset($options[self::EXCLUDE_FROM_RECENT_OPTION]) && is_array($options[self::EXCLUDE_FROM_RECENT_OPTION]))
		{
			$this->options[self::EXCLUDE_FROM_RECENT_OPTION] = $options[self::EXCLUDE_FROM_RECENT_OPTION];
		}
		if (isset($options[self::SEARCH_CHAT_TYPES_OPTION]) && is_array($options[self::SEARCH_CHAT_TYPES_OPTION]))
		{
			$this->options[self::SEARCH_CHAT_TYPES_OPTION] = $options[self::SEARCH_CHAT_TYPES_OPTION];
		}
		else
		{
			$this->options[self::SEARCH_CHAT_TYPES_OPTION] = static::ALLOWED_SEARCH_CHAT_TYPES;
		}
		if (isset($options[self::EXCLUDE_IDS_OPTION]) && is_array($options[self::EXCLUDE_IDS_OPTION]))
		{
			$this->options[self::EXCLUDE_IDS_OPTION] = $options[self::EXCLUDE_IDS_OPTION];
		}
		$this->prepareSearchFlags($options);
		parent::__construct();
	}

	public function isAvailable(): bool
	{
		global $USER;

		return $USER->IsAuthorized();
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$this->originalSearchString = $searchQuery->getQuery();
		$this->preparedSearchString = $this->prepareSearchString($searchQuery->getQuery());
		$searchQuery->setCacheable(false);
		if (!Content::canUseFulltextSearch($this->preparedSearchString))
		{
			return;
		}
		$items = $this->getSortedLimitedBlankItems();
		$this->fillItems($items);
		$dialog->addItems($items);
	}

	public function fillDialog(Dialog $dialog): void
	{
		if (!Loader::includeModule('intranet'))
		{
			return;
		}

		$requiredCountToFill = self::LIMIT - $dialog->getRecentItems()->count();

		if ($requiredCountToFill <= 0)
		{
			return;
		}

		$defaultItems = $this->getDefaultDialogItems();

		rsort($defaultItems);
		$defaultItems = array_slice($defaultItems, 0, $requiredCountToFill);

		foreach ($defaultItems as $itemId)
		{
			$dialog->getRecentItems()->add(new RecentItem(['id' => $itemId, 'entityId' => static::ENTITY_ID]));
		}
	}

	protected function getDefaultDialogItems(): array
	{
		if (!$this->getContext()->getUser()->isExtranet())
		{
			return Department::getInstance()->getColleagues();
		}

		return Group::getUsersInSameGroups($this->getContext()->getUserId());
	}

	public function getItems(array $ids): array
	{
		$this->sortEnable = false;
		$this->setUserAndChatIds($ids);
		$items = $this->getItemsWithDates();
		$this->fillItems($items);

		$this->chatIds = [];
		$this->userIds = [];

		return $items;
	}

	public function getPreselectedItems(array $ids): array
	{
		/*$this->sortEnable = false;
		$ids = array_slice($ids, 0, self::LIMIT);
		$this->setUserAndChatIds($ids);
		$foundItems = $this->getItemsWithDates();
		$foundItemsDialogId = array_keys($foundItems);
		$otherItemsDialogId = array_diff($ids, $foundItemsDialogId);
		$otherItems = $this->getBlankItems($otherItemsDialogId);
		$items = $this->mergeByKey($foundItems, $otherItems);
		$this->fillItems($items);*/

		return $this->getItems($ids);
	}

	private function setUserAndChatIds(array $ids): void
	{
		$needExcludeChats = isset($this->options[self::EXCLUDE_FROM_RECENT_OPTION][self::FLAG_CHATS]);
		$needExcludeUsers = isset($this->options[self::EXCLUDE_FROM_RECENT_OPTION][self::FLAG_USERS]);
		foreach ($ids as $id)
		{
			if ($this->isChatId($id) && !$needExcludeChats)
			{
				$chatId = substr($id, 4);
				$this->chatIds[$chatId] = $chatId;
			}
			elseif (!$needExcludeUsers)
			{
				$this->userIds[$id] = $id;
			}
		}
	}

	private function getBlankItem(string $dialogId, ?DateTime $dateMessage = null, ?DateTime $secondDate = null): Item
	{
		$id = $dialogId;
		$entityType = self::ENTITY_TYPE_USER;
		if ($this->isChatId($dialogId))
		{
			$id = substr($dialogId, 4);
			$entityType = self::ENTITY_TYPE_CHAT;
		}
		$customData = ['id' => $id];
		$sort = 0;
		$customData['dateMessage'] = $dateMessage;
		$customData['secondSort'] = $secondDate instanceof DateTime ? $secondDate->getTimestamp() : 0;
		if (isset($dateMessage))
		{
			if ($this->sortEnable)
			{
				$sort = $dateMessage->getTimestamp();
			}
		}

		return new Item([
			'id' => $dialogId,
			'entityId' => static::ENTITY_ID,
			'entityType' => $entityType,
			'sort' => $sort,
			'customData' => $customData,
		]);
	}

	/**
	 * @param Item[] $items
	 * @return array
	 */
	private function fillItems(array $items): void
	{
		$userIds = [];
		$chats = [];
		foreach ($items as $item)
		{
			$id = $item->getCustomData()->get('id');
			if ($item->getEntityType() === self::ENTITY_TYPE_USER)
			{
				$userIds[] = $id;

				continue;
			}

			$chats[$id] = Chat::getInstance($id);
		}

		$users = new UserCollection($userIds);
		$users->fillOnlineData();
		$privateChatIds = \Bitrix\Im\Dialog::getChatIds($userIds, $this->getContext()->getUserId());
		$copilotRoles = $this->getCopilotRoles($this->filterCopilotChats($chats));
		Chat::fillSelfRelations($chats);

		foreach ($items as $item)
		{
			$customData = $item->getCustomData()->getValues();
			if ($item->getEntityType() === self::ENTITY_TYPE_USER)
			{
				$user = $users->getById($customData['id']);
				$customData['user'] = $user->toRestFormat();

				$chatId = (int)$privateChatIds[$customData['id']];
				$customData['chat']['textFieldEnabled'] = (new TextFieldEnabled($chatId))->get();
				$customData['chat']['backgroundId'] = (new Background($chatId))->get();
				$customData['copilot'] = null;

				$item
					->setTitle($user->getName())
					->setAvatar($user->getAvatar())
					->setCustomData($customData)
				;
			}
			if ($item->getEntityType() === self::ENTITY_TYPE_CHAT)
			{
				$chat = $chats[$customData['id']] ?? null;
				if ($chat === null)
				{
					continue;
				}

				$customData['chat'] = $chat->toRestFormat(['CHAT_SHORT_FORMAT' => true]);
				$customData['copilot'] = $copilotRoles[$chat->getId()] ?? null;
				$item
					->setTitle($chat->getTitle())
					->setAvatar($chat->getAvatar())
					->setCustomData($customData)
				;
			}
		}
	}

	/**
	 * @param Chat\CopilotChat[] $copilotChats
	 * @return array
	 */
	private function getCopilotRoles(array $copilotChats): array
	{
		$roleManager = new RoleManager();

		$roleCodes = [];
		foreach ($copilotChats as $chat)
		{
			$chatId = (int)$chat->getId();
			$roleCodes[$chatId] = $roleManager->getMainRole($chatId);
		}

		$roles = $roleManager->getRoles($roleCodes);

		$result = [];
		foreach ($roleCodes as $chatId => $code)
		{
			$result[$chatId] = $roles[$code] ?? $roles[RoleManager::getDefaultRoleCode()];
		}

		return $result;
	}

	/**
	 * @param Chat[] $chats
	 * @return Chat\CopilotChat[]
	 */
	private function filterCopilotChats(array $chats): array
	{
		return array_filter($chats, static fn($chat) => $chat instanceof Chat\CopilotChat);
	}

	private function getItemsWithDates(): array
	{
		$userItemsWithDate = $this->getUserItemsWithDate();
		$chatItemsWithDate = $this->getChatItemsWithDate();

		return $this->mergeByKey($userItemsWithDate, $chatItemsWithDate);
	}

	private function getSortedLimitedBlankItems(): array
	{
		$items = $this->getItemsWithDates();
		usort($items, function(Item $a, Item $b) {
			if ($b->getSort() === $a->getSort())
			{
				if (!$this->isChatId($b->getId()) && !$this->isChatId($a->getId()))
				{
					$bUser = User::getInstance($b->getId());
					$aUser = User::getInstance($a->getId());
					if ($aUser->isExtranet() === $bUser->isExtranet())
					{
						return $bUser->getId() <=> $aUser->getId();
					}

					return $aUser->isExtranet() <=> $bUser->isExtranet();
				}
				return (int)$b->getCustomData()->get('secondSort') <=> (int)$a->getCustomData()->get('secondSort');
			}
			return $b->getSort() <=> $a->getSort();
		});

		return array_slice($items, 0, self::LIMIT);
	}

	private function getChatItemsWithDate(): array
	{
		if (!$this->needSearch(self::FLAG_CHATS))
		{
			return [];
		}

		if (isset($this->preparedSearchString))
		{
			return $this->mergeByKey(
				$this->getChatItemsWithDateByUsers(),
				$this->getChatItemsWithDateByTitle()
			);
		}

		if (isset($this->chatIds) && !empty($this->chatIds))
		{
			return $this->getChatItemsWithDateByIds();
		}

		return [];
	}

	private function getChatItemsWithDateByIds(): array
	{
		if (!isset($this->chatIds) || empty($this->chatIds))
		{
			return [];
		}

		$result = $this->getCommonChatQuery(limit: self::TECHNICAL_LIMIT)->whereIn('ID', $this->chatIds)->fetchAll();

		return $this->getChatItemsByRawResult($result);
	}

	private function getChatItemsWithDateByTitle(): array
	{
		if (!isset($this->preparedSearchString))
		{
			return [];
		}

		$result = $this
			->getCommonChatQueryWithOrder()
			->whereMatch('INDEX.SEARCH_TITLE', $this->preparedSearchString)
			->fetchAll()
		;

		return $this->getChatItemsByRawResult($result, ['byUser' => false]);
	}

	private function getChatItemsWithDateByUsers(): array
	{
		if (!isset($this->preparedSearchString) || !$this->withChatByUsers())
		{
			return [];
		}

		$result = $this
			->getCommonChatQueryWithOrder(Join::TYPE_INNER)
			->registerRuntimeField(
				'CHAT_SEARCH',
				(new Reference(
					'CHAT_SEARCH',
					Entity::getInstanceByQuery($this->getChatsByUserNameQuery()),
					Join::on('this.ID', 'ref.CHAT_ID')
				))->configureJoinType(Join::TYPE_INNER)
			)
			->fetchAll()
		;

		return $this->getChatItemsByRawResult($result, ['byUser' => true]);
	}

	private function getChatsByUserNameQuery(): Query
	{
		return RelationTable::query()
			->setSelect(['CHAT_ID'])
			->registerRuntimeField(
				'USER',
				(new Reference(
					'USER',
					\Bitrix\Main\UserTable::class,
					Join::on('this.USER_ID', 'ref.ID'),
				))->configureJoinType(Join::TYPE_INNER)
			)
			->registerRuntimeField(
				'USER_INDEX',
				(new Reference(
					'USER_INDEX',
					UserIndexTable::class,
					Join::on('this.USER_ID', 'ref.USER_ID'),
				))->configureJoinType(Join::TYPE_INNER)
			)
			->whereIn('MESSAGE_TYPE', [Chat::IM_TYPE_CHAT, Chat::IM_TYPE_OPEN])
			->where('USER.IS_REAL_USER', 'Y')
			->whereMatch('USER_INDEX.SEARCH_USER_CONTENT', $this->preparedSearchString)
			->setGroup(['CHAT_ID'])
		;
	}

	protected function getChatItemsByRawResult(array $raw, array $additionalCustomData = []): array
	{
		$result = [];

		foreach ($raw as $row)
		{
			$dialogId = 'chat' . $row['ID'];
			$messageDate = $row['MESSAGE_DATE_CREATE'] ?? null;
			$secondDate = $row['MESSAGE_DATE_CREATE'] ?? null;
			if (($row['IS_MEMBER'] ?? 'Y') === 'N')
			{
				$messageDate = null;
			}
			$item = $this->getBlankItem($dialogId, $messageDate, $secondDate);
			if (!empty($additionalCustomData))
			{
				$customData = $item->getCustomData()->getValues();
				$item->setCustomData(array_merge($customData, $additionalCustomData));
			}
			$result[$dialogId] = $item;
		}

		return $result;
	}

	protected function getCommonChatQueryWithOrder(string $joinType = Join::TYPE_LEFT, int $limit = self::LIMIT): Query
	{
		return $this->getCommonChatQuery($joinType, $limit)
			->setOrder(['IS_MEMBER' => 'DESC', 'LAST_MESSAGE_ID' => 'DESC', 'DATE_CREATE' => 'DESC'])
		;
	}

	protected function getCommonChatQuery(string $joinType = Join::TYPE_LEFT, int $limit = self::LIMIT): Query
	{
		$query = ChatTable::query()
			->setSelect(['ID', 'IS_MEMBER', 'MESSAGE_DATE_CREATE' => 'MESSAGE.DATE_CREATE', 'DATE_CREATE'])
			->registerRuntimeField(new Reference(
					'RELATION',
					RelationTable::class,
					Join::on('this.ID', 'ref.CHAT_ID')
						->where('ref.USER_ID', $this->getContext()->getUserId()),
					['join_type' => $joinType]
				)
			)
			->registerRuntimeField(
				new Reference(
					'MESSAGE',
					MessageTable::class,
					Join::on('this.LAST_MESSAGE_ID', 'ref.ID'),
					['join_type' => Join::TYPE_LEFT]
				)
			)
			->registerRuntimeField(
				'IS_MEMBER',
				(new ExpressionField(
					'IS_MEMBER',
					"CASE WHEN %s IS NULL THEN 'N' ELSE 'Y' END",
					['RELATION.ID']
				))->configureValueType(BooleanField::class)
			)
			->setLimit($limit)
			->whereIn('TYPE', $this->getAllowedChatTypesForQuery())
		;
		if ($joinType === Join::TYPE_LEFT)
		{
			$query->where($this->getRelationFilter());
		}

		if ($this->options[self::ONLY_WITH_MANAGE_MESSAGES_RIGHT_OPTION])
		{
			\Bitrix\Im\V2\Permission\Filter::getRoleOrmFilter($query, ActionGroup::ManageMessages, 'RELATION', '');
		}

		if ($this->options[self::ONLY_WITH_MANAGE_USERS_ADD_RIGHT_OPTION])
		{
			\Bitrix\Im\V2\Permission\Filter::getRoleOrmFilter($query, ActionGroup::ManageUsersAdd, 'RELATION', '');
		}

		if ($this->options[self::ONLY_WITH_OWNER_RIGHT_OPTION])
		{
			$query->where('AUTHOR_ID', $this->getContext()->getUserId());
		}

		if ($this->options[self::ONLY_WITH_NULL_ENTITY_TYPE_OPTION])
		{
			$query->where(Query::filter()
				->logic('or')
				->whereNull('ENTITY_TYPE')
				->where('ENTITY_TYPE', ''))
			;
		}

		if (isset($this->options[self::EXCLUDE_IDS_OPTION]) && is_array($this->options[self::EXCLUDE_IDS_OPTION]))
		{
			$query->whereNotIn('ID', $this->options[self::EXCLUDE_IDS_OPTION]);
		}

		return $query;
	}

	protected function getAllowedChatTypesForQuery(): array
	{
		$types = [
			Chat::IM_TYPE_CHAT,
			Chat::IM_TYPE_OPEN,
			Chat::IM_TYPE_CHANNEL,
			Chat::IM_TYPE_OPEN_CHANNEL,
			Chat::IM_TYPE_COLLAB,
			Chat::IM_TYPE_COPILOT,
		];

		return $types;
	}

	private function getRelationFilter(): ConditionTree
	{
		if (User::getCurrent()->isExtranet())
		{
			return Query::filter()->whereNotNull('RELATION.USER_ID');
		}

		return Query::filter()
			->logic('or')
			->whereNotNull('RELATION.USER_ID')
			->whereIn('TYPE', [Chat::IM_TYPE_OPEN, Chat::IM_TYPE_OPEN_CHANNEL])
		;
	}

	private function getUserItemsWithDate(): array
	{
		$result = [];
		if (!$this->needSearch(self::FLAG_USERS))
		{
			return $result;
		}
		$query = UserTable::query()
			->setSelect(['ID', 'DATE_MESSAGE' => 'RECENT.DATE_MESSAGE', 'IS_INTRANET_USER', 'DATE_CREATE' => 'DATE_REGISTER'])
			->where('ACTIVE', true)
			->registerRuntimeField(
				'RECENT',
				new Reference(
					'RECENT',
					RecentTable::class,
					Join::on('this.ID', 'ref.ITEM_ID')
						->where('ref.USER_ID', $this->getContext()->getUserId())
						->where('ref.ITEM_TYPE', Chat::IM_TYPE_PRIVATE),
					['join_type' => Join::TYPE_LEFT]
				)
			)
		;

		if (isset($this->preparedSearchString))
		{
			$query
				->whereMatch('INDEX.SEARCH_USER_CONTENT', $this->preparedSearchString)
				->setOrder(['RECENT.DATE_MESSAGE' => 'DESC', 'IS_INTRANET_USER' => 'DESC', 'DATE_CREATE' => 'DESC'])
				->setLimit(self::LIMIT)
			;
		}
		elseif (isset($this->userIds) && !empty($this->userIds))
		{
			$query->whereIn('ID', $this->userIds)->setLimit(self::TECHNICAL_LIMIT);
		}
		else
		{
			return [];
		}

		$query->where($this->getIntranetFilter());

		$raw = $query->fetchAll();

		foreach ($raw as $row)
		{
			if ($this->isHiddenBot((int)$row['ID']))
			{
				continue;
			}

			$result[(int)$row['ID']] = $this->getBlankItem((int)$row['ID'], $row['DATE_MESSAGE'], $row['DATE_CREATE']);
		}

		$result = $this->getAdditionalUsers($result);

		return $result;
	}

	private function getAdditionalUsers(array $foundUserItems): array
	{
		if ($this->needAddFavoriteChat($foundUserItems))
		{
			$foundUserItems[$this->getContext()->getUserId()] = $this->getFavoriteChatUserItem();
		}

		return $foundUserItems;
	}

	private function getFavoriteChatUserItem(): Item
	{
		$userId = $this->getContext()->getUserId();
		$row = ChatTable::query()
			->setSelect(['DATE_MESSAGE' => 'MESSAGE.DATE_CREATE', 'DATE_CREATE'])
			->registerRuntimeField(
				new Reference(
					'MESSAGE',
					MessageTable::class,
					Join::on('this.LAST_MESSAGE_ID', 'ref.ID'),
					['join_type' => Join::TYPE_LEFT]
				)
			)
			->where('ENTITY_TYPE', Chat::ENTITY_TYPE_FAVORITE)
			->where('ENTITY_ID', $userId)
			->fetch() ?: []
		;
		$dateMessage = $row['DATE_MESSAGE'] ?? null;
		$dateCreate = $row['DATE_CREATE'] ?? null;

		return $this->getBlankItem($this->getContext()->getUserId(), $dateMessage, $dateCreate);
	}

	private function needAddFavoriteChat(array $foundUserItems): bool
	{
		return
			!isset($foundUserItems[$this->getContext()->getUserId()])
			&& isset($this->originalSearchString)
			&& static::isPhraseFoundBySearchQuery(Chat\FavoriteChat::getTitlePhrase(), $this->originalSearchString)
		;
	}

	private static function isPhraseFoundBySearchQuery(string $phrase, string $searchQuery): bool
	{
		$searchWords = explode(' ', $searchQuery);
		$phraseWords = explode(' ', $phrase);

		foreach ($searchWords as $searchWord)
		{
			$searchWordLowerCase = mb_strtolower($searchWord);
			$found = false;
			foreach ($phraseWords as $phraseWord)
			{
				$phraseWordLowerCase = mb_strtolower($phraseWord);
				if (str_starts_with($phraseWordLowerCase, $searchWordLowerCase))
				{
					$found = true;
					break;
				}
			}
			if (!$found)
			{
				return false;
			}
		}

		return true;
	}

	private function isHiddenBot(int $userId): bool
	{
		$user = User::getInstance($userId);

		if ($user instanceof UserBot && $user->isBot())
		{
			if (!$this->needSearch(self::FLAG_BOTS))
			{
				return true;
			}

			$botData = $user->getBotData()->toRestFormat();
			if ($botData['isHidden'])
			{
				return true;
			}
		}

		return false;
	}

	private function getIntranetFilter(): ConditionTree
	{
		$filter = Query::filter();
		if (!Loader::includeModule('intranet'))
		{
			return $filter->where($this->getRealUserOrBotCondition());
		}

		$subQuery = Group::getExtranetAccessibleUsersQuery($this->getContext()->getUserId());
		if (!User::getCurrent()->isExtranet())
		{
			$filter->logic('or');
			$filter->where('IS_INTRANET_USER', true);
			if ($subQuery !== null)
			{
				$filter->whereIn('ID', $subQuery);
			}
			return $filter;
		}

		$filter->where($this->getRealUserOrBotCondition());
		if ($subQuery !== null)
		{
			$filter->whereIn('ID', $subQuery);
		}
		else
		{
			$filter->where(new ExpressionField('EMPTY_LIST', '1'), '!=', 1);
		}

		return $filter;
	}

	private function getRealUserOrBotCondition(): ConditionTree
	{
		return Query::filter()
			->logic('or')
			->whereNotIn('EXTERNAL_AUTH_ID', UserTable::filterExternalUserTypes(['bot']))
			->whereNull('EXTERNAL_AUTH_ID')
		;
	}

	private function prepareSearchFlags(array $options): void
	{
		$this->options[self::SEARCH_FLAGS_OPTION] = self::SEARCH_FLAGS_DEFAULT;

		if (isset($options[self::INCLUDE_ONLY_OPTION]) && is_array($options[self::INCLUDE_ONLY_OPTION]))
		{
			foreach (self::ALLOWED_SEARCH_FLAGS as $searchFlag)
			{
				$this->options[self::SEARCH_FLAGS_OPTION][$searchFlag] = false;
			}

			foreach ($options[self::INCLUDE_ONLY_OPTION] as $searchFlag)
			{
				if ($this->isValidSearchFlag($searchFlag))
				{
					$this->options[self::SEARCH_FLAGS_OPTION][$searchFlag] = true;
				}
			}
		}
		elseif (isset($options[self::EXCLUDE_OPTION]) && is_array($options[self::EXCLUDE_OPTION]))
		{
			foreach ($options[self::EXCLUDE_OPTION] as $searchFlag)
			{
				if ($this->isValidSearchFlag($searchFlag))
				{
					$this->options[self::SEARCH_FLAGS_OPTION][$searchFlag] = false;
				}
			}
		}
	}

	private function isValidSearchFlag(string $searchFlag): bool
	{
		return in_array($searchFlag, self::ALLOWED_SEARCH_FLAGS, true);
	}

	private function needSearch(string $flag): bool
	{
		return $this->options[self::SEARCH_FLAGS_OPTION][$flag] ?? true;
	}

	private function mergeByKey(array ...$arrays): array
	{
		$result = [];
		foreach ($arrays as $array)
		{
			foreach ($array as $key => $value)
			{
				$result[$key] = $value;
			}
		}

		return $result;
	}

	private function isChatId(string $id): bool
	{
		return substr($id, 0, 4) === 'chat';
	}

	private function withChatByUsers(): bool
	{
		return $this->options[self::WITH_CHAT_BY_USERS_OPTION] ?? self::WITH_CHAT_BY_USERS_DEFAULT;
	}

	private function prepareSearchString(string $searchString): string
	{
		$searchString = trim($searchString);

		return Helper::matchAgainstWildcard(Content::prepareStringToken($searchString));
	}
}
