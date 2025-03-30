<?php
namespace Bitrix\Rest\Event;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Pull;
use Bitrix\Rest\EventOfflineTable;
use Bitrix\Main\EventManager;
use Bitrix\Rest\Tools\Diagnostics\Event\Logger;
use Bitrix\Rest\Tools\Diagnostics\Event\LogType;
use Bitrix\Rest\Tools\Diagnostics\LoggerManager;

class ProviderOffline implements ProviderOfflineInterface
{
	/**
	 * @var ProviderOffline
	 */
	protected static $instance = null;

	private array $eventList = [];
	private bool $isFinaliseInit = false;

	public static function instance(): ProviderOffline
	{
		if (static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function send(array $eventList)
	{
		$this->eventList = array_merge($this->eventList, $eventList);
		$this->registerFinalize();
	}

	/**
	 * Registers background saving events.
	 */
	public function registerFinalize(): void
	{
		if (!$this->isFinaliseInit)
		{
			$this->isFinaliseInit = true;
			$application = Application::getInstance();
			$application->addBackgroundJob(
				job: [__CLASS__, 'runFinalize'],
				priority: $application::JOB_PRIORITY_LOW
			);
		}
	}

	/**
	 * Runs finalize
	 */
	public static function runFinalize(): void
	{
		$instance = static::instance();
		$instance->finalize();
	}

	/**
	 * Saves events,
	 */
	public function finalize(): void
	{
		$serverAuthData = $this->getServerAuthData();

		$offlineEventsCount = [];
		$offlineEventsApp = [];

		foreach ($this->eventList as $item)
		{
			$application = $item['APPLICATION'];
			$handler = $item['HANDLER'];

			if (
				$serverAuthData['client_id'] !== $application['CLIENT_ID']
				|| $serverAuthData['auth_connector'] !== $handler['CONNECTOR_ID']
			)
			{
				if (!isset($offlineEventsCount[$application['CLIENT_ID']]))
				{
					$offlineEventsCount[$application['CLIENT_ID']] = [];
				}

				if (!isset($offlineEventsCount[$application['CLIENT_ID']][$handler['CONNECTOR_ID']]))
				{
					$offlineEventsCount[$application['CLIENT_ID']][$handler['CONNECTOR_ID']] = 0;
				}

				EventOfflineTable::callEvent(
					[
						'APP_ID' => $application['ID'],
						'EVENT_NAME' => $handler['EVENT_NAME'],
						'EVENT_DATA' => $item['DATA'],
						'EVENT_ADDITIONAL' => $item['AUTH'],
						'CONNECTOR_ID' => $handler['CONNECTOR_ID'],
					]
				);

				$offlineEventsCount[$application['CLIENT_ID']][$handler['CONNECTOR_ID']]++;
				$offlineEventsApp[$application['ID']] = true;
			}
			else
			{
				LoggerManager::getInstance()->getLogger()?->info(
					"\n{delimiter}\n"
					. "{date} - {host}\n{delimiter}\n"
					. "Event skipped because initializer is current application. \n"
					. "auth: {serverAuthData}"
					. "app: {application}\n", [
					'serverAuthData' => $serverAuthData,
					'application' => $application,
					'MESSAGE' => LogType::OFFLINE_EVENT_SKIPPED->value,
				]);
			}
		}

		if (!empty($offlineEventsCount))
		{
			$this->notifyApplications($offlineEventsCount);
		}

		if (!empty($offlineEventsApp))
		{
			$this->sendOfflineEvent(array_keys($offlineEventsApp));
		}

		$this->eventList = [];
		$this->isFinaliseInit = false;
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
			if(is_array($connectorCounters) && !empty($connectorCounters))
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
