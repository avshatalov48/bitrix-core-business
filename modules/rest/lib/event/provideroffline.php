<?php
namespace Bitrix\Rest\Event;

use Bitrix\Main\Loader;
use Bitrix\Pull;
use Bitrix\Rest\EventOfflineTable;
use Bitrix\Main\EventManager;
use Bitrix\Rest\Tools\Diagnostics\LoggerManager;

class ProviderOffline implements ProviderOfflineInterface
{
	/**
	 * @var ProviderOffline
	 */
	protected static $instance = null;

	public static function instance()
	{
		if(static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function send(array $eventList)
	{
		$serverAuthData = $this->getServerAuthData();

		$offlineEventsCount = array();
		$offlineEventsApp = array();

		foreach($eventList as $item)
		{
			$application = $item['APPLICATION'];
			$handler = $item['HANDLER'];

			if(
				$serverAuthData['client_id'] !== $application['CLIENT_ID']
				|| $serverAuthData['auth_connector'] !== $handler['CONNECTOR_ID']
			)
			{
				if(!isset($offlineEventsCount[$application['CLIENT_ID']]))
				{
					$offlineEventsCount[$application['CLIENT_ID']] = array();
				}

				if(!isset($offlineEventsCount[$application['CLIENT_ID']][$handler['CONNECTOR_ID']]))
				{
					$offlineEventsCount[$application['CLIENT_ID']][$handler['CONNECTOR_ID']] = 0;
				}

				EventOfflineTable::callEvent(array(
					'APP_ID' => $application['ID'],
					'EVENT_NAME' => $handler['EVENT_NAME'],
					'EVENT_DATA' => $item['DATA'],
					'EVENT_ADDITIONAL' => $item['AUTH'],
					'CONNECTOR_ID' => $handler['CONNECTOR_ID'],
				));

				$offlineEventsCount[$application['CLIENT_ID']][$handler['CONNECTOR_ID']]++;
				$offlineEventsApp[$application['ID']] = true;
			}
			else
			{
				$logger = LoggerManager::getInstance()->getLogger();
				if ($logger)
				{
					$logger->debug(
						"\n{delimiter}\n"
						. "{date} - {host}\n{delimiter}\n"
						. "Event skipped because initializer is current application. \n"
						. "auth: {serverAuthData}"
						. "app: {application}\n",
						[
							'serverAuthData' => $serverAuthData,
							'application' => $application,
						]
					);
				}
			}
		}

		if(count($offlineEventsCount) > 0)
		{
			$this->notifyApplications($offlineEventsCount);
		}

		if(count($offlineEventsApp) > 0)
		{
			$this->sendOfflineEvent(array_keys($offlineEventsApp));
		}
	}

	protected function getServerAuthData()
	{
		$server = \CRestServer::instance();
		$serverAuthData = array('auth_connector' => '', 'client_id' => '');
		if($server !== null)
		{
			$serverAuthData = $server->getAuthData();
			if(!isset($serverAuthData['auth_connector']))
			{
				$serverAuthData['auth_connector'] = '';
			}

			$serverAuthData['client_id'] = $server->getClientId();
		}

		return $serverAuthData;
	}



	protected function sendOfflineEvent(array $appList)
	{
		foreach (EventManager::getInstance()->findEventHandlers(
			"rest",
			"onAfterOfflineEventCall"
		) as $event)
		{
			ExecuteModuleEventEx($event, [['APP_LIST' => $appList]]);
		}
	}

	protected function notifyApplications(array $counters)
	{
		foreach($counters as $clientId => $connectorCounters)
		{
			if(is_array($connectorCounters) && count($connectorCounters) > 0)
			{
				$this->notifyApplication($clientId, $connectorCounters);
			}
		}
	}

	protected function notifyApplication($clientId, array $connectorCounters)
	{
		if(Loader::includeModule('pull'))
		{
			$eventParam = array();

			foreach($connectorCounters as $connectorId => $count)
			{
				$eventParam[] = array(
					'connector_id' => $connectorId,
					'count' => $count
				);
			}

			Pull\Event::add(Pull\Event::SHARED_CHANNEL, array(
				'module_id' => 'rest',
				'command' => 'event_offline',
				'params' => $eventParam,
			), $clientId);
		}
	}

}

