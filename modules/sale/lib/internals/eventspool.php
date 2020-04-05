<?php
namespace Bitrix\Sale\Internals;

class EventsPool extends PoolBase
{
	protected static $events = array();

	public static function getEvents($code)
	{
		$resultList = array();
		$list = parent::getPoolByCode($code);

		if (is_array($list) && !empty($list))
		{
			foreach ($list as $eventName => $eventData)
			{
				$resultList[$eventName] = reset($eventData);
			}

			$list = $resultList;
		}

		return $list;
	}

	public static function getEventsByType($code, $type)
	{
		$data = parent::get($code, $type);
		if (!empty($data))
		{
			$data = reset($data);
		}

		return $data;
	}

	/**
	 * @param $code
	 * @param $type
	 * @param $event
	 */
	public static function addEvent($code, $type, $event)
	{
		parent::add($code, $type, $event);
	}

	/**
	 * @param $code
	 * @param $type
	 *
	 * @return bool
	 */
	public static function isEventTypeExists($code, $type)
	{
		return parent::isTypeExists($code, $type);
	}

	/**
	 * @param null $code
	 * @param null $type
	 */
	public static function resetEvents($code = null, $type = null)
	{
		parent::resetPool($code, $type);
	}
}
