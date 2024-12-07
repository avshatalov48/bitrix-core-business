<?php

namespace Bitrix\Iblock\Integration\UI\Grid\General;

use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\UI\PageNavigation;
use CFile;

/**
 * Grid provider.
 *
 * Contains columns, actions, other options and parameters of the `main.ui.grid` component.
 */
abstract class BaseProvider
{
	private array $rows;
	private ?PageNavigation $pagination = null;

	/**
	 * Grid id.
	 *
	 * @return string
	 */
	abstract public function getId(): string;

	/**
	 * Columns.
	 *
	 * @return array
	 */
	abstract public function getColumns(): array;

	/**
	 * Template row.
	 *
	 * Using for creating new rows.
	 *
	 * @return array|null
	 */
	protected function getTemplateRow(): ?array
	{
		return null;
	}

	/**
	 * Set rows. Rows should be in the final (prepared) state.
	 *
	 * @see ::prepareRow
	 *
	 * @param array $rows Grid rows.
	 *
	 * @return void
	 */
	public function setRows(array $rows): void
	{
		$this->rows = $rows;
	}

	/**
	 * Rows.
	 *
	 * @return array
	 */
	protected function getRows(): array
	{
		$result = $this->rows ?? [];

		$templateRow = $this->getTemplateRow();
		if (isset($templateRow))
		{
			$templateRowId = 'template_0';

			$isHasTemplateRow = false;
			foreach ($result as $row)
			{
				if ($row['id'] === $templateRowId)
				{
					$isHasTemplateRow = true;

					break;
				}
			}

			if (!$isHasTemplateRow)
			{
				$templateRow['id'] = $templateRowId;
				array_unshift($result, $templateRow);
			}
		}

		return $result;
	}

	/**
	 * Pagination.
	 *
	 * @param PageNavigation $pagination Pagination description.
	 *
	 * @return void
	 */
	public function setNavObject(PageNavigation $pagination): void
	{
		$this->pagination = $pagination;
	}

	/**
	 * Pagination.
	 *
	 * @return PageNavigation|null
	 */
	protected function getNavObject(): ?PageNavigation
	{
		return $this->pagination;
	}

	/**
	 * Available page sizes.
	 *
	 * @see \Bitrix\Iblock\Integration\UI\Grid\General\PageSizes contains frequent values
	 *
	 * @return array
	 */
	public function getPageSizes(): array
	{
		return PageSizes::SIZE_5_10_20_50_100;
	}

	/**
	 * Default page size.
	 *
	 * @return int
	 */
	protected function getDefaultPageSize(): int
	{
		$sizes = $this->getPageSizes();
		$sizesCount = count($sizes);

		if ($sizesCount > 0)
		{
			$middleIndex = ceil($sizesCount / 2) - 1;
			if (isset($sizes[$middleIndex]))
			{
				return (int)$sizes[$middleIndex]['VALUE'];
			}
		}

		return 0;
	}

	/**
	 * Default sort.
	 *
	 * @return array|null
	 */
	protected function getDefaultSort(): ?array
	{
		return null;
	}

	/**
	 * Action panel.
	 *
	 * @return array|null
	 */
	protected function getActionPanel(): ?array
	{
		$snippet = new Snippet();

		$items = [];
		$items[] = $snippet->getRemoveButton();
		$items[] = $snippet->getEditButton();

		if ($items)
		{
			return [
				'GROUPS' => [
					[
						'ITEMS' => $items,
					],
				],
			];
		}

		return null;
	}

	/**
	 * AJAX id.
	 *
	 * @return string
	 */
	protected function getAjaxId(): string
	{
		return $this->getId();
	}

	/**
	 * Column name with identifier row.
	 *
	 * @return string
	 */
	protected function getRowIdColumn(): string
	{
		return 'ID';
	}

	/**
	 * Prepare row.
	 *
	 * @param array $rawRow Raw data for grid row.
	 *
	 * @return array
	 */
	public function prepareRow(array $rawRow): array
	{
		$isEditable = $this->isEditable($rawRow);

		return [
			'id' => $rawRow[$this->getRowIdColumn()],
			'data' => $rawRow,
			'editable' => $isEditable,
			'columns' => $this->getRowColumns($rawRow),
			'actions' => $this->getRowActions($rawRow, $isEditable),
		];
	}

	/**
	 * Row is editable.
	 *
	 * @param array $row
	 *
	 * @return bool
	 */
	protected function isEditable(array $row): bool
	{
		return true;
	}

	/**
	 * Row columns.
	 *
	 * Values that are displayed when viewing.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	protected function getRowColumns(array $row): array
	{
		$result = [];

		$columns = $this->getColumns();
		foreach ($columns as $column)
		{
			$colName = $column['id'] ?? null;
			if (!isset($colName))
			{
				continue;
			}

			$type = $column['type'] ?? null;
			if ($type === 'list')
			{
				$value = $row[$colName] ?? null;

				$items = $column['editable']['items'] ?? null;
				if (is_array($items))
				{
					$value = $items[$value] ?? null;
				}

				$result[$colName] = $value;
			}
			elseif ($type === 'image')
			{
				$value = null;

				$fileId = (int)($row[$colName] ?? null);
				if ($fileId > 0)
				{
					$value = CFile::GetPath($fileId);
				}

				$result[$colName] = $value;
			}
			else
			{
				$result[$colName] = $row[$colName] ?? null;
			}
		}

		return $result;
	}

	/**
	 * Leaves only the fields available for this grid.
	 *
	 * @param array $fields Raw field list for grid description.
	 *
	 * @return array
	 */
	public function cleanFields(array $fields): array
	{
		$availableFields = array_column($this->getColumns(), 'id');

		return array_filter(
			$fields,
			static fn($key) => isset($key) && in_array($key, $availableFields, true),
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Row actions.
	 *
	 * @param array $row
	 * @param bool $isEditable
	 *
	 * @return array
	 */
	protected function getRowActions(array $row, bool $isEditable): array
	{
		return [];
	}

	/**
	 * Convert provider to array.
	 *
	 * Corresponds to the parameters of the `main.ui.grid` component.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$pagination = $this->getNavObject();
		$actionPanel = $this->getActionPanel();
		$sort = $this->getDefaultSort();

		return [
			// general
			'GRID_ID' => $this->getId(),
			// rows
			'ROWS' => $this->getRows(),
			'COLUMNS' => $this->getColumns(),
			'SORT' => $sort,
			// navigation
			'NAV_OBJECT' => $pagination,
			'NAV_PARAM_NAME' => $pagination ? $pagination->getId() : null,
			'CURRENT_PAGE' => $pagination ? $pagination->getCurrentPage() : null,
			'TOTAL_ROWS_COUNT' => $pagination ? $pagination->getRecordCount() : 0,
			'SHOW_PAGINATION' => $pagination ? $pagination->getPageCount() > 1 : false,
			'SHOW_NAVIGATION_PANEL' => isset($pagination),
			'SHOW_PAGESIZE' => isset($pagination),
			'PAGE_SIZES' => $this->getPageSizes(),
			'DEFAULT_PAGE_SIZE' => $this->getDefaultPageSize(),
			// ajax
			'AJAX_ID' => $this->getAjaxId(),
			'AJAX_MODE' => 'Y',
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_STYLE' => 'N',
			'AJAX_OPTION_HISTORY' => 'N',
			// actions
			'ACTION_PANEL' => $actionPanel,
			'SHOW_ACTION_PANEL' => isset($actionPanel),
			// allows & shows
			'ALLOW_ROWS_SORT' => isset($sort),
			'SHOW_ROW_CHECKBOXES' => isset($actionPanel),
			'SHOW_CHECK_ALL_CHECKBOXES' => isset($actionPanel),
			'HANDLE_RESPONSE_ERRORS' => true,
			'USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP' => true,
			'ENABLE_FIELDS_SEARCH' => 'Y',
		];
	}
}
