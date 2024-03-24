<?php

namespace Bitrix\Im\V2\Integration\UI\EntitySelector;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\Model\UserTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Entity\User\UserBot;
use Bitrix\Im\V2\Entity\User\UserCollection;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Entity;
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

	private const LIMIT = 30;
	private const ENTITY_ID = 'im-recent-v2';
	private const ENTITY_TYPE_USER = 'im-user';
	private const ENTITY_TYPE_CHAT = 'im-chat';
	private const WITH_CHAT_BY_USERS_OPTION = 'withChatByUsers';
	private const WITH_CHAT_BY_USERS_DEFAULT = false;

	private string $preparedSearchString;
	private string $originalSearchString;
	private array $userIds;
	private array $chatIds;
	private bool $sortEnable = true;

	public function __construct(array $options = [])
	{
		$this->options[self::WITH_CHAT_BY_USERS_OPTION] = self::WITH_CHAT_BY_USERS_DEFAULT;
		if (isset($options[self::WITH_CHAT_BY_USERS_OPTION]) && is_bool($options[self::WITH_CHAT_BY_USERS_OPTION]))
		{
			$this->options[self::WITH_CHAT_BY_USERS_OPTION] = $options[self::WITH_CHAT_BY_USERS_OPTION];
		}
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
		if (!Content::canUseFulltextSearch($this->preparedSearchString))
		{
			return;
		}
		$searchQuery->setCacheable(false);
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

		$result = \CIntranetUtils::getDepartmentColleagues(null, true, false, 'Y', ['ID']);
		$colleaguesIds = [];

		while (($row = $result->Fetch()))
		{
			$colleaguesIds[] = (int)$row['ID'];
		}

		rsort($colleaguesIds);
		$colleaguesIds = array_slice($colleaguesIds, 0, $requiredCountToFill);

		foreach ($colleaguesIds as $userId)
		{
			$dialog->getRecentItems()->add(new RecentItem(['id' => $userId, 'entityId' => self::ENTITY_ID]));
		}
	}

	public function getItems(array $ids): array
	{
		$this->sortEnable = false;
		$ids = array_slice($ids, 0, self::LIMIT);
		$this->setUserAndChatIds($ids);
		$items = $this->getItemsWithDates();
		$this->fillItems($items);

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
		foreach ($ids as $id)
		{
			if ($this->isChatId($id))
			{
				$chatId = substr($id, 4);
				$this->chatIds[$chatId] = $chatId;
			}
			else
			{
				$this->userIds[$id] = $id;
			}
		}
	}

	private function getBlankItems(array $ids, array $datesUpdate = [], array $datesCreate = []): array
	{
		$result = [];

		foreach ($ids as $id)
		{
			$result[] = $this->getBlankItem($id, $datesUpdate[$id] ?? null, $datesCreate[$id] ?? null);
		}

		return $result;
	}

	private function getBlankItem(string $dialogId, ?DateTime $dateMessage = null, ?DateTime $dateCreate = null): Item
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
		$customData['dateCreateTs'] = $dateCreate instanceof DateTime ? $dateCreate->getTimestamp() : 0;
		if (isset($dateMessage))
		{
			if ($this->sortEnable)
			{
				$sort = $dateMessage->getTimestamp();
			}
		}

		return new Item([
			'id' => $dialogId,
			'entityId' => self::ENTITY_ID,
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
			}
			else
			{
				$chats[$id] = Chat::getInstance($id);
			}
		}
		$users = new UserCollection($userIds);
		$users->fillOnlineData();
		Chat::fillRole($chats);
		foreach ($items as $item)
		{
			$customData = $item->getCustomData()->getValues();
			if ($item->getEntityType() === self::ENTITY_TYPE_USER)
			{
				$user = $users->getById($customData['id']);
				$customData = array_merge($customData, $user->toRestFormat());
				$item->setTitle($user->getName())->setAvatar($user->getAvatar())->setCustomData($customData);
			}
			if ($item->getEntityType() === self::ENTITY_TYPE_CHAT)
			{
				$chat = $chats[$customData['id']] ?? null;
				if ($chat === null)
				{
					continue;
				}
				$customData = array_merge($customData, $chat->toRestFormat(['CHAT_SHORT_FORMAT' => true]));
				$item->setTitle($chat->getTitle())->setAvatar($chat->getAvatar())->setCustomData($customData);
			}
		}
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
				return (int)$b->getCustomData()->get('dateCreateTs') <=> (int)$a->getCustomData()->get('dateCreateTs');
			}
			return $b->getSort() <=> $a->getSort();
		});

		return array_slice($items, 0, self::LIMIT);
	}

	private function getChatItemsWithDate(): array
	{
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

		$result = $this->getCommonChatQuery()->whereIn('ID', $this->chatIds)->fetchAll();

		return $this->getChatItemsByRawResult($result);
	}

	private function getChatItemsWithDateByTitle(): array
	{
		if (!isset($this->preparedSearchString))
		{
			return [];
		}

		$result = $this
			->getCommonChatQuery()
			->whereMatch('INDEX.SEARCH_TITLE', $this->preparedSearchString)
			->setOrder(['LAST_MESSAGE_ID' => 'DESC', 'DATE_CREATE' => 'DESC'])
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
			->getCommonChatQuery(Join::TYPE_INNER)
			->setOrder(['LAST_MESSAGE_ID' => 'DESC', 'DATE_CREATE' => 'DESC'])
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

	private function getChatItemsByRawResult(array $raw, array $additionalCustomData = []): array
	{
		$result = [];

		foreach ($raw as $row)
		{
			$dialogId = 'chat' . $row['ID'];
			$item = $this->getBlankItem($dialogId, $row['MESSAGE_DATE_CREATE'], $row['DATE_CREATE']);
			if (!empty($additionalCustomData))
			{
				$customData = $item->getCustomData()->getValues();
				$item->setCustomData(array_merge($customData, $additionalCustomData));
			}
			$result[$dialogId] = $item;
		}

		return $result;
	}

	private function getCommonChatQuery(string $joinType = Join::TYPE_LEFT): Query
	{
		$query = ChatTable::query()
			->setSelect(['ID', 'MESSAGE_DATE_CREATE' => 'MESSAGE.DATE_CREATE', 'DATE_CREATE'])
			->registerRuntimeField(new Reference(
					'RELATION',
					RelationTable::class,
					Join::on('this.ID', 'ref.CHAT_ID')
						->where('ref.USER_ID', $this->getContext()->getUserId())
						->where('ref.MESSAGE_TYPE', Chat::IM_TYPE_CHAT),
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
			->setLimit(self::LIMIT)
			->whereIn('TYPE', [Chat::IM_TYPE_CHAT, Chat::IM_TYPE_OPEN])
		;
		if ($joinType === Join::TYPE_LEFT)
		{
			$query->where($this->getRelationFilter());
		}

		return $query;
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
			->where('TYPE', Chat::IM_TYPE_OPEN)
		;
	}

	private function getUserItemsWithDate(): array
	{
		$result = [];
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
			->setLimit(self::LIMIT)
		;

		if (isset($this->preparedSearchString))
		{
			$query
				->whereMatch('INDEX.SEARCH_USER_CONTENT', $this->preparedSearchString)
				->setOrder(['RECENT.DATE_MESSAGE' => 'DESC', 'IS_INTRANET_USER' => 'DESC', 'DATE_CREATE' => 'DESC'])
			;
		}
		elseif (isset($this->userIds) && !empty($this->userIds))
		{
			$query->whereIn('ID', $this->userIds);
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

		$subQuery = $this->getExtranetUsersQuery();
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

	private function getExtranetUsersQuery(): ?Query
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return null;
		}

		$extranetSiteId = Option::get('extranet', 'extranet_site');
		$extranetSiteId = ($extranetSiteId && ModuleManager::isModuleInstalled('extranet') ? $extranetSiteId : false);

		if (
			!$extranetSiteId
			|| \CSocNetUser::isCurrentUserModuleAdmin()
		)
		{
			return null;
		}

		/** @see \Bitrix\Socialnetwork\Integration\UI\EntitySelector\UserProvider::EXTRANET_ROLES */
		$extranetRoles = [
			\Bitrix\Socialnetwork\UserToGroupTable::ROLE_USER,
			\Bitrix\Socialnetwork\UserToGroupTable::ROLE_OWNER,
			\Bitrix\Socialnetwork\UserToGroupTable::ROLE_MODERATOR,
			\Bitrix\Socialnetwork\UserToGroupTable::ROLE_REQUEST,
		];

		$query = \Bitrix\Socialnetwork\UserToGroupTable::query();
		$query->addSelect(new ExpressionField('DISTINCT_USER_ID', 'DISTINCT %s', 'USER.ID'));
		$query->whereIn('ROLE', $extranetRoles);
		$query->registerRuntimeField(
			new Reference(
				'GS',
				\Bitrix\Socialnetwork\WorkgroupSiteTable::class,
				Join::on('ref.GROUP_ID', 'this.GROUP_ID')->where('ref.SITE_ID', $extranetSiteId),
				['join_type' => 'INNER']
			)
		);

		$query->registerRuntimeField(
			new Reference(
				'UG_MY',
				\Bitrix\Socialnetwork\UserToGroupTable::class,
				Join::on('ref.GROUP_ID', 'this.GROUP_ID')
					->where('ref.USER_ID', $this->getContext()->getUserId())
					->whereIn('ref.ROLE', $extranetRoles),
				['join_type' => 'INNER']
			)
		);

		return $query;
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