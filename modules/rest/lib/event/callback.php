<?php
namespace Bitrix\Rest\Event;

use Bitrix\Rest\AppTable;
use Bitrix\Rest\EventTable;

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
	 */
	public static function __callStatic($name, $arguments)
	{
		$event = Sender::parseEventName($name);

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
					if($restEvent[0] == $event['MODULE_ID'] && toUpper($restEvent[1]) == $event['EVENT'])
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

		if(array_key_exists('EVENT_REST', $event))
		{
			$dbRes = EventTable::getList(
				[
					'filter' => [
						'=EVENT_NAME' => toUpper($event['EVENT_REST']['EVENT']),
					],
					'select' => [
						'*',
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
				if (!empty($handler['APP_CODE']))
				{
					if (
						$handler['APP_ACTIVE'] !== AppTable::ACTIVE
						|| $handler['APP_INSTALLED'] !== AppTable::INSTALLED
					)
					{
						continue;
					}

					$appStatus = AppTable::getAppStatusInfo($handler['APP_CODE'], '');
					if ($appStatus['PAYMENT_EXPIRED'] === 'Y')
					{
						continue;
					}
				}

				$handlerArguments = $arguments;
				$handlerFound = true;

				if(!$dataProcessed)
				{
					try
					{
						$handlerArguments = call_user_func_array($event['EVENT_REST']['HANDLER'], array($handlerArguments, $handler));
						$call[] = array($handler, $handlerArguments, $event['EVENT_REST']['ADDITIONAL']);
					}
					catch(\Exception $e)
					{
					}
				}
				else
				{
					$call[] = array($handler, $handlerArguments, $event['EVENT_REST']['ADDITIONAL']);
				}
			}

			if(count($call) > 0)
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
