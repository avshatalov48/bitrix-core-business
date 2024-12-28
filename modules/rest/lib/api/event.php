<?php
namespace Bitrix\Rest\Api;


use Bitrix\Bitrix24\Feature;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\AuthTypeException;
use Bitrix\Rest\EventOfflineTable;
use Bitrix\Rest\EventTable;
use Bitrix\Rest\HandlerHelper;
use Bitrix\Rest\LicenseException;
use Bitrix\Rest\OAuth\Auth;
use Bitrix\Rest\RestException;
use Bitrix\Rest\Exceptions;

class Event extends \IRestService
{
	const FEATURE_EXTENDED_MODE = 'rest_offline_extended';

	/**
	 * Returns description of events REST API
	 *
	 * @return array
	 */
	public static function onRestServiceBuildDescription()
	{
		return array(
			\CRestUtil::GLOBAL_SCOPE => array(
				'events' => array(__CLASS__, 'eventsList'),
				'event.bind' => array(__CLASS__, 'eventBind'),
				'event.unbind' => array(__CLASS__, 'eventUnBind'),
				'event.get' => array(__CLASS__, 'eventGet'),
				'event.offline.get' => array(__CLASS__, 'eventOfflineGet'),
				'event.offline.clear' => array(__CLASS__, 'eventOfflineClear'),
				'event.offline.error' => array(__CLASS__, 'eventOfflineError'),
				'event.offline.list' => array(__CLASS__, 'eventOfflineList'),

				'event.test' => array(
					'callback' => array(__CLASS__, 'eventTest'),
					'options' => array()
				),
				\CRestUtil::EVENTS =>  array(
					'onOfflineEvent' => array(
						'rest',
						'onAfterOfflineEventCall',
						array(EventOfflineTable::class, 'prepareOfflineEvent'),
						array(
							"sendRefreshToken" => true,
							"disableOffline" => true,
							"allowOptions" => [
								'minTimeout' => 'int'
							],
						),
					)
				),
			),
		);
	}

	/**
	 * /rest/events method handler
	 *
	 * Administrator rights required
	 *
	 * Query format:
	 *
	 * SCOPE - limit events list by some scope
	 * FULL - get all events regardless of application scope
	 *
	 * @param array $query
	 * @param $n
	 * @param \CRestServer $server
	 *
	 * @return array
	 *
	 * @throws AuthTypeException
	 */
	public static function eventsList($query, $n, \CRestServer $server)
	{
		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException();
		}

		$serviceDescription = $server->getServiceDescription();

		$scopeList = array(\CRestUtil::GLOBAL_SCOPE);
		$result = array();

		$query = array_change_key_case($query, CASE_UPPER);

		if(isset($query['SCOPE']))
		{
			if($query['SCOPE'] != '')
			{
				$scopeList = array($query['SCOPE']);
			}
		}
		elseif(isset($query['FULL']) && $query['FULL'])
		{
			$scopeList = array_keys($serviceDescription);
		}
		else
		{
			$scopeList = $server->getAuthScope();
			$scopeList[] = \CRestUtil::GLOBAL_SCOPE;
		}

		foreach ($serviceDescription as $scope => $scopeMethods)
		{
			if(in_array($scope, $scopeList) && isset($scopeMethods[\CRestUtil::EVENTS]))
			{
				$result = array_merge($result, array_keys($scopeMethods[\CRestUtil::EVENTS]));
			}
		}

		return $result;
	}


	/**
	 * /rest/event.bind method handler
	 *
	 * Administrator rights required
	 *
	 * Query format:
	 *
	 * - EVENT - event name
	 * - EVENT_TYPE = {online|offline} - type of event handling. Default: online
	 * - AUTH_TYPE - User ID, whose auth will be generated for handler. Useless for offline type. Default value is 0, which means getting auth for user, authorized when event is called
	 * - HANDLER - URL of event handler. Useless for offline type
	 *
	 * @param array $query
	 * @param $n
	 * @param \CRestServer $server
	 *
	 * @return bool
	 *
	 * @throws AccessException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws AuthTypeException
	 * @throws RestException
	 * @throws \Exception
	 */
	public static function eventBind($query, $n, \CRestServer $server)
	{
		global $USER;

		if($server->getAuthType() !== \Bitrix\Rest\OAuth\Auth::AUTH_TYPE)
		{
			throw new AuthTypeException();
		}

		$query = array_change_key_case($query, CASE_UPPER);

		$eventName = mb_strtoupper($query['EVENT'] ?? '');
		$eventType = mb_strtolower($query['EVENT_TYPE'] ?? '');
		$eventUser = intval($query['AUTH_TYPE'] ?? null);
		$eventCallback = $query['HANDLER'] ?? '';
		$options = isset($query['OPTIONS']) && is_array($query['OPTIONS']) ? $query['OPTIONS'] : [];

		if($eventUser > 0)
		{
			if(!\CRestUtil::isAdmin() && $eventUser !== intval($USER->GetID()))
			{
				throw new AccessException('Event binding with AUTH_TYPE requires administrator access rights');
			}
		}
		elseif(!\CRestUtil::isAdmin())
		{
			$eventUser = intval($USER->GetID());
		}

		$authData = $server->getAuthData();

		$connectorId = isset($authData['auth_connector']) ? $authData['auth_connector'] : '';

		if($eventName == '')
		{
			throw new Exceptions\ArgumentNullException("EVENT");
		}

		if($eventType <> '')
		{
			if(!in_array($eventType, array(EventTable::TYPE_ONLINE, EventTable::TYPE_OFFLINE)))
			{
				throw new Exceptions\ArgumentException('Value must be one of {'.EventTable::TYPE_ONLINE.'|'.EventTable::TYPE_OFFLINE.'}', 'EVENT_TYPE');
			}
		}
		else
		{
			$eventType = EventTable::TYPE_ONLINE;
		}

		if($eventType === EventTable::TYPE_OFFLINE)
		{
			if(!\CRestUtil::isAdmin())
			{
				throw new AccessException('Offline events binding requires administrator access rights');
			}

			$eventCallback = '';
			$eventUser = 0;
		}
		elseif($eventCallback == '' && $eventType === EventTable::TYPE_ONLINE)
		{
			throw new Exceptions\ArgumentNullException("HANDLER");
		}

		$clientInfo = AppTable::getByClientId($server->getClientId());

		if($eventCallback == '' || HandlerHelper::checkCallback($eventCallback, $clientInfo))
		{
			$scopeList = $server->getAuthScope();
			$scopeList[] = \CRestUtil::GLOBAL_SCOPE;

			$serviceDescription = $server->getServiceDescription();

			foreach($scopeList as $scope)
			{
				if(
					isset($serviceDescription[$scope])
					&& is_array($serviceDescription[$scope][\CRestUtil::EVENTS])
					&& array_key_exists($eventName, $serviceDescription[$scope][\CRestUtil::EVENTS])
				)
				{
					$eventInfo = $serviceDescription[$scope][\CRestUtil::EVENTS][$eventName];
					if(is_array($eventInfo))
					{
						$eventHandlerFields = array(
							'APP_ID' => $clientInfo['ID'],
							'EVENT_NAME' => $eventName,
							'EVENT_HANDLER' => $eventCallback,
							'CONNECTOR_ID' => $connectorId,
							'OPTIONS' => []
						);

						if($eventUser > 0)
						{
							$eventHandlerFields['USER_ID'] = $eventUser;
						}

						if (
							$eventCallback === ''
							&& isset($eventInfo[3]['disableOffline'])
							&& $eventInfo[3]['disableOffline'] === true
						)
						{
							throw new RestException('Offline event cannot be registered for this event.', RestException::ERROR_ARGUMENT);
						}

						if (!empty($options) && isset($eventInfo[3]['allowOptions']) && is_array($eventInfo[3]['allowOptions']))
						{
							foreach ($eventInfo[3]['allowOptions'] as $code => $type)
							{
								if (isset($options[$code]))
								{
									if ($type === 'int')
									{
										$eventHandlerFields['OPTIONS'][$code] = (int) $options[$code];
									}
									elseif($type === 'str' && is_string($options[$code]))
									{
										$eventHandlerFields['OPTIONS'][$code] = $options[$code];
									}
								}
							}
						}

						$lockKey = implode('|', [$clientInfo['ID'], $eventName, $eventCallback, $connectorId, $eventUser]);

						if (Application::getConnection()->lock($lockKey))
						{
							$result = EventTable::add($eventHandlerFields);
							Application::getConnection()->unlock($lockKey);
						}
						else
						{
							$result = (new Result())->addError(new Error('Process of binding the handler has already started'));
						}

						if($result->isSuccess())
						{
							\Bitrix\Rest\Event\Sender::bind($eventInfo[0], $eventInfo[1]);
						}
						else
						{
							$errorMessage = $result->getErrorMessages();
							throw new RestException('Unable to set event handler: '.implode('. ', $errorMessage), RestException::ERROR_CORE);
						}
					}

					return true;
				}
			}

			throw new RestException('Event not found', EventTable::ERROR_EVENT_NOT_FOUND);
		}
		else
		{
			return false;
		}
	}

	/**
	 * /rest/event.unbind method handler
	 *
	 * Returns count of unbinded events
	 *
	 * Administrator rights required
	 *
	 * Query format:
	 *
	 * - EVENT - event name
	 * - EVENT_TYPE = {online|offline} - type of event handling. Default: online
	 * - AUTH_TYPE - The same value as event.bind was called with. Useless for offline type. Default 0
	 * - HANDLER - URL of event handler. Useless for offline type
	 *
	 * @param array $query
	 * @param $n
	 * @param \CRestServer $server
	 *
	 * @return array
	 *
	 * @throws AccessException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws AuthTypeException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 */
	public static function eventUnbind($query, $n, \CRestServer $server)
	{
		global $USER;

		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException();
		}

		$query = array_change_key_case($query, CASE_UPPER);

		$eventName = mb_strtoupper($query['EVENT'] ?? '');
		$eventType = mb_strtolower($query['EVENT_TYPE'] ?? '');
		$eventCallback = $query['HANDLER'] ?? '';

		if($eventName == '')
		{
			throw new ArgumentNullException("EVENT");
		}

		if($eventType <> '')
		{
			if(!in_array($eventType, array(EventTable::TYPE_ONLINE, EventTable::TYPE_OFFLINE)))
			{
				throw new ArgumentException('Value must be one of {'.EventTable::TYPE_ONLINE.'|'.EventTable::TYPE_OFFLINE.'}', 'EVENT_TYPE');
			}
		}
		else
		{
			$eventType = EventTable::TYPE_ONLINE;
		}

		if($eventType === EventTable::TYPE_OFFLINE)
		{
			if(!\CRestUtil::isAdmin())
			{
				throw new AccessException('Offline events unbinding requires administrator access rights');
			}

			$eventCallback = '';
		}
		elseif($eventCallback == '')
		{
			throw new Exceptions\ArgumentNullException('HANDLER');
		}

		$clientInfo = AppTable::getByClientId($server->getClientId());

		$filter = array(
			'=APP_ID' => $clientInfo["ID"],
			'=EVENT_NAME' => $eventName,
			'=EVENT_HANDLER' => $eventCallback,
		);

		if($eventType === EventTable::TYPE_OFFLINE)
		{
			$authData = $server->getAuthData();
			$filter['=CONNECTOR_ID'] = isset($authData['auth_connector']) ? $authData['auth_connector'] : '';
		}
		else
		{
			if(isset($query['AUTH_TYPE']))
			{
				if(!\CRestUtil::isAdmin() && $query['AUTH_TYPE'] !== intval($USER->GetID()))
				{
					throw new AccessException('Event unbinding with AUTH_TYPE requires administrator access rights');
				}

				$filter['=USER_ID'] = intval($query['AUTH_TYPE']);
			}
			elseif(!\CRestUtil::isAdmin())
			{
				$filter['=USER_ID'] = intval($USER->GetID());
			}
		}

		$dbRes = EventTable::getList(array(
			'filter' => $filter,
			'select' => ['ID']
		));

		$cnt = 0;
		while($eventInfo = $dbRes->fetch())
		{
			$result = EventTable::delete($eventInfo["ID"]);
			if($result->isSuccess())
			{
				// we shouldn't make Unbind here, it'll be done during the first event call
				$cnt++;
			}
		}

		return array('count' => $cnt);
	}


	public static function eventGet($query, $n, \CRestServer $server)
	{
		global $USER;

		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException();
		}

		$result = array();

		$clientInfo = AppTable::getByClientId($server->getClientId());

		$filter = array(
			"=APP_ID" => $clientInfo["ID"],
		);

		if(!\CRestUtil::isAdmin())
		{
			$filter['=USER_ID'] = $USER->GetID();
		}

		$dbRes = EventTable::getList(array(
			"filter" => $filter,
			'order' => array(
				"ID" => "ASC",
			),
		));
		while($eventHandler = $dbRes->fetch())
		{
			if($eventHandler['EVENT_HANDLER'] <> '')
			{
				$result[] = array(
					"event" => $eventHandler['EVENT_NAME'],
					"handler" => $eventHandler['EVENT_HANDLER'],
					"auth_type" => $eventHandler['USER_ID'],
					"offline" => 0
				);
			}
			else
			{
				$result[] = array(
					"event" => $eventHandler['EVENT_NAME'],
					"connector_id" => $eventHandler['CONNECTOR_ID'] === null ? '' : $eventHandler['CONNECTOR_ID'],
					"offline" => 1
				);
			}
		}

		return $result;
	}


	public static function eventTest($query, $n, \CRestServer $server)
	{
		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException();
		}

		$clientInfo = AppTable::getByClientId($server->getClientId());

		foreach(GetModuleEvents("rest", "OnRestAppTest", true) as $event)
		{
			ExecuteModuleEventEx($event, array(array(
				"APP_ID" => $clientInfo["ID"],
				"QUERY" => $query
			)));
		}

		return 1;
	}


	public static function eventOfflineGet($query, $n, \CRestServer $server)
	{
		if ($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException();
		}

		if (!\CRestUtil::isAdmin())
		{
			throw new AccessException();
		}

		$query = array_change_key_case($query, CASE_LOWER);

		$clearEvents = !isset($query['clear']) ? 1 : intval($query['clear']);
		$processId = isset($query['process_id']) ? trim($query['process_id']) : null;

		if (!$clearEvents && !static::isExtendedModeEnabled())
		{
			throw new LicenseException('extended offline events handling');
		}

		$filter = isset($query['filter']) ? $query['filter'] : array();
		$order = isset($query['order']) ? $query['order'] : array('TIMESTAMP_X' => 'ASC');
		$limit = isset($query['limit']) ? intval($query['limit']) : static::LIST_LIMIT;

		$getErrors = isset($query['error']) && intval($query['error']) === 1;

		$authData = $server->getAuthData();
		$connectorId = isset($authData['auth_connector']) ? $authData['auth_connector'] : '';

		$returnProcessId = !$clearEvents;

		if ($limit <= 0)
		{
			throw new Exceptions\ArgumentException('Value must be positive integer', 'LIMIT');
		}

		$queryFilter = static::sanitizeFilter($filter);

		$order = static::sanitizeOrder($order);

		$clientInfo = AppTable::getByClientId($server->getClientId());

		$queryFilter['=APP_ID'] = $clientInfo['ID'];
		$queryFilter['=CONNECTOR_ID'] = $connectorId;
		$queryFilter['=ERROR'] = $getErrors ? 1 : 0;

		if ($processId === null)
		{
			$queryFilter['=PROCESS_ID'] = '';
			$processId = EventOfflineTable::markEvents($queryFilter, $order, $limit);
		}
		else
		{
			$returnProcessId = true;
		}

		$queryFilter['=PROCESS_ID'] = $processId;

		$dbRes = EventOfflineTable::getList(array(
			'select' => array(
				'ID', 'TIMESTAMP_X', 'EVENT_NAME', 'EVENT_DATA', 'EVENT_ADDITIONAL', 'MESSAGE_ID'
			),
			'filter' => $queryFilter,
			'limit' => $limit,
			'order' => $order,
		));

		$result = array();

		while ($event = $dbRes->fetch())
		{
			/** @var DateTime $ts */
			$ts = $event['TIMESTAMP_X'];

			$event['TIMESTAMP_X'] = \CRestUtil::convertDateTime($ts->toString());

			if (isset($event['EVENT_ADDITIONAL'][Auth::PARAM_LOCAL_USER]))
			{
				$event['EVENT_ADDITIONAL'] = [
					'user_id' => $event['EVENT_ADDITIONAL'][Auth::PARAM_LOCAL_USER],
				];
			}

			$result[] = $event;
		}

		if ($clearEvents && count($result) > 0)
		{
			EventOfflineTable::clearEvents($processId, $clientInfo['ID'], $connectorId);
		}

		return array(
			'process_id' => $returnProcessId ? $processId : null,
			'events' => $result
		);
	}

	public static function eventOfflineClear($query, $n, \CRestServer $server)
	{
		if ($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException();
		}

		if (!\CRestUtil::isAdmin())
		{
			throw new AccessException();
		}

		$query = array_change_key_case($query, CASE_LOWER);

		$processId = isset($query['process_id']) ? trim($query['process_id']) : null;

		$authData = $server->getAuthData();
		$connectorId = isset($authData['auth_connector']) ? $authData['auth_connector'] : '';

		if ($processId === null)
		{
			throw new Exceptions\ArgumentNullException('PROCESS_ID');
		}

		$clientInfo = AppTable::getByClientId($server->getClientId());

		if (isset($query['message_id']))
		{
			$listIds = false;
			if (!is_array($query['message_id']))
			{
				throw new Exceptions\ArgumentException('Value must be array of MESSAGE_ID values', 'message_id');
			}

			foreach($query['message_id'] as $messageId)
			{
				$messageId = trim($messageId);

				if (mb_strlen($messageId) !== 32)
				{
					throw new Exceptions\ArgumentException('Value must be array of MESSAGE_ID values', 'messsage_id');
				}

				$listIds[] = $messageId;
			}

			EventOfflineTable::clearEventsByMessageId($processId, $clientInfo['ID'], $connectorId, $listIds);
		}
		else
		{
			$listIds = false;
			if (isset($query['id']))
			{
				if (!is_array($query['id']))
				{
					throw new Exceptions\ArgumentException('Value must be array of integers', 'id');
				}

				foreach($query['id'] as $id)
				{
					$id = intval($id);

					if ($id <= 0)
					{
						throw new Exceptions\ArgumentException('Value must be array of integers', 'id');
					}

					$listIds[] = $id;
				}
			}

			EventOfflineTable::clearEvents($processId, $clientInfo['ID'], $connectorId, $listIds);
		}

		return true;
	}

	public static function eventOfflineError($query, $n, \CRestServer $server)
	{
		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException();
		}

		if(!\CRestUtil::isAdmin())
		{
			throw new AccessException();
		}

		$query = array_change_key_case($query, CASE_LOWER);

		$processId = isset($query['process_id']) ? trim($query['process_id']) : null;
		$messageId = isset($query['message_id']) ? $query['message_id'] : null;

		$authData = $server->getAuthData();
		$connectorId = isset($authData['auth_connector']) ? $authData['auth_connector'] : '';

		if($processId === null)
		{
			throw new ArgumentNullException('PROCESS_ID');
		}

		if(!is_array($messageId))
		{
			throw new ArgumentException('Value must be array of MESSAGE_ID values', 'message_id');
		}

		$clientInfo = AppTable::getByClientId($server->getClientId());
		if(count($messageId) > 0)
		{
			EventOfflineTable::markError($processId, $clientInfo['ID'], $connectorId, $messageId);
		}

		return true;
	}

	public static function eventOfflineList($query, $n, \CRestServer $server)
	{
		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException();
		}

		if(!\CRestUtil::isAdmin())
		{
			throw new AccessException();
		}

		$query = array_change_key_case($query, CASE_LOWER);

		$filter = isset($query['filter']) ? $query['filter'] : array();
		$order = isset($query['order']) ? $query['order'] : array('ID' => 'ASC');

		$authData = $server->getAuthData();
		$connectorId = isset($authData['auth_connector']) ? $authData['auth_connector'] : '';

		$queryFilter = static::sanitizeFilter($filter, array('ID', 'TIMESTAMP_X', 'EVENT_NAME', 'MESSAGE_ID', 'PROCESS_ID', 'ERROR'));

		$order = static::sanitizeOrder($order, array('ID', 'TIMESTAMP_X', 'EVENT_NAME', 'MESSAGE_ID', 'PROCESS_ID', 'ERROR'));

		$clientInfo = AppTable::getByClientId($server->getClientId());

		$queryFilter['=APP_ID'] = $clientInfo['ID'];

		$getEventQuery = EventOfflineTable::query();

		if ($connectorId === '')
		{
			$getEventQuery->where('CONNECTOR_ID', '');
		}
		else
		{
			$queryFilter['=CONNECTOR_ID'] = $connectorId;
		}

		$navParams = static::getNavData($n, true);

		$getEventQuery
			->setSelect(['ID', 'TIMESTAMP_X', 'EVENT_NAME', 'EVENT_DATA', 'EVENT_ADDITIONAL', 'MESSAGE_ID', 'PROCESS_ID', 'ERROR'])
			->setFilter($queryFilter)
			->setOrder($order)
			->setLimit($navParams['limit'])
			->setOffset($navParams['offset']);

		$result = array();
		$dbRes = $getEventQuery->exec();

		while($event = $dbRes->fetch())
		{
			/** @var DateTime $ts */
			$ts = $event['TIMESTAMP_X'];

			$event['TIMESTAMP_X'] = \CRestUtil::convertDateTime($ts->toString());

			if (isset($event['EVENT_ADDITIONAL'][Auth::PARAM_LOCAL_USER]))
			{
				$event['EVENT_ADDITIONAL'] = [
					'user_id' => $event['EVENT_ADDITIONAL'][Auth::PARAM_LOCAL_USER],
				];
			}

			$result[] = $event;
		}

		return static::setNavData($result, array(
			"count" => $getEventQuery->queryCountTotal(),
			"offset" => $navParams['offset']
		));
	}

	protected static function sanitizeFilter($filter, array $availableFields = null, $valueCallback = null, array $availableOperations = null)
	{
		static $defaultFields = array('ID', 'TIMESTAMP_X', 'EVENT_NAME', 'MESSAGE_ID');

		if($availableFields === null)
		{
			$availableFields = $defaultFields;
		}

		return parent::sanitizeFilter(
			$filter,
			$availableFields,
			function($field, $value)
			{
				switch($field)
				{
					case 'TIMESTAMP_X':

						return DateTime::createFromUserTime(\CRestUtil::unConvertDateTime($value));

					break;
				}
				return $value;
			}
		);
	}

	protected static function sanitizeOrder($order, array $availableFields = null)
	{
		static $defaultFields = array('ID', 'TIMESTAMP_X', 'EVENT_NAME', 'MESSAGE_ID');

		if($availableFields === null)
		{
			$availableFields = $defaultFields;
		}

		return parent::sanitizeOrder($order, $availableFields);
	}

	protected static function isExtendedModeEnabled()
	{
		return !Loader::includeModule('bitrix24')
			|| Feature::isFeatureEnabled(static::FEATURE_EXTENDED_MODE);
	}
}