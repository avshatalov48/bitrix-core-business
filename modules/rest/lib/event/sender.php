<?php

namespace Bitrix\Rest\Event;

use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Rest\Application;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\OAuth\Auth;
use Bitrix\Rest\OAuthService;
use Bitrix\Rest\Sqs;
use Bitrix\Rest\Tools\Diagnostics\Event\Logger;
use Bitrix\Rest\Tools\Diagnostics\Event\LogType;
use Bitrix\Rest\UsageStatTable;
use Bitrix\Rest\Tools\Diagnostics\LoggerManager;

/**
 * Class Sender
 *
 * Transport and utility for REST events.
 *
 * @package Bitrix\Rest
 **/
class Sender
{
	protected static $initialized = false;
	protected static $forkSet = false;
	protected static $queryData = array();

	/**
	 * @var ProviderInterface
	 */
	protected static $provider;

	/**
	 * @var ProviderOfflineInterface
	 */
	protected static $providerOffline;

	protected static $defaultEventParams = array(
		"category" => Sqs::CATEGORY_DEFAULT,
		"sendAuth" => true,
		"sendRefreshToken" => false,
	);

	protected static array $appData = [];

	/**
	 * Utility function to parse pseudo-method name
	 *
	 * @param string $name Pseudo-method name.
	 *
	 * @return array
	 */
	public static function parseEventName($name)
	{
		$res = array();
		[$res['MODULE_ID'], $res['EVENT']] = explode('__', $name);

		$res['EVENT'] = str_replace('_0_', '\\', $res['EVENT']);
		$res['EVENT'] = str_replace('_1_', '::', $res['EVENT']);

		return $res;
	}

	/**
	 * Binds REST event handler on PHP event.
	 *
	 * @param string $moduleId Event owner module.
	 * @param string $eventName Event name.
	 */
	public static function bind($moduleId, $eventName)
	{
		$eventManager = EventManager::getInstance();
		$eventManager->registerEventHandler($moduleId, $eventName, "rest", "\\Bitrix\\Rest\\Event\\Callback", static::getHandlerName($moduleId, $eventName));
	}

	/**
	 * Unbinds REST event handler on PHP event.
	 *
	 * @param string $moduleId Event owner module.
	 * @param string $eventName Event name.
	 */
	public static function unbind($moduleId, $eventName)
	{
		$eventManager = EventManager::getInstance();
		$eventManager->unRegisterEventHandler($moduleId, $eventName, "rest", "\\Bitrix\\Rest\\Event\\Callback", static::getHandlerName($moduleId, $eventName));

		/* compatibility */
		$eventManager->unRegisterEventHandler($moduleId, $eventName, "rest", "CRestEventCallback", static::getHandlerName($moduleId, $eventName));
	}

	/**
	 * Getter for default event params array.
	 *
	 * @return array
	 */
	public static function getDefaultEventParams()
	{
		return static::$defaultEventParams;
	}

	/**
	 * Returns authorization array for event handlers and BP activities.
	 *
	 * @param string|int $appId Application ID or CODE.
	 * @param int $userId User ID which will be the owner of access_token.
	 * @param array $additionalData Additional data which will be stored with access_token.
	 * @param array $additional Event parameters. Keys sendAuth and sendRefreshToken supported.
	 *
	 * @return array|bool|null
	 */
	public static function getAuth($appId, $userId, array $additionalData = array(), array $additional = array())
	{
		$auth = null;

		$application = AppTable::getByClientId($appId);
		if($application)
		{
			if($userId > 0 && $additional["sendAuth"])
			{
				if(OAuthService::getEngine()->isRegistered())
				{
					$auth = Application::getAuthProvider()->get($application['CLIENT_ID'], $application['SCOPE'], $additionalData, $userId);

					if(is_array($auth) && !$additional["sendRefreshToken"])
					{
						unset($auth['refresh_token']);
					}
				}
			}

			if(!is_array($auth))
			{
				$auth = array(
					"domain" => Context::getCurrent()->getRequest()->getHttpHost(),
					"member_id" => \CRestUtil::getMemberId()
				);
			}

			$auth["application_token"] = \CRestUtil::getApplicationToken($application);
		}

		return $auth;
	}

	/**
	 * Calls or schedules the query to SQS.
	 *
	 * @param array $handlersList Event handlers to call.
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function call($handlersList)
	{
		global $USER;

		$offlineEvents = array();
		LoggerManager::getInstance()->getLogger()?->debug(
			"\n{delimiter}\n"
			. "{date} - {host}\n{delimiter}\n"
			. " Sender::call() starts.\n"
			. "{handlersList}\n{delimiter} ", [
			'handlersList' => $handlersList,
			'MESSAGE' => LogType::SENDER_CALL_START->value,
		]);

		foreach($handlersList as $handlerInfo)
		{
			$handler = $handlerInfo[0];
			$data = $handlerInfo[1];
			$additional = $handlerInfo[2];

			foreach(static::$defaultEventParams as $key => $value)
			{
				if(!isset($additional[$key]))
				{
					$additional[$key] = $value;
				}
			}

			$session = Session::get();
			if(!$session)
			{
				LoggerManager::getInstance()->getLogger()?->info(
					"\n{delimiter}\n"
					. "{date} - {host}\n{delimiter}\n"
					. "Session ttl exceeded {session}.\n", [
					'session' => $session,
					'MESSAGE' => LogType::CYCLIC_CALL_LIMIT->value,
				]);

				// ttl exceeded, kill session
				return;
			}

			$userId = $handler['USER_ID'] > 0
				? $handler['USER_ID']
				: (
					// USER object can be null if event runs in BP or agent
					is_object($USER) && $USER->isAuthorized()
						? $USER->getId()
						: 0
				);

			$authData = null;
			if(isset($handler['APP_ID']) && $handler['APP_ID'] > 0)
			{
				if (isset(static::$appData[$handler['APP_ID']]) && is_array(static::$appData[$handler['APP_ID']]))
				{
					$application = static::$appData[$handler['APP_ID']];
				}
				else
				{
					$select = [
						'CLIENT_ID',
						'ID',
						'CODE',
						'STATUS',
						'DATE_FINISH',
						'IS_TRIALED',
						'URL_DEMO',
						'APPLICATION_TOKEN',
						'CLIENT_SECRET',
						'SHARED_KEY',
					];
					$dbRes = AppTable::getByPrimary($handler['APP_ID'], ['select' => $select]);
					$application = $dbRes->fetch();
					static::$appData[$handler['APP_ID']] = $application;
				}


				$appStatus = \Bitrix\Rest\AppTable::getAppStatusInfo($application, '');
				if($appStatus['PAYMENT_ALLOW'] === 'Y')
				{
					$authData = array(
						Session::PARAM_SESSION => $session,
						Auth::PARAM_LOCAL_USER => $userId,
						"application_token" => \CRestUtil::getApplicationToken($application),
					);
				}

				if($handler['EVENT_HANDLER'] <> '')
				{
					UsageStatTable::logEvent($application['CLIENT_ID'], $handler['EVENT_NAME']);
				}
			}
			else
			{
				$application = array('CLIENT_ID' => null);

				$authData = array(
					Session::PARAM_SESSION => $session,
					Auth::PARAM_LOCAL_USER => $userId,
					'application_token' => $handler['APPLICATION_TOKEN'],
				);
			}

			if($authData)
			{
				if ($handler['EVENT_HANDLER'] !== '')
				{
					$eventBufferAddResult = Buffer::getInstance()->addEvent([
						$application['CLIENT_ID'],
						$handler['EVENT_HANDLER'],
						$handler['EVENT_NAME'],
						$authData,
						$data,
						$additional
					]);

					if ($eventBufferAddResult->isSuccess())
					{
						self::$queryData[] = Sqs::queryItem(
							$application['CLIENT_ID'],
							$handler['EVENT_HANDLER'],
							array(
								'event' => $handler['EVENT_NAME'],
								'event_handler_id' => $handler['ID'],
								'data' => $data,
								'ts' => time(),
							),
							$authData,
							$additional
						);
					}
				}
				else
				{
					$offlineEvents[] = array(
						'HANDLER' => $handler,
						'APPLICATION' => $application,
						'AUTH' => $authData,
						'DATA' => $data,
					);
				}
			}
		}

		if (count($offlineEvents) > 0)
		{
			LoggerManager::getInstance()->getLogger()?->debug(
				"\n{delimiter}\n"
				. "{date} - {host}\n{delimiter}\n"
				. "Event count: {eventCount}\n{delimiter}"
				. "Offline event list:\n"
				. "{offlineEvents}", [
				'eventCount' => count($offlineEvents),
				'offlineEvents' => $offlineEvents,
				'MESSAGE' => LogType::READY_OFFLINE_EVENT_LIST->value,
			]);
			static::getProviderOffline()->send($offlineEvents);
		}

		if (count(static::$queryData) > 0)
		{
			self::enqueueBackgroundJob();
		}
	}

	/**
	 * Sends all scheduled handlers to SQS.
	 */
	public static function send(): void
	{
		LoggerManager::getInstance()->getLogger()?->debug(
			"\n{delimiter}\n"
			. "{date} - {host}\n{delimiter}\n"
			. "Starts method Sender::send()\n"
			. "count: {eventCount}"
			. "Event list:\n"
			. "{eventList}", [
			'eventCount' => count(static::$queryData),
			'eventList' => static::$queryData,
			'MESSAGE' => LogType::SENDER_SEND_START->value,
		]);
		if (count(self::$queryData) > 0)
		{
			UsageStatTable::finalize();
			static::getProvider()->send(self::$queryData);
			self::$queryData = [];
			self::$forkSet = false;
		}
	}

	public static function queueEvent($queryItem)
	{
		self::$queryData[] = $queryItem;
		self::enqueueBackgroundJob();
	}

	/**
	 * @return ProviderInterface
	 */
	public static function getProvider()
	{
		static::initialize();

		if(!static::$provider)
		{
			static::$provider = static::getDefaultProvider();
		}

		return static::$provider;
	}

	/**
	 * @param ProviderInterface $provider
	 */
	public static function setProvider(ProviderInterface $provider)
	{
		static::$provider = $provider;
	}

	protected static function getDefaultProvider()
	{
		return ProviderOAuth::instance();
	}

	/**
	 * @return ProviderOfflineInterface
	 */
	public static function getProviderOffline()
	{
		static::initialize();

		if(!static::$providerOffline)
		{
			static::$providerOffline = static::getDefaultProviderOffline();
		}

		return static::$providerOffline;
	}

	/**
	 * @param ProviderOfflineInterface $providerOffline
	 */
	public static function setProviderOffline(ProviderOfflineInterface $providerOffline)
	{
		static::$providerOffline = $providerOffline;
	}

	protected static function enqueueBackgroundJob(): void
	{
		if (!static::$forkSet)
		{
			LoggerManager::getInstance()->getLogger()?->debug(
				"\n{delimiter}\n"
				. "{date} - {host}\n{delimiter}\n"
				. "Registers the background job to send the event.\n"
				. "count: {eventCount}", [
				'eventCount' => count(static::$queryData),
				'MESSAGE' => LogType::READY_ONLINE_EVENT_LIST->value,
			]);
			$application = \Bitrix\Main\Application::getInstance();
			$application->addBackgroundJob(
				job: [__CLASS__, 'send'],
				priority: $application::JOB_PRIORITY_LOW
			);
			static::$forkSet = true;
		}
	}

	protected static function getDefaultProviderOffline()
	{
		return ProviderOffline::instance();
	}

	protected static function initialize()
	{
		if(!static::$initialized)
		{
			static::$initialized = true;

			$event = new Event('rest', 'onEventManagerInitialize');
			$event->send();
		}
	}

	protected static function getHandlerName($moduleId, $eventName)
	{
		// \Bitrix\Rest\EventTable::on
		if(mb_strpos($eventName, '::') >= 0)
		{
			$handlerName = $moduleId.'__'.mb_strtoupper(str_replace(array("\\", '::'), array('_0_', '_1_'), $eventName));
		}
		else
		{
			$handlerName = $moduleId.'__'.mb_strtoupper($eventName);
		}

		return $handlerName;
	}
}
