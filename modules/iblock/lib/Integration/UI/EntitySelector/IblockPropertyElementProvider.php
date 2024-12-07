<?php

namespace Bitrix\Iblock\Integration\UI\EntitySelector;

use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\PropertyTable;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\SearchQuery;

class IblockPropertyElementProvider extends BaseProvider
{
	public const ENTITY_ID = 'iblock-property-element';
	protected const ELEMENTS_LIMIT = 100;

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

		foreach ($this->getElements($filter) as $element)
		{
			$items[] = $this->makeItem($element);
		}

		return $items;
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

		$recentItems = $dialog->getRecentItems()->getEntityItems(self::ENTITY_ID);
		$recentItemsCount = count($recentItems);

		if ($recentItemsCount < self::ELEMENTS_LIMIT)
		{
			$elements = $this->getElements([], self::ELEMENTS_LIMIT);
			foreach ($elements as $element)
			{
				$dialog->addRecentItem($this->makeItem($element));
			}
		}
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$filter = [];

		$query = $searchQuery->getQuery();
		if ($query !== '')
		{
			$filter = $this->getQueryFilter($query);
		}

		$elements = $this->getElements($filter, self::ELEMENTS_LIMIT);
		if (count($elements) === self::ELEMENTS_LIMIT)
		{
			$searchQuery->setCacheable(false);
		}
		foreach ($elements as $element)
		{
			$dialog->addItem(
				$this->makeItem($element)
			);
		}
	}

	public function getPreselectedItems(array $ids): array
	{
		return $this->getItems($ids);
	}

	private function getQueryFilter(string $query): array
	{
		return [
			'*SEARCHABLE_CONTENT' => $query,
		];
	}

	protected function getElements(array $additionalFilter = [], ?int $limit = null): array
	{
		$elements = [];

		$filter = $this->getDefaultFilter();
		if (!empty($additionalFilter))
		{
			$filter = array_merge($filter, $additionalFilter);
		}

		$navParams = false;
		if ($limit)
		{
			$navParams = ['nTopCount' => $limit];
		}

		$selectFields = [
			'ID',
			'NAME',
			'DETAIL_TEXT',
			'PREVIEW_PICTURE',
			'IBLOCK_ID',
			'XML_ID',
		];

		if (!empty($filter))
		{
			$elementData = \CIBlockElement::GetList(
				[],
				$filter,
				false,
				$navParams,
				$selectFields
			);
			while ($element = $elementData->fetch())
			{
				if (empty($element['PREVIEW_PICTURE']))
				{
					$element['PREVIEW_PICTURE'] = $this->getElementImage($element);
				}
				$elements[] = $element;
			}
		}

		return $elements;
	}

	protected function makeItem(array $element): Item
	{
		$itemParams = [
			'id' => $element['ID'] ?? null,
			'entityId' => self::ENTITY_ID,
			'title' => $element['NAME'] ?? null,
			'subtitle' => $element['ID'] ?? null,
			'description' => $element['DETAIL_TEXT'] ?? null,
			'avatar' => $element['PREVIEW_PICTURE'] ?? null,
			'customData' => [
				'xmlId' => $element['XML_ID'] ?? null,
			],
		];

		return new Item($itemParams);
	}

	private function getElementImage(array $element): ?string
	{
		$iblockId = $element['IBLOCK_ID'] ?? null;
		if (!$iblockId)
		{
			return '';
		}

		$photoPropertyId = $this->getMorePhotoPropertyId($iblockId);
		if (!$photoPropertyId)
		{
			return '';
		}

		$propertyFilter = [
			'ID' => $photoPropertyId,
		];
		$result = \CIBlockElement::GetProperty($iblockId, $element['ID'], 'sort', 'asc', $propertyFilter)->Fetch();

		if (empty($result['VALUE']))
		{
			return '';
		}

		if (is_array($result['VALUE']))
		{
			$imageId = (int)$result['VALUE'][0];
		}
		else
		{
			$imageId = (int)$result['VALUE'];
		}

		return $this->getImageSource($imageId);
	}

	private function getDefaultFilter(): array
	{
		$filter = [
			'CHECK_PERMISSIONS' => 'Y',
			'MIN_PERMISSION' => 'R',
		];

		$iblockId = (int)($this->getOption('iblockId', 0));
		if (!empty($iblockId))
		{
			$filter['IBLOCK_ID'] = $iblockId;
		}

		return $filter;
	}

	private function getMorePhotoPropertyId(int $iblockId): ?int
	{
		$iterator = PropertyTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'=CODE' => \CIBlockPropertyTools::CODE_MORE_PHOTO,
				'=ACTIVE' => 'Y',
			],
		]);
		if ($row = $iterator->fetch())
		{
			return (int)$row['ID'];
		}

		return null;
	}

	private function getImageSource(int $id): ?string
	{
		if ($id <= 0)
		{
			return null;
		}

		$file = \CFile::GetFileArray($id);
		if (!$file)
		{
			return null;
		}

		return Tools::getImageSrc($file, false) ?: null;
	}
}
