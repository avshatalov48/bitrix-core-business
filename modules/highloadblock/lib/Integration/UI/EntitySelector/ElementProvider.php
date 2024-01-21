<?php

namespace Bitrix\Highloadblock\Integration\UI\EntitySelector;

use Bitrix\Main\ORM;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Highloadblock\HighloadBlockRightsTable;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class ElementProvider extends BaseProvider
{
	public const ENTITY_ID = 'highloadblock-element';

	public const QUERY_SEARCH = 'S';
	public const QUERY_SUBSTRING = 'L';
	public const QUERY_BEGIN = 'B';

	protected const ELEMENTS_LIMIT = 100;

	private int $highloadblockId;

	private ORM\Entity $highloadblock;

	private array $fields;

	/**
	 * @param array $options
	 * <ul>
	 * <li>highloadblockId - Highloadblock id (int, required)
	 * <li>valueField - Value field name (string, optional, default value - UF_XML_ID)
	 * <li>titleField - Title field name (string, optional, default value - UF_NAME)
	 * <li>orderField - Order field name (string, optional, default value - UF_SORT)
	 * <li>direction - Order direaction (string, optional, ASC/DESC, default value - ASC)
	 * <li>queryMethod - Filter type for query (string, optional, default value - B)
	 * </ul>
	 */
	public function __construct(array $options = [])
	{
		parent::__construct();

		$this->options = array_filter(
			$options,
			function ($row)
			{
				return $row !== null;
			}
		);
	}

	public function isAvailable(): bool
	{
		global $USER;

		if (!(isset($USER) && $USER instanceof \CUser))
		{
			return false;
		}
		if (!$USER->isAuthorized())
		{
			return false;
		}

		if (!$this->isHighloadblockExists())
		{
			return false;
		}

		if ($USER->IsAdmin())
		{
			return true;
		}

		if (!$this->canReadHighloadblock())
		{
			return false;
		}

		return true;
	}

	public function getItems(array $ids): array
	{
		if (!$this->isHighloadblockExists())
		{
			return [];
		}

		$items = [];

		$elementList = $this->getElements([
			'filter' => $this->getFilterByIds($ids),
		]);
		foreach ($elementList as $element)
		{
			$items[] = $this->makeItem($element);
		}

		return $items;
	}

	public function getPreselectedItems(array $ids): array
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

		$recentItems = $dialog->getRecentItems()->getEntityItems(self::ENTITY_ID);
		$recentItemsCount = count($recentItems);

		if ($recentItemsCount < self::ELEMENTS_LIMIT)
		{
			$elementList = $this->getElements([
				'filter' => [],
				'limit' => self::ELEMENTS_LIMIT,
			]);
			foreach ($elementList as $element)
			{
				$dialog->addRecentItem($this->makeItem($element));
			}
		}
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$filter = $this->getQueryFilter($searchQuery);
		if ($filter === null)
		{
			return;
		}

		$elementList = $this->getElements([
			'filter' => $filter,
			'limit' => self::ELEMENTS_LIMIT,
		]);
		if (count($elementList) === self::ELEMENTS_LIMIT)
		{
			$searchQuery->setCacheable(false);
		}
		foreach ($elementList as $element)
		{
			$dialog->addItem(
				$this->makeItem($element)
			);
		}
	}

	protected function getHighloadblockId(): ?int
	{
		if (!isset($this->highloadblockId))
		{
			$this->initHighloadblock();
		}

		return $this->highloadblockId > 0 ? $this->highloadblockId : null;
	}

	protected function initHighloadblock(): void
	{
		if (isset($this->highloadblockId))
		{
			return;
		}

		$this->highloadblockId = (int)$this->getOption('highloadblockId');
		if ($this->highloadblockId > 0)
		{
			$hlblock = HighloadBlockTable::getRow([
				'select' => [
					'ID',
				],
				'filter' => [
					'=ID' => $this->highloadblockId,
				],
			]);
			if ($hlblock === null)
			{
				$this->highloadblockId = 0;
			}
		}
		if ($this->highloadblockId > 0)
		{
			$this->highloadblock = HighloadBlockTable::compileEntity($this->highloadblockId);
			$this->fields = $this->highloadblock->getScalarFields();
		}
	}

	protected function isHighloadblockExists(): bool
	{
		return $this->getHighloadblockId() !== null;
	}

	protected function canReadHighloadblock(): bool
	{
		$hlblockId = $this->getHighloadblockId();
		if ($hlblockId === null)
		{
			return false;
		}

		return true;
	}

	protected function getDefaultValueField(): string
	{
		return 'UF_XML_ID';
	}

	protected function getDefaultTitleField(): string
	{
		return 'UF_NAME';
	}

	protected function getDefaultOrderField(): string
	{
		return 'UF_SORT';
	}

	protected function getDefaultQueryMethod(): string
	{
		return self::QUERY_BEGIN;
	}

	protected function getValueField(): ?string
	{
		$field = trim((string)$this->getOption('valueField', $this->getDefaultValueField()));

		return $this->checkFieldName($field) ? $field: null;
	}

	protected function getTitleField(): ?string
	{
		$field = trim((string)$this->getOption('titleField', $this->getDefaultTitleField()));

		return $this->checkFieldName($field) ? $field: null;
	}

	protected function getOrderField(): ?string
	{
		$field = trim((string)$this->getOption('orderField', $this->getDefaultOrderField()));

		return $this->checkFieldName($field) ? $field: null;
	}

	protected function checkFieldName(string $fieldName): bool
	{
		if ($fieldName === '')
		{
			return false;
		}

		return isset($this->fields[$fieldName]);
	}

	protected function getDirection(): string
	{
		$result = mb_strtoupper(trim((string)$this->getOption('direction')));

		return $result === 'DESC' ? 'DESC' : 'ASC';
	}

	protected function getQueryMethod(): string
	{
		return trim((string)$this->getOption('queryMethod', $this->getDefaultQueryMethod()));
	}

	protected function prepareGetElementsParams(array $settings = []): ?array
	{
		$fields = [
			'VALUE' => $this->getValueField(),
			'TITLE' => $this->getTitleField(),
			'ORDER' => $this->getOrderField(),
		];
		if (in_array(null, $fields, true))
		{
			return null;
		}

		$select = [
			'ID',
			'VALUE' => $fields['VALUE'],
			'TITLE' => $fields['TITLE'],
		];
		if ($fields['ORDER'] !== 'ID')
		{
			$select[] = $fields['ORDER'];
		}

		$result = [
			'select' => $select,
			'filter' => $settings['filter'] ?? [],
			'order' => [
				$fields['ORDER'] => $this->getDirection(),
				'ID' => 'ASC',
			],
		];

		if (isset($settings['limit']))
		{
			$result['limit'] = $settings['limit'];
		}

		return $result;
	}

	protected function getQueryFilter(SearchQuery $searchQuery): ?array
	{
		$query = $searchQuery->getQuery();
		if (mb_strlen($query) < 2)
		{
			return [];
		}
		$titleField = $this->getTitleField();
		if ($titleField === null)
		{
			return null;
		}

		return match ($this->getQueryMethod())
		{
			self::QUERY_SEARCH => [
				'*'.$titleField => $query,
			],
			self::QUERY_SUBSTRING => [
				'%'.$titleField => $query,
			],
			default => [
				$titleField => $query.'%',
			],
		};
	}

	protected function getFilterByIds(array $ids): array
	{
		if (empty($ids))
		{
			return [];
		}
		$valueField = $this->getValueField();
		if ($valueField === null)
		{
			return [];
		}

		return [
			'@' . $valueField => $ids,
		];
	}

	protected function getElements(array $settings = []): array
	{
		$params = $this->prepareGetElementsParams($settings);
		if ($params === null)
		{
			return [];
		}

		$result = [];
		$ormClass = $this->highloadblock->getDataClass();
		$iterator = $ormClass::getList($params);
		while ($row = $iterator->fetch())
		{
			$result[] = $row;
		}
		unset ($row, $iterator);

		return $result;
	}

	protected function makeItem(array $row): Item
	{
		$item = [
			'id' => $row['VALUE'],
			'entityId' => self::ENTITY_ID,
			'title' => $row['TITLE'],
		];

		return new Item($item);
	}
}
