<?php

namespace Bitrix\Report\VisualConstructor\Internal\Manager;

use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\View;

/**
 * Class ViewManager
 * @package Bitrix\Report\VisualConstructor\Internal\Manager
 */
class ViewManager extends Base
{
	private static $viewsList;
	private static $indices = array(
		'dataType' => array()
	);

	/**
	 * @return string
	 */
	protected function getEventTypeKey()
	{
		return Common::EVENT_VIEW_TYPE_COLLECT;
	}

	/**
	 * @return View[]
	 */
	public function getViewControllers()
	{
		return self::$viewsList;
	}

	/**
	 * @return array
	 */
	public function getIndices()
	{
		return self::$indices;
	}

	/**
	 * @return View[]
	 */
	public function call()
	{
		if (!self::$viewsList)
		{
			/** @var View[] $views */
			$views = $this->getResult();
			foreach ($views as $view)
			{
				self::$viewsList[$view->getKey()] = $view;
				self::$indices['dataType'][$view->getCompatibleDataType()][] = $view->getKey();

			}
		}
		return self::$viewsList;
	}
}