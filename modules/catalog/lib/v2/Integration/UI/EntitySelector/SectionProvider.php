<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntitySelector;

use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class SectionProvider extends BaseProvider
{
	private const SECTION_LIMIT = 20;
	protected const SECTION_ENTITY_ID = 'section';

	public function __construct(array $options = [])
	{
		parent::__construct();

		$this->options = $options;
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function getItems(array $ids): array
	{
		$items = [];

		$filter = !empty($ids) ? ['ID' => $ids] : [];

		foreach ($this->getActiveSections($filter) as $section)
		{
			$items[] = $this->makeItem($section);
		}

		return $items;
	}

	public function getSelectedItems(array $ids): array
	{
		$selectedItems = [];

		$filter = !empty($ids) ? ['ID' => $ids] : [];

		foreach ($this->getSections($filter) as $section)
		{
			$selectedItems[] = $this->makeItem($section);
		}

		return $selectedItems;
	}

	public function fillDialog(Dialog $dialog): void
	{
		$dialog->loadPreselectedItems();

		if ($dialog->getItemCollection()->count() > 0)
		{
			foreach ($dialog->getItemCollection() as $item)
			{
				$dialog->addRecentItem($item);
			}
		}

		$recentItemsCount = count($dialog->getRecentItems()->getEntityItems(static::SECTION_ENTITY_ID));

		if ($recentItemsCount < self::SECTION_LIMIT)
		{
			foreach ($this->getActiveSections() as $section)
			{
				$dialog->addRecentItem(
					$this->makeItem($section)
				);
			}
		}
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$filter = [];

		$query = $searchQuery->getQuery();
		if ($query !== '')
		{
			$filter['%NAME'] = $query;
		}

		foreach ($this->getActiveSections($filter) as $section)
		{
			$dialog->addItem(
				$this->makeItem($section)
			);
		}

		if ($dialog->getItemCollection()->count() >= self::SECTION_LIMIT)
		{
			$searchQuery->setCacheable(false);
		}
	}

	protected function getActiveSections(array $additionalFilter = []): array
	{
		return $this->getSections(array_merge(['=ACTIVE' => 'Y'], $additionalFilter));
	}

	protected function getSections(array $additionalFilter = []): array
	{
		$sections = [];

		$filter = $this->getDefaultFilter();
		if (!empty($additionalFilter))
		{
			$filter = array_merge($filter, $additionalFilter);
		}

		if (!empty($filter))
		{
			$sectionData = \CIBlockSection::GetList(
				[],
				$filter,
				false,
				['ID', 'NAME', 'PICTURE'],
				[
					'nTopCount' => self::SECTION_LIMIT,
				]
			);
			while ($section = $sectionData->fetch())
			{
				if (!empty($section['PICTURE']))
				{
					$section['PICTURE'] = \CFile::resizeImageGet(
						$section['PICTURE'],
						[
							'width' => 100,
							'height' => 100,
						],
						BX_RESIZE_IMAGE_EXACT,
						false
					)['src'];
				}

				$sections[] = $section;
			}
		}

		return $sections;
	}

	protected function makeItem(array $section): Item
	{
		return new Item([
			'id' => $section['ID'],
			'entityId' => static::SECTION_ENTITY_ID,
			'title' => $section['NAME'],
			'avatar' => $section['PICTURE'],
		]);
	}

	private function getDefaultFilter(): array
	{
		$filter = [];

		$iblockId = (int)($this->getOptions()['iblockId'] ?? 0);
		if (!empty($iblockId))
		{
			$filter['IBLOCK_ID'] = $iblockId;
		}

		return $filter;
	}
}