<?php

namespace Bitrix\Main\Grid;

use Bitrix\Main\Grid\Column\Columns;
use Bitrix\Main\Grid\Column\DataProvider\TabletColumnsProvider;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;

abstract class TabletGrid extends Grid
{
	abstract protected function getTabletClass(): string;

	final protected function getDataManager(): DataManager
	{
		$ref = new \ReflectionClass($this->getTabletClass());

		return $ref->newInstance();
	}

	final protected function getEntity(): Entity
	{
		return $this->getDataManager()->getEntity();
	}

	protected function createColumns(): Columns
	{
		return new Columns(
			new TabletColumnsProvider($this->getEntity()),
		);
	}
}
