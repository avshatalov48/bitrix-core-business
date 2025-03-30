<?php
namespace Bitrix\Rest\Event;

use Bitrix\Rest\AppTable;
use Bitrix\Rest\EventTable;
use Bitrix\Rest\Tools\Diagnostics\Event;
use Bitrix\Rest\Tools\Diagnostics\LoggerManager;

/**
 * Class Callback
 *
 * Callback for Bitrix events transferred to REST events
 *
 * @package Bitrix\Rest
 **/
class Callback
{
	/**
	 * Handler for all PHP events transferred to REST.
	 *
	 * @param string $name Event name.
	 * @param array $arguments Event arguments.
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 */
	public static function __callStatic($name, $arguments)
	{
		$event = Sender::parseEventName($name);
		LoggerManager::getInstance()->getLogger()?->info(
			"\n{delimiter}\n"
			. "{date} - {host}\n{delimiter}\n"
			. "Event {eventName} starts. \n{delimiter}\n"
			. "{arguments}", [
			'RESPONSE_DATA' => $arguments,
			'SCOPE' => $event['MODULE_ID'] ?? null,
			'METHOD' => $event['EVENT'] ?? null,
			'MESSAGE' => Event\LogType::EVENT_START->value,
			'eventName' => $event['EVENT'],
			'arguments' => $arguments,
		]);

		$provider = new \CRestProvider();
		$description = $provider->getDescription();

		foreach($description as $scope => $scopeMethods)
		{
			if(
				array_key_exists(\CRestUtil::EVENTS, $scopeMethods)
				&& is_array($scopeMethods[\CRestUtil::EVENTS])
			)
			{
				foreach($scopeMethods[\CRestUtil::EVENTS] as $key => $restEvent)
				{
					if($restEvent[0] == $event['MODULE_ID'] && mb_strtoupper($restEvent[1]) == $event['EVENT'])
					{
						$event['EVENT_REST'] = array(
							'EVENT' => $key,
							'HANDLER' => $restEvent[2],
							'ADDITIONAL' => array(),
						);

						if(isset($restEvent[3]) && is_array($restEvent[3]))
						{
							$event['EVENT_REST']['ADDITIONAL'] = $restEvent[3];
						}

						break;
					}
				}
			}

			if(array_key_exists('EVENT_REST', $event))
			{
				break;
			}
		}

		$handlerFound = false;
		$appHoldExceptId = 0;
		if (!empty($arguments[1]['REST_EVENT_HOLD_EXCEPT_APP']))
		{
			$app = AppTable::getByClientId($arguments[1]['REST_EVENT_HOLD_EXCEPT_APP']);
			if ($app['ID'] > 0)
			{
				$appHoldExceptId = $app['ID'];
			}
		}

		if(array_key_exists('EVENT_REST', $event))
		{
			$filter = [
				'=EVENT_NAME' => mb_strtoupper($event['EVENT_REST']['EVENT']),
			];
			if ($appHoldExceptId > 0)
			{
				$filter['=APP_ID'] = $appHoldExceptId;
			}

			$dbRes = EventTable::getList(
				[
					'filter' => $filter,
					'select' => [
						'ID',
						'APP_ID',
						'EVENT_NAME',
						'EVENT_HANDLER',
						'USER_ID',
						'APPLICATION_TOKEN',
						'CONNECTOR_ID',
						'APP_CODE' => 'REST_APP.CLIENT_ID',
						'APP_ACTIVE' => 'REST_APP.ACTIVE',
						'APP_INSTALLED' => 'REST_APP.INSTALLED',
					],
				]
			);

			$dataProcessed = !is_array($event['EVENT_REST']['HANDLER']) || !is_callable($event['EVENT_REST']['HANDLER']);
			$call = array();
			while ($handler = $dbRes->fetch())
			{
				$handlerFound = true;

				LoggerManager::getInstance()->getLogger()?->info(
					"\n{delimiter}\n"
					. "{date} - {host}\n{delimiter}\n"
					. "Event {eventName} handler found. \n{delimiter}\n"
					. "{handler}", [
					'RESPONSE_DATA' => $arguments,
					'CLIENT_ID' => $handler['APP_CODE'] ?? null,
					'SCOPE' => $event['MODULE_ID'] ?? null,
					'EVENT_ID' => $handler['ID'] ?? null,
					'METHOD' => $event['EVENT'] ?? null,
					'MESSAGE' => Event\LogType::EVENT_HANDLER_FOUND->value,
					'eventName' => $event['EVENT'] ?? null,
					'handler' => $handler,
				]);

				if (!empty($handler['APP_CODE']))
				{
					if (
						$handler['APP_ACTIVE'] !== AppTable::ACTIVE
						|| $handler['APP_INSTALLED'] !== AppTable::INSTALLED
					)
					{
						LoggerManager::getInstance()->getLogger()?->info(
							"\n{delimiter}\n"
							. "{date} - {host}\n{delimiter}\n"
							. "Event {eventName} skipped because inactive app: \n"
							. "{handler}", [
							'RESPONSE_DATA' => $arguments,
							'SCOPE' => $event['MODULE_ID'] ?? null,
							'METHOD' => $event['EVENT'] ?? null,
							'CLIENT_ID' => $handler['APP_CODE'],
							'EVENT_ID' => $handler['ID'] ?? null,
							'MESSAGE' => Event\LogType::SKIP_BY_APP_INACTIVE->value,
							'eventName' => $event['EVENT'] ?? null,
							'handler' => $handler,
						]);

						continue;
					}

					$appStatus = AppTable::getAppStatusInfo($handler['APP_CODE'], '');
					if ($appStatus['PAYMENT_EXPIRED'] === 'Y')
					{
						LoggerManager::getInstance()->getLogger()?->info(
							"\n{delimiter}\n"
							. "{date} - {host}\n{delimiter}\n"
							. "Event {eventName} skipped because PAYMENT_EXPIRED: \n"
							. "{appStatus}", [
							'RESPONSE_DATA' => $arguments,
							'SCOPE' => $event['MODULE_ID'] ?? null,
							'METHOD' => $event['EVENT'] ?? null,
							'CLIENT_ID' => $handler['APP_CODE'],
							'EVENT_ID' => $handler['ID'] ?? null,
							'MESSAGE' => Event\LogType::SKIP_BY_PAYMENT_EXPIRED->value,
							'eventName' => $event['EVENT'] ?? null,
							'appStatus' => $appStatus,
						]);

						continue;
					}
				}

				$handlerArguments = $arguments;

				if(!$dataProcessed)
				{
					try
					{
						$handlerArguments = call_user_func_array($event['EVENT_REST']['HANDLER'], array($handlerArguments, $handler));
						$call[] = array($handler, $handlerArguments, $event['EVENT_REST']['ADDITIONAL']);
					}
					catch(\Exception $e)
					{
						LoggerManager::getInstance()->getLogger()?->error(
							"\n{delimiter}\n"
							. "{date} - {host}\n{delimiter}\n"
							. "Event {eventName} exception: \n"
							. "{errorCode}: {errorMessage}", [
							'RESPONSE_DATA' => $e->getMessage(),
							'SCOPE' => $event['MODULE_ID'] ?? null,
							'METHOD' => $event['EVENT'] ?? null,
							'CLIENT_ID' => $handler['APP_CODE'] ?? null,
							'EVENT_ID' => $handler['ID'] ?? null,
							'RESPONSE_STATUS' => $e->getCode(),
							'MESSAGE' => Event\LogType::EVENT_EXCEPTION->value,
							'eventName' => $event['EVENT'],
							'errorCode' => $e->getCode(),
							'errorMessage' => $e->getMessage(),
						]);
					}
				}
				else
				{
					$call[] = array($handler, $handlerArguments, $event['EVENT_REST']['ADDITIONAL']);
				}
			}

			if (!empty($call))
			{
				Sender::call($call);
			}
		}

		if(!$handlerFound)
		{
			Sender::unbind($event['MODULE_ID'], $event['EVENT']);
		}
	}
}
