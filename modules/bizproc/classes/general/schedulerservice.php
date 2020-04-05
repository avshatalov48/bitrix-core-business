<?
use Bitrix\Bizproc\SchedulerEventTable;

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/classes/general/runtimeservice.php");

class CBPSchedulerService
	extends CBPRuntimeService
{
	/**
	 * @param bool $withType Return as array [value, type].
	 * @return int|array
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function getDelayMinLimit($withType = false)
	{
		$result = (int) \Bitrix\Main\Config\Option::get('bizproc', 'delay_min_limit', 0);
		if (!$withType)
			return $result;
		$type = 's';
		if ($result > 0)
		{
			if ($result % (3600 * 24) == 0)
			{
				$result = $result / (3600 * 24);
				$type = 'd';
			}
			elseif ($result % 3600 == 0)
			{
				$result = $result / 3600;
				$type = 'h';
			}
			elseif ($result % 60 == 0)
			{
				$result = $result / 60;
				$type = 'm';
			}
		}
		return array($result, $type);
	}

	public static function setDelayMinLimit($limit, $type = 's')
	{
		$limit = (int)$limit;
		switch ($type)
		{
			case 'd':
				$limit *= 3600 * 24;
				break;
			case 'h':
				$limit *= 3600;
				break;
			case 'm':
				$limit *= 60;
				break;
			default:
				break;
		}
		\Bitrix\Main\Config\Option::set('bizproc', 'delay_min_limit', $limit);
	}

	public function SubscribeOnTime($workflowId, $eventName, $expiresAt)
	{
		CTimeZone::Disable();

		$workflowId = preg_replace('#[^a-z0-9.]#i', '', $workflowId);
		$eventName = preg_replace('#[^a-z0-9._-]#i', '', $eventName);

		$minLimit = static::getDelayMinLimit(false);
		if ($minLimit > 0)
		{
			$minExpiresAt = time() + $minLimit;
			if ($minExpiresAt > $expiresAt)
				$expiresAt = $minExpiresAt;
		}

		$result = CAgent::AddAgent(
			"CBPSchedulerService::OnAgent('".$workflowId."', '".$eventName."', array('SchedulerService' => 'OnAgent'));",
			"bizproc",
			"N",
			10,
			"",
			"Y",
			date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATETIME), $expiresAt)
		);
		CTimeZone::Enable();
		return $result;
	}

	public function UnSubscribeOnTime($id)
	{
		CAgent::Delete($id);
	}

	public static function OnAgent($workflowId, $eventName, $arEventParameters = array())
	{
		try
		{
			CBPRuntime::SendExternalEvent($workflowId, $eventName, $arEventParameters);
		}
		catch (Exception $e)
		{
			
		}
	}

	public function SubscribeOnEvent($workflowId, $eventHandlerName, $eventModule, $eventName, $entityId = null)
	{
		$entityKey = null;
		if (is_array($entityId))
		{
			foreach ($entityId as $entityKey => $entityId)
				break;
		}
		elseif ($entityId !== null)
		{
			$entityKey = 0;
		}

		if (!SchedulerEventTable::isSubscribed($workflowId, $eventHandlerName, $eventModule, $eventName, $entityId))
		{
			SchedulerEventTable::add(array(
				'WORKFLOW_ID' => (string)$workflowId,
				'HANDLER' => (string)$eventHandlerName,
				'EVENT_MODULE' => (string)$eventModule,
				'EVENT_TYPE' => (string)$eventName,
				'ENTITY_ID' => (string)$entityId
			));
		}

		RegisterModuleDependences(
			$eventModule,
			$eventName,
			'bizproc',
			'CBPSchedulerService',
			'sendEvents',
			100,
			'',
			array($eventModule, $eventName, $entityKey)
		);
	}

	public function UnSubscribeOnEvent($workflowId, $eventHandlerName, $eventModule, $eventName, $entityId = null)
	{
		// Clean old-style registry entry.
		UnRegisterModuleDependences(
			$eventModule,
			$eventName,
			"bizproc",
			"CBPSchedulerService",
			"OnEvent",
			"",
			array($workflowId, $eventHandlerName, array('SchedulerService' => 'OnEvent', 'EntityId' => $entityId))
		);

		$entityKey = null;
		if (is_array($entityId))
		{
			foreach ($entityId as $entityKey => $entityId)
				break;
		}
		elseif ($entityId !== null)
		{
			$entityKey = 0;
		}

		SchedulerEventTable::deleteBySubscription($workflowId, $eventHandlerName, $eventModule, $eventName, $entityId);

		if (!SchedulerEventTable::hasSubscriptions($eventModule, $eventName))
		{
			UnRegisterModuleDependences(
				$eventModule,
				$eventName,
				'bizproc',
				'CBPSchedulerService',
				'sendEvents',
				'',
				array($eventModule, $eventName, $entityKey)
			);
		}
	}

	/**
	 * @deprecated
	 * @param $workflowId
	 * @param $eventName
	 * @param array $arEventParameters
	 */
	public static function OnEvent($workflowId, $eventName, $arEventParameters = array())
	{
		$num = func_num_args();
		if ($num > 3)
		{
			for ($i = 3; $i < $num; $i++)
				$arEventParameters[] = func_get_arg($i);
		}

		if (is_array($arEventParameters["EntityId"]))
		{
			foreach ($arEventParameters["EntityId"] as $key => $value)
			{
				if (!isset($arEventParameters[0][$key]) || $arEventParameters[0][$key] != $value)
					return;
			}
		}
		elseif ($arEventParameters["EntityId"] != null && $arEventParameters["EntityId"] != $arEventParameters[0])
			return;

		global $BX_MODULE_EVENT_LAST;
		$lastEvent = $BX_MODULE_EVENT_LAST;

		try
		{
			CBPRuntime::SendExternalEvent($workflowId, $eventName, $arEventParameters);
		}
		catch (Exception $e)
		{
			//Clean-up records if instance not found
			if (
				$e->getCode() === \CBPRuntime::EXCEPTION_CODE_INSTANCE_NOT_FOUND
				&& $lastEvent['TO_MODULE_ID'] == 'bizproc'
				&& $lastEvent['TO_CLASS'] == 'CBPSchedulerService'
				&& $lastEvent['TO_METHOD'] == 'OnEvent'
				&& is_array($lastEvent['TO_METHOD_ARG'])
				&& $lastEvent['TO_METHOD_ARG'][0] == $workflowId
			)
			{
				UnRegisterModuleDependences(
					$lastEvent['FROM_MODULE_ID'],
					$lastEvent['MESSAGE_ID'],
					"bizproc",
					"CBPSchedulerService",
					"OnEvent",
					"",
					$lastEvent['TO_METHOD_ARG']
				);
			}
		}
	}

	public static function sendEvents($eventModule, $eventName, $entityKey)
	{
		$eventParameters = array(
			'SchedulerService' => 'OnEvent',  // compatibility
			'eventModule' => $eventModule,
			'eventName' => $eventName
		);

		$num = func_num_args();
		if ($num > 3)
		{
			for ($i = 3; $i < $num; $i++)
				$eventParameters[] = func_get_arg($i);
		}

		$filter = array(
			'=EVENT_MODULE' => $eventModule,
			'=EVENT_TYPE' => $eventName
		);

		$entityId = null;
		if ($entityKey === 0 && isset($eventParameters[0]))
			$entityId = (string)$eventParameters[0];
		elseif ($entityKey !== null && isset($eventParameters[0][$entityKey]))
			$entityId = (string)$eventParameters[0][$entityKey];

		if ($entityId !== null)
			$filter['=ENTITY_ID'] = $entityId;

		$iterator = SchedulerEventTable::getList(array(
			'filter' => $filter
		));

		while ($row = $iterator->fetch())
		{
			try
			{
				CBPRuntime::SendExternalEvent($row['WORKFLOW_ID'], $row['HANDLER'], $eventParameters);
			}
			catch (Exception $e)
			{
				if ($e->getCode() === \CBPRuntime::EXCEPTION_CODE_INSTANCE_NOT_FOUND)
				{
					SchedulerEventTable::delete($row['ID']); //Check this.
				}
			}
		}
	}
}