<?php

namespace Bitrix\Report\VisualConstructor\Internal\Manager;

use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Entity\Dashboard;

/**
 * Manager for default boards, collect all default boards, an provide instruments to work with them
 * @package Bitrix\Report\VisualConstructor\Internal\Manager
 */
class DefaultBoardManager extends Base
{
	private static $defaultBoardsList;
	private static $indices = array(
		'boardKey' => array()
	);

	/**
	 * @return Dashboard[]
	 */
	public function getDefaultBoardsList()
	{
		return self::$defaultBoardsList;
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
		return Common::EVENT_DEFAULT_BOARDS_COLLECT;
	}

	/**
	 * @return mixed
	 */
	public function call()
	{
		if (!self::$defaultBoardsList)
		{
			/** @var Dashboard[] $boards */
			$boards = $this->getResult();
			foreach ($boards as $board)
			{
				self::$defaultBoardsList[$board->getBoardKey()] = $board;
				self::$indices['boardKey'][$board->getBoardKey()][] = $board->getBoardKey();

			}
		}
		return self::$defaultBoardsList;
	}


}