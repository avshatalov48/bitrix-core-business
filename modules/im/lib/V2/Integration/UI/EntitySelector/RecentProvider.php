<?php

namespace Bitrix\Im\V2\Integration\UI\EntitySelector;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\Model\UserTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Entity\User\UserCollection;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Filter\Helper;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Search\Content;
use Bitrix\Main\Type\DateTime;
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

	private string $preparedSearchString;
	private array $userIds;
	private array $chatIds;
	private bool $sortEnable = true;

	public function __construct()
	{
		parent::__construct();
	}

	public function isAvailable(): bool
	{
		global $USER;

		return $USER->IsAuthorized();
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
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
		[$ids, $datesUpdate, $datesCreate] = $this->getDialogIdsWithDates();
		$items = $this->getBlankItems($ids, $datesUpdate, $datesCreate);
		$this->fillItems($items);

		return $items;
	}

	public function getPreselectedItems(array $ids): array
	{
		$this->sortEnable = false;
		$ids = array_slice($ids, 0, self::LIMIT);
		$this->setUserAndChatIds($ids);
		[, $datesUpdate, $datesCreate] = $this->getDialogIdsWithDates();
		$items = $this->getBlankItems($ids, $datesUpdate, $datesCreate);
		$this->fillItems($items);

		return $items;
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

	private function getBlankItem(string $dialogId, ?DateTime $dateUpdate = null, ?DateTime $dateCreate = null): Item
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
		$customData['dateUpdate'] = $dateUpdate;
		$customData['dateCreateTs'] = $dateCreate instanceof DateTime ? $dateCreate->getTimestamp() : 0;
		if (isset($dateUpdate))
		{
			if ($this->sortEnable)
			{
				$sort = $dateUpdate->getTimestamp();
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

	private function getDialogIdsWithDates(): array
	{
		$userIdsWithDate = $this->getUserIdsWithDate();
		$chatIdsWithDate = $this->getChatIdsWithDate();
		$ids = array_merge(array_keys($chatIdsWithDate), array_keys($userIdsWithDate));
		$dates = $this->mergeByKey($chatIdsWithDate, $userIdsWithDate);
		$datesUpdate = array_column($dates, 'DATE_UPDATE', 'DIALOG_ID');
		$datesCreate = array_column($dates, 'DATE_CREATE', 'DIALOG_ID');

		return [$ids, $datesUpdate, $datesCreate];
	}

	private function getSortedLimitedBlankItems(): array
	{
		[$ids, $datesUpdate, $datesCreate] = $this->getDialogIdsWithDates();
		$items = $this->getBlankItems($ids, $datesUpdate, $datesCreate);
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

	private function getChatIdsWithDate(): array
	{
		$result = [];
		$query = ChatTable::query()
			->setSelect(['ID', 'RECENT_DATE_UPDATE' => 'RECENT.DATE_UPDATE', 'DATE_CREATE'])
			->setLimit(self::LIMIT)
			->registerRuntimeField(
				new Reference(
					'RECENT',
					RecentTable::class,
					Join::on('this.ID', 'ref.ITEM_CID')->where('ref.USER_ID', $this->getContext()->getUserId()),
					['join_type' => Join::TYPE_LEFT]
				)
			)
			->where($this->getRecentFilter())
			->whereIn('TYPE', [Chat::IM_TYPE_CHAT, Chat::IM_TYPE_OPEN])
		;
		if (isset($this->preparedSearchString))
		{
			$query
				->whereMatch('INDEX.SEARCH_TITLE', $this->preparedSearchString)
				->setOrder(['RECENT.DATE_UPDATE' => 'DESC', 'DATE_CREATE' => 'DESC'])
			;
		}
		elseif (isset($this->chatIds) && !empty($this->chatIds))
		{
			$query->whereIn('ID', $this->chatIds);
		}
		else
		{
			return [];
		}

		$raw = $query->fetchAll();

		foreach ($raw as $row)
		{
			$dialogId = 'chat' . $row['ID'];
			$result[$dialogId] = [
				'DIALOG_ID' => $dialogId,
				'DATE_UPDATE' => $row['RECENT_DATE_UPDATE'],
				'DATE_CREATE' => $row['DATE_CREATE'],
			];
		}

		return $result;
	}

	private function getRecentFilter(): ConditionTree
	{
		if (User::getCurrent()->isExtranet())
		{
			return Query::filter()->whereNotNull('RECENT.USER_ID');
		}

		return Query::filter()
			->logic('or')
			->whereNotNull('RECENT.USER_ID')
			->where('TYPE', Chat::IM_TYPE_OPEN)
		;
	}

	private function getUserIdsWithDate(): array
	{
		$result = [];
		$query = UserTable::query()
			->setSelect(['ID', 'DATE_UPDATE' => 'RECENT.DATE_UPDATE', 'IS_INTRANET_USER', 'DATE_CREATE' => 'DATE_REGISTER'])
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
				->setOrder(['RECENT.DATE_UPDATE' => 'DESC', 'IS_INTRANET_USER' => 'DESC', 'DATE_CREATE' => 'DESC'])
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
			$result[(int)$row['ID']] = [
				'DIALOG_ID' => (int)$row['ID'],
				'DATE_UPDATE' => $row['DATE_UPDATE'],
				'DATE_CREATE' => $row['DATE_CREATE'],
			];
		}

		return $result;
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

	private function prepareSearchString(string $searchString): string
	{
		$searchString = trim($searchString);

		return Helper::matchAgainstWildcard(Content::prepareStringToken($searchString));
	}
}