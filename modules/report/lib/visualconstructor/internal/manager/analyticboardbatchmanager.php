<?php

namespace Bitrix\Report\VisualConstructor\Internal\Manager;

use Bitrix\Report\VisualConstructor\AnalyticBoardBatch;
use Bitrix\Report\VisualConstructor\Config\Common;

class AnalyticBoardBatchManager extends Base
{
	private static $analyticBoardBatchList = [];
	private static $indices = array(
		'batchKey' => array()
	);

	/**
	 * @return AnalyticBoardBatch[]
	 */
	public function getAnalyticBoardsBatchList()
	{
		return self::$analyticBoardBatchList;
	}

	/**
	 * @return array
	 */
	public function getIndices()
	{
		return self::$indices;
	}

	/**
	 * @return string
	 */
	protected function getEventTypeKey()
	{
		return Common::EVENT_ANALYTIC_PAGE_BATCh_COLLECT;

	}

	/**
	 * @return mixed
	 */
	public function call()
	{
		if (!self::$analyticBoardBatchList)
		{
			/** @var \Bitrix\Report\VisualConstructor\AnalyticBoardBatch[] $batches */
			$batches = $this->getResult();
			foreach ($batches as $batch)
			{
				self::$analyticBoardBatchList[$batch->getKey()] = $batch;
				self::$indices['batchKey'][$batch->getKey()][] = $batch->getKey();
			}
		}
		return self::$analyticBoardBatchList;
	}
}