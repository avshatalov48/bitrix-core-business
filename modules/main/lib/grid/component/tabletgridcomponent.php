<?php

namespace Bitrix\Main\Grid\Component;

use Bitrix\Main\Grid\Grid;
use Bitrix\Main\ORM\Data\DataManager;

abstract class TabletGridComponent extends GridComponent
{
	/**
	 * @return DataManager class name
	 */
	abstract protected function getTablet(): string;

	protected function fillPagination(Grid $grid): void
	{
		if (!$grid->getPagination())
		{
			return;
		}

		$grid->getPagination()->setRecordCount(
			$this->getTablet()::getCount(
				$grid->getOrmFilter()
			)
		);
	}

	protected function fillRows(Grid $grid): void
	{
		$grid->setRawRows(
			$this->getTablet()::getList(
				$grid->getOrmParams()
			)
		);
	}
}
