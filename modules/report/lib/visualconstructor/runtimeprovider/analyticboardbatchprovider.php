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
		return ['primary', 'batchKey'];
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
}