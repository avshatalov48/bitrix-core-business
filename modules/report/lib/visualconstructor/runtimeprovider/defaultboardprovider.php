<?php

namespace Bitrix\Report\VisualConstructor\RuntimeProvider;

use Bitrix\Report\VisualConstructor\Entity\Dashboard;
use Bitrix\Report\VisualConstructor\Internal\Manager\DefaultBoardManager;
use Bitrix\Report\VisualConstructor\IProvidable;

/**
 * Class DefaultBoardProvider
 * @package Bitrix\Report\VisualConstructor\RuntimeProvider
 * @method Dashboard|null getFirstResult()
 */
class DefaultBoardProvider extends Base
{

	/**
	 * @return array
	 */
	protected function availableFilterKeys()
	{
		return array('primary', 'boardKey');
	}

	/**
	 * @return DefaultBoardManager
	 */
	protected function getManagerInstance()
	{
		return DefaultBoardManager::getInstance();
	}

	/**
	 * @return Dashboard[]
	 */
	protected function getEntitiesList()
	{
		return $this->getManagerInstance()->getDefaultBoardsList();
	}

	/**
	 * @return mixed
	 */
	protected function getIndices()
	{
		return $this->getManagerInstance()->getIndices();
	}
}