<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntitySelector;

use Bitrix\Main\Loader;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Highloadblock as HL;

class BrandProvider extends BaseProvider
{
	private const BRAND_LIMIT = 20;
	private const BRAND_ENTITY_ID = 'brand';
	private const BRAND_CODE = 'BRAND_REF';

	public function __construct(array $options = [])
	{
		parent::__construct();

		$this->options['iblockId'] = (int)($options['iblockId'] ?? 0);
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function getItems(array $ids): array
	{
		$items = [];

		$filter = !empty($ids) ? ['=ID' => $ids] : [];

		foreach ($this->getBrands($filter) as $section)
		{
			$items[] = $this->makeItem($section);
		}

		return $items;
	}

	public function getSelectedItems(array $ids): array
	{
		return $this->getItems($ids);
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

		$recentItemsCount = count($dialog->getRecentItems()->getEntityItems(self::BRAND_ENTITY_ID));

		if ($recentItemsCount < self::BRAND_LIMIT)
		{
			foreach ($this->getBrands() as $brand)
			{
				$dialog->addRecentItem(
					$this->makeItem($brand)
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
			$filter['%UF_NAME'] = $query;
		}

		foreach ($this->getBrands($filter) as $brand)
		{
			$dialog->addItem(
				$this->makeItem($brand)
			);
		}

		if ($dialog->getItemCollection()->count() >= self::BRAND_LIMIT)
		{
			$searchQuery->setCacheable(false);
		}
	}

	protected function getBrands(array $filter = []): array
	{
		if (!Loader::includeModule('highloadblock') || !Loader::includeModule('iblock'))
		{
			return [];
		}

		$catalogId = $this->options['iblockId'];
		if ($catalogId <= 0)
		{
			return [];
		}

		$propertySettings = PropertyTable::getList([
			'select' => ['ID', 'USER_TYPE_SETTINGS'],
			'filter' => [
				'=IBLOCK_ID' => $catalogId,
				'=ACTIVE' => 'Y',
				'=CODE' => self::BRAND_CODE,
			],
			'limit' => 1,
		])
			->fetch()
		;

		if (!$propertySettings)
		{
			return [];
		}

		$propertySettings['USER_TYPE_SETTINGS'] = (
		$userTypeSettings = CheckSerializedData($propertySettings['USER_TYPE_SETTINGS'])
			? unserialize($propertySettings['USER_TYPE_SETTINGS'], ['allowed_classes' => false])
			: []
		);

		if (empty($userTypeSettings['TABLE_NAME']))
		{
			return [];
		}

		$table = HL\HighloadBlockTable::getList([
			'select' => ['TABLE_NAME', 'NAME', 'ID'],
			'filter' => ['=TABLE_NAME' => $userTypeSettings['TABLE_NAME']],
		])
			->fetch()
		;

		$brandEntity = HL\HighloadBlockTable::compileEntity($table);
		$brandEntityClass = $brandEntity->getDataClass();

		$parameters = [
			'select' => ['UF_XML_ID', 'UF_FILE', 'UF_NAME'],
		];

		if (!empty($filter))
		{
			$parameters['filter'] = $filter;
		}

		$brands = [];
		$brandsRaw = $brandEntityClass::getList($parameters);
		while ($brand = $brandsRaw->fetch())
		{
			if (!empty($brand['UF_FILE']))
			{
				$brand['LOGO'] = \CFile::resizeImageGet(
					$brand['UF_FILE'],
					[
						'width' => 100,
						'height' => 100,
					],
					BX_RESIZE_IMAGE_EXACT,
					false
				)['src'];
			}

			$brands[] = $brand;
		}

		return $brands;
	}

	protected function makeItem(array $brand): Item
	{
		return new Item([
			'id' => $brand['UF_XML_ID'],
			'entityId' => self::BRAND_ENTITY_ID,
			'title' => $brand['UF_NAME'],
			'avatar' => $brand['LOGO'],
		]);
	}
}