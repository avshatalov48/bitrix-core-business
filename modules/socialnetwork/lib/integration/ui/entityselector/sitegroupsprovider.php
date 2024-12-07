<?php

namespace Bitrix\Socialnetwork\Integration\UI\EntitySelector;

use Bitrix\Main\GroupTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;
use Bitrix\UI\EntitySelector\Tab;

class SiteGroupsProvider extends BaseProvider
{
	public const ENTITY_ID = 'site-groups';

	private const SEARCH_ITEMS_LIMIT = 20;

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
		$siteGroupsRecent = $dialog->getRecentItems()->getEntityItems(self::ENTITY_ID);
		$recentIds = array_keys($siteGroupsRecent);
		$items = $this->getItems($recentIds);

		foreach ($items as $index => $item)
		{
			if (empty($dialog->getContext()))
			{
				$item->setSort($index);
			}
			$dialog->addRecentItem($item);
		}

		$dialog->addTab(new Tab([
			'id' => self::ENTITY_ID,
			'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_SITE_GROUPS_TAB_TITLE'),
		]));
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
			->fetchAll();

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
			->fetchAll();

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
