<?php

namespace Bitrix\Socialnetwork\Integration\UI\EntitySelector;

use Bitrix\Main\GroupTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\ArrayHelper;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\RecentItem;
use Bitrix\UI\EntitySelector\SearchQuery;
use Bitrix\UI\EntitySelector\Tab;

class SiteGroupsProvider extends BaseProvider
{
	public const ENTITY_ID = 'site-groups';

	private const RECENT_TAB_FILL_LIMIT = 20;
	private const SEARCH_ITEMS_LIMIT = self::RECENT_TAB_FILL_LIMIT;

	public function __construct(array $options = [])
	{
		parent::__construct();
	}

	public function isAvailable(): bool
	{
		global $USER;
		if (!($USER instanceof \CUser))
		{
			return false;
		}

		return $USER->IsAuthorized() && $USER->CanDoOperation('view_groups');
	}

	public function fillDialog(Dialog $dialog): void
	{
		$recent = $dialog->getRecentItems();

		$moreToFillCount = self::RECENT_TAB_FILL_LIMIT - $recent->count();
		if ($moreToFillCount > 0)
		{
			$addedCount = $this->fillRecentFromGlobalContext($dialog, $moreToFillCount);
			$moreToFillCount -= $addedCount;
		}

		if ($moreToFillCount > 0)
		{
			$this->fillRecentFromDB($dialog, $moreToFillCount);
		}

		$dialog->addTab(new Tab([
			'id' => self::ENTITY_ID,
			'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_SITE_GROUPS_TAB_TITLE'),
		]));
	}

	private function fillRecentFromGlobalContext(Dialog $dialog, int $maxItemsToAdd): int
	{
		$recent = $dialog->getRecentItems();

		$addedCount = 0;

		/** @var RecentItem $globalRecentItem */
		foreach ($dialog->getGlobalRecentItems()->getEntityItems(self::ENTITY_ID) as $globalRecentItem)
		{
			if ($addedCount >= $maxItemsToAdd)
			{
				break;
			}

			$recent->add($globalRecentItem);
			$addedCount++;
		}

		return $addedCount;
	}

	private function fillRecentFromDB(Dialog $dialog, int $limit): void
	{
		$query = GroupTable::query()
			->setSelect(['ID'])
			->where('ANONYMOUS', 'N')
			->whereNotNull('NAME')
			->setLimit($limit)
		;

		$alreadyAddedIds = array_map(fn(RecentItem $item) => $item->getId(), $dialog->getRecentItems()->getAll());
		ArrayHelper::normalizeArrayValuesByInt($alreadyAddedIds);
		if (!empty($alreadyAddedIds))
		{
			$query->whereNotIn('ID', $alreadyAddedIds);
		}

		$ids = $query->fetchCollection()->getIdList();
		foreach ($ids as $id)
		{
			$dialog->getRecentItems()->add(new RecentItem([
				'id' => $id,
				'entityId' => self::ENTITY_ID,
			]));
		}
	}

	public function getItems(array $ids): array
	{
		if (empty($ids))
		{
			return [];
		}

		$result = [];

		$groupItems = GroupTable::query()
			->setSelect(['ID', 'NAME'])
			->where('ANONYMOUS', 'N')
			->whereNotNull('NAME')
			->whereIn('ID', $ids)
			->fetchAll()
		;

		foreach ($groupItems as $groupItem)
		{
			$item = new Item([
				'id' => $groupItem['ID'],
				'entityId' => static::ENTITY_ID,
				'title' => $groupItem['NAME'],
			]);

			$item->addTab(['site-groups']);

			$result[] = $item;
		}

		return $result;
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$items = GroupTable::query()
			->setSelect(['ID', 'NAME'])
			->where('ANONYMOUS', 'N')
			->whereNotNull('NAME')
			->whereLike('NAME', "%{$searchQuery->getQuery()}%")
			->setLimit(self::SEARCH_ITEMS_LIMIT)
			->fetchAll()
		;

		foreach ($items as $item)
		{
			$dialog->addItem(new Item([
				'id' => $item['ID'],
				'entityId' => self::ENTITY_ID,
				'title' => $item['NAME'],
			]));
		}

		$isTherePossiblyMoreResultsForThisQuery = count($items) >= self::SEARCH_ITEMS_LIMIT;
		$searchQuery->setCacheable(!$isTherePossiblyMoreResultsForThisQuery);
	}
}
