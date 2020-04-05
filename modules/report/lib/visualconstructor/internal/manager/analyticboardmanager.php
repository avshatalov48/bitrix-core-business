<?php
namespace Bitrix\Report\VisualConstructor\Internal\Manager;

use Bitrix\Report\VisualConstructor\AnalyticBoard;
use Bitrix\Report\VisualConstructor\Config\Common;

class AnalyticBoardManager extends Base
{
	private static $analyticBoardList = [];
	private static $indices = array(
		'boardKey' => array()
	);


	/**
	 * @return AnalyticBoard[]
	 */
	public function getAnalyticBoardsList()
	{
		return self::$analyticBoardList;
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
		return Common::EVENT_ANALYTIC_PAGE_COLLECT;

	}

	/**
	 * @return mixed
	 */
	public function call()
	{
		if (!self::$analyticBoardList)
		{
			/** @var \Bitrix\Report\VisualConstructor\AnalyticBoard[] $boards */
			$boards = $this->getResult();
			foreach ($boards as $board)
			{
				self::$analyticBoardList[$board->getBoardKey()] = $board;
				self::$indices['boardKey'][$board->getBoardKey()][] = $board->getBoardKey();
			}
		}
		return self::$analyticBoardList;
	}
}