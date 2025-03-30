<?php

namespace Bitrix\Rest\Repository;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\Entity\Collection\AppCollection;
use Bitrix\Rest\EO_App_Collection;
use Bitrix\Rest\EO_App_Query;

class AppRepository implements \Bitrix\Rest\Contract\Repository\AppRepository
{
	public function __construct(
		private readonly \Bitrix\Rest\Model\Mapper\App $mapper,
	)
	{}

	public function getPaidApplications(): AppCollection
	{
		$appList = $this->buildPaidAppsQuery()
			->addSelect('*')
			->fetchCollection();

		return $this->createAppCollectionFromModelArray($appList);
	}

	public function hasPaidApps(): bool
	{
		$collection = $this->buildPaidAppsQuery()
			->setSelect(['ID'])
			->setLimit(1)
			->fetch();

		return !empty($collection);
	}

	/**
	 * @return EO_App_Query
	 */
	private function buildPaidAppsQuery(): Query
	{
		return AppTable::query()
			->addFilter('!=STATUS', AppTable::STATUS_LOCAL)
			->addFilter('=ACTIVE', AppTable::ACTIVE)
			->addFilter('IS_FREE', false);
	}

	/**
	 * @param EO_App_Collection $modelCollection
	 * @return AppCollection
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function createAppCollectionFromModelArray(EO_App_Collection $modelCollection): AppCollection
	{
		$collection = new AppCollection();
		foreach ($modelCollection as $model)
		{
			$collection->add($this->mapper->mapModelToEntity($model));
		}

		return $collection;
	}
}
