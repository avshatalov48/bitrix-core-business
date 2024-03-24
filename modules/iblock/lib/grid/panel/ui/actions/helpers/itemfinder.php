<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Helpers;

use Bitrix\Iblock\Grid\RowType;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\HttpRequest;

trait ItemFinder
{
	abstract protected function getIblockId(): int;

	abstract protected function getListMode(): string;

	abstract protected function getRequestRows(HttpRequest $request): ?array;

	protected function prepareItemIds(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter = null): array
	{
		if ($isSelectedAllRows)
		{
			return $this->getItemIdsByFilter($filter);
		}
		else
		{
			$ids = $this->getRequestRows($request);
			if (empty($ids))
			{
				return [[], []];
			}

			[$elementIds, $sectionIds] = RowType::parseIndexList($ids);

			return [
				$this->validateElementIds($elementIds),
				$this->validateSectionIds($sectionIds)
			];
		}
	}

	private function getPageSize(): int
	{
		return 500;
	}

	protected function getBaseFilter(): array
	{
		return [
			'IBLOCK_ID' => $this->getIblockId(),
			'CHECK_PERMISSIONS' => 'Y',
			'MIN_PERMISSION' => 'R',
		];
	}

	protected function getItemIdsByFilter(?Filter $filter = null): array
	{
		$itemFilter = $this->getBaseFilter();

		if ($filter !== null)
		{
			$itemFilter += $filter->getValue();
		}

		$elementIds = [];
		$sectionIds = [];

		$iterator = $this->getItemsList([
			'select' => ['ID'],
			'filter' => $itemFilter,
		]);
		while ($row = $iterator->Fetch())
		{
			$id = (int)$row['ID'];
			if (($row['TYPE'] ?? RowType::ELEMENT) === RowType::SECTION)
			{
				$sectionIds[] = $id;
			}
			else
			{
				$elementIds[] = $id;
			}
		}
		unset($row, $iterator);

		return [$elementIds, $sectionIds];
	}

	protected function getItemsByFilter(array $select, ?Filter $filter = null): array
	{
		$select[] = 'ID';
		$select = array_values(array_unique($select));

		$itemFilter = $this->getBaseFilter();

		if ($filter !== null)
		{
			$itemFilter += $filter->getValue();
		}

		$elements = [];
		$sections = [];

		$iterator = $this->getItemsList([
			'select' => $select,
			'filter' => $itemFilter,
		]);
		while ($row = $iterator->Fetch())
		{
			$row['ID'] = (int)$row['ID'];
			if (($row['TYPE'] ?? RowType::ELEMENT) === RowType::SECTION)
			{
				$sections[] = $row;
			}
			else
			{
				$elements[] = $row;
			}
		}
		unset($row, $iterator);

		return [$elements, $sections];
	}

	protected function getElementIdsByFilter(?Filter $filter = null): array
	{
		$itemFilter = $this->getBaseFilter();

		if ($filter !== null)
		{
			$itemFilter += $filter->getValue();
		}

		$elementIds = [];

		$iterator = \CIBlockElement::GetList(
			[],
			$itemFilter,
			false,
			false,
			['ID']
		);
		while ($row = $iterator->Fetch())
		{
			$elementIds[] = (int)$row['ID'];
		}
		unset($row, $iterator);

		return $elementIds;
	}

	protected function validateElementIds(array $elementIds): array
	{
		if (empty($elementIds))
		{
			return [];
		}

		$itemFilter = $this->getBaseFilter();

		$result = [];

		foreach (array_chunk($elementIds, $this->getPageSize()) as $pageIds)
		{
			$itemFilter['ID'] = $pageIds;
			$iterator = \CIBlockElement::GetList(
				[],
				$itemFilter,
				false,
				false,
				['ID']
			);
			while ($row = $iterator->Fetch())
			{
				$result[] = (int)$row['ID'];
			}
			unset($row, $iterator);
		}

		return $result;
	}

	protected function validateSectionIds(array $sectionIds): array
	{
		if (empty($sectionIds))
		{
			return [];
		}

		$itemFilter = $this->getBaseFilter();

		$result = [];

		foreach (array_chunk($sectionIds, $this->getPageSize()) as $pageIds)
		{
			$itemFilter['ID'] = $pageIds;
			$iterator = \CIBlockSection::GetList(
				[],
				$itemFilter,
				false,
				['ID']
			);
			while ($row = $iterator->Fetch())
			{
				$result[] = (int)$row['ID'];
			}
			unset($row, $iterator);
		}

		return $result;
	}

	protected function getElementsByIdList(array $select, array $elementIds): array
	{
		if (empty($elementIds))
		{
			return [];
		}
		$select[] = 'ID';
		$select = array_values(array_unique($select));

		$itemFilter = $this->getBaseFilter();

		$result = [];

		foreach (array_chunk($elementIds, $this->getPageSize()) as $pageIds)
		{
			$itemFilter['ID'] = $pageIds;
			$iterator = \CIBlockElement::GetList(
				[],
				$itemFilter,
				false,
				false,
				$select
			);
			while ($row = $iterator->Fetch())
			{
				$row['ID'] = (int)$row['ID'];
				$result[] = $row;
			}
			unset($row, $iterator);
		}

		return $result;
	}

	protected function getSectionsByIdList(array $select, array $sectionIds): array
	{
		if (empty($sectionIds))
		{
			return [];
		}
		$select[] = 'ID';
		$select = array_values(array_unique($select));

		$itemFilter = $this->getBaseFilter();

		$result = [];

		foreach (array_chunk($sectionIds, $this->getPageSize()) as $pageIds)
		{
			$itemFilter['ID'] = $pageIds;
			$iterator = \CIBlockSection::GetList(
				[],
				$itemFilter,
				false,
				$select
			);
			while ($row = $iterator->Fetch())
			{
				$row['ID'] = (int)$row['ID'];
				$result[] = $row;
			}
			unset($row, $iterator);
		}

		return $result;
	}

	private function getItemsList(array $params): \CDBResult
	{
		if ($this->getListMode() === IblockTable::LIST_MODE_COMBINED)
		{
			$iterator = \CIBlockSection::GetMixedList(
				[],
				$params['filter'],
				false,
				$params['select']
			);
		}
		else
		{
			$iterator = \CIBlockElement::GetList(
				[],
				$params['filter'],
				false,
				false,
				$params['select']
			);
		}

		return $iterator;
	}
}
