<?php
namespace Bitrix\Report\VisualConstructor\RuntimeProvider;

use Bitrix\Report\VisualConstructor\Handler\BaseWidget;
use Bitrix\Report\VisualConstructor\Internal\Manager\WidgetManager;

/**
 * @method BaseWidget|null getFirstResult()
 * Class WidgetProvider
 * @package Bitrix\Report\VisualConstructor\RuntimeProvider
 */
class WidgetProvider extends Base
{
	/**
	 * @return array
	 */
	protected function availableFilterKeys()
	{
		return array('widgetClassName', 'primary');
	}

	/**
	 * @return \Bitrix\Report\VisualConstructor\Internal\Manager\WidgetManager
	 */
	protected function getManagerInstance()
	{
		return WidgetManager::getInstance();
	}

	/**
	 * @return IProvidable[]
	 */
	protected function getEntitiesList()
	{
		return $this->getManagerInstance()->getWidgetList();
	}

	/**
	 * @return mixed
	 */
	protected function getIndices()
	{
		return $this->getManagerInstance()->getIndices();
	}


}