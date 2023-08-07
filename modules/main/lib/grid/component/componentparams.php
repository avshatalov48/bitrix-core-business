<?php

namespace Bitrix\Main\Grid\Component;

use Bitrix\Main\Grid\Grid;

/**
 * Params for `main.ui.grid` component.
 */
final class ComponentParams
{
	private Grid $grid;

	public function __construct(Grid $grid)
	{
		$this->grid = $grid;
	}

	public static function get(Grid $grid, array $additionParams = []): array
	{
		return (new self($grid))->getParams($additionParams);
	}

	public function getParams(array $additionParams = []): array
	{
		$rows = $this->grid->prepareRows();
		$columns = $this->grid->prepareColumns();

		$pagination = $this->grid->getPagination();
		$issetPagination = isset($pagination);

		$actionsPanel = $this->grid->getPanel();
		$issetActionsPanel = isset($actionsPanel);
		$actionsPanelControls = null;
		if ($issetActionsPanel)
		{
			$actionsPanelControls = [
				'GROUPS' => [
					[
						'ITEMS' => $actionsPanel->getControls(),
					],
				],
			];
		}

		$pageSizes = null;
		if ($issetPagination)
		{
			$pageSizes = array_map(
				static function (int $size) {
					return [
						'NAME' => (string)$size,
						'VALUE' => (string)$size,
					];
				},
				$pagination->getPageSizes()
			);
		}

		return $additionParams + [
			// general
			'GRID_ID' => $this->grid->getId(),
			'ROWS' => $rows,
			'COLUMNS' => $columns,
			// pagination
			'NAV_OBJECT' => $pagination,
			'TOTAL_ROWS_COUNT' => $issetPagination ? $pagination->getRecordCount() : count($rows),
			'SHOW_PAGINATION' => $issetPagination,
			'SHOW_TOTAL_COUNTER' => true,
			'PAGE_SIZES' => $pageSizes,
			'SHOW_PAGESIZE' => $issetPagination,
			// actions
			'ACTION_PANEL' => $actionsPanelControls,
			'SHOW_ACTION_PANEL' => $issetActionsPanel,
			'SHOW_ROW_CHECKBOXES' => $issetActionsPanel,
			'SHOW_SELECTED_COUNTER' => $issetActionsPanel,
			// sort
			'ALLOW_COLUMNS_SORT' => true,
			'ALLOW_COLUMNS_RESIZE' => true,
			'ALLOW_SORT' => true,
			// ajax
			'AJAX_MODE' => 'Y',
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_STYLE' => 'N',
			'AJAX_OPTION_HISTORY' => 'N',
			// other
			'HANDLE_RESPONSE_ERRORS' => true,
			'SHOW_GRID_SETTINGS_MENU' => true,
		];
	}
}
