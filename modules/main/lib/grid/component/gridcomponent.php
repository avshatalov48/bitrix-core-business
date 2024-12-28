<?php

namespace Bitrix\Main\Grid\Component;

use Bitrix\Main\Grid\Grid;

abstract class GridComponent extends \CBitrixComponent
{
	abstract protected function createGrid(): Grid;

	abstract protected function fillRows(Grid $grid): void;

	protected function fillPagination(Grid $grid): void
	{
		// override if using
	}

	public function executeComponent(): void
	{
		$grid = $this->createGrid();
		$grid->processRequest();

		$this->fillPagination($grid);
		$this->fillRows($grid);

		$this->arResult['~GRID'] = $grid;
		$this->arResult['~FILTER'] = $grid->getFilter();

		$this->includeComponentTemplate();
	}
}
