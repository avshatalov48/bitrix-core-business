<?php

namespace Bitrix\Report\VisualConstructor\Internal\Manager;

use Bitrix\Report\VisualConstructor\BaseWidgetHandler;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;
use Bitrix\Report\VisualConstructor\Internal\Error\Error;

/**
 * Class WidgetManager
 * @package Bitrix\Report\VisualConstructor\Internal\Manager
 */
class WidgetManager extends Base
{
	private static $widgetsList = array();
	private static $indices = array(
		'widgetClassName' => array()
	);
	private static $called = false;

	/**
	 * @return string
	 */
	protected function getEventTypeKey()
	{
		return Common::EVENT_WIDGET_COLLECT;
	}


	/**
	 * @return array|bool
	 */
	public function getWidgetList()
	{
		return $this->isCalled() ? self::$widgetsList : false;
	}

	/**
	 * @return array|bool
	 */
	public function getIndices()
	{
		return $this->isCalled() ? self::$indices : false;
	}

	/**
	 * Call special Event end build list of result and create index list for searchable keys
	 * @return void
	 */
	public function call()
	{
		if (!self::$called)
		{
			/** @var BaseWidget[] $widgets */
			$widgets = $this->getResult();
			foreach ($widgets as $key => $widget)
			{
				self::$widgetsList[$key] = $widget;
				self::$indices['widgetClassName'][$widget::getClassName()][] = $key;
			}
		}
		self::$called = true;
	}


	/**
	 * @return bool
	 */
	private function isCalled()
	{
		if (!self::$called)
		{
			$this->errors[]	= new Error('invoke call method manager before get some parameters');
			return false;
		}
		else
		{
			return true;
		}
	}

}