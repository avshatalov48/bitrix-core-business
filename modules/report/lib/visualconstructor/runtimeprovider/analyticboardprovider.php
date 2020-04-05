<?php

namespace Bitrix\Report\VisualConstructor\RuntimeProvider;

use Bitrix\Report\VisualConstructor\AnalyticBoard;
use Bitrix\Report\VisualConstructor\Internal\Manager\AnalyticBoardManager;
use Bitrix\Report\VisualConstructor\IProvidable;

/**
 * Class AnalyticBoardProvider
 * @package Bitrix\Report\VisualConstructor\RuntimeProvider
 * @method AnalyticBoard|null getFirstResult()
 */
class AnalyticBoardProvider extends Base
{
	/**
	 * @return array
	 */
	protected function availableFilterKeys()
	{
		return ['primary', 'boardKey'];
	}

	/**
	 * @return AnalyticBoardManager
	 */
	protected function getManagerInstance()
	{
		return AnalyticBoardManager::getInstance();
	}

	/**
	 * @return AnalyticBoard[]
	 */
	protected function getEntitiesList()
	{
		return $this->getManagerInstance()->getAnalyticBoardsList();
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