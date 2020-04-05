<?

namespace Bitrix\Main\UI\Selector;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Actions
{
	const GET_DATA = "getData";
	const GET_DEPARTMENT_DATA = "getDepartmentData";
	const SEARCH = "search";

	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}

	public static function processAjax($action = false, $options = array(), $requestFields = array())
	{
		$result = array();

		if (isset($requestFields['LD_SEARCH']) && $requestFields['LD_SEARCH'] == 'Y')
		{
			$action = self::SEARCH;
		}
		elseif (isset($requestFields['LD_DEPARTMENT_RELATION']) && $requestFields['LD_DEPARTMENT_RELATION'] == 'Y')
		{
			$action = self::GET_DEPARTMENT_DATA;
		}

		if (!in_array($action, self::getList()))
		{
			return $result;
		}

		$event = new Event("main", "OnUISelectorActionProcessAjax", array(
			'action' => $action,
			'options' => $options,
			'requestFields' => $requestFields
		));
		$event->send();
		$eventResultList = $event->getResults();

		if (is_array($eventResultList) && !empty($eventResultList))
		{
			foreach ($eventResultList as $eventResult)
			{
				if ($eventResult->getType() == EventResult::SUCCESS)
				{
					$resultParams = $eventResult->getParameters();
					$result = $resultParams['result'];
					break;
				}
			}
		}

		return $result;
	}
}