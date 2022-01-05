<?php
namespace Bitrix\Report\VisualConstructor\RuntimeProvider;

use Bitrix\Report\VisualConstructor\AnalyticBoardBatch;
use Bitrix\Report\VisualConstructor\Internal\Manager\AnalyticBoardBatchManager;
use Bitrix\Report\VisualConstructor\IProvidable;

class AnalyticBoardBatchProvider extends Base
{
	/**
	 * @return array
	 */
	protected function availableFilterKeys()
	{
		return ['primary', 'batchKey', 'group'];
	}

	/**
	 * @return AnalyticBoardBatchManager
	 */
	protected function getManagerInstance()
	{
		return AnalyticBoardBatchManager::getInstance();
	}

	/**
	 * @return AnalyticBoardBatch[]
	 */
	protected function getEntitiesList()
	{
		return $this->getManagerInstance()->getAnalyticBoardsBatchList();
	}

	/**
	 * @return mixed
	 *
	 */
	protected function getIndices()
	{
		return $this->getManagerInstance()->getIndices();
	}

	/**
	 * @param AnalyticBoardBatch[] $result
	 */
	protected function sortResults(&$result)
	{
		usort($result, function($a, $b)
		{
			/** @var \Bitrix\Report\VisualConstructor\AnalyticBoardBatch $a */
			/** @var \Bitrix\Report\VisualConstructor\AnalyticBoardBatch $b */
			return $a->getOrder() <=> $b->getOrder();
		});
	}

	/**
	 * @param array $entities
	 * @param array $filteredEntityIds
	 *
	 * @return array
	 */
	protected function applyFilters($entities, $filteredEntityIds)
	{
		$result = [];

		foreach ($entities as $key => $entity)
		{
			/** @var \Bitrix\Report\VisualConstructor\AnalyticBoardBatch $entity */
			if (in_array($key, $filteredEntityIds))
			{
				$this->processAvailableRelations($entity);
				$result[] = $entity;
			}
		}

		return $result;
	}
}