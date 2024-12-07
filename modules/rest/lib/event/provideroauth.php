<?php
namespace Bitrix\Rest\Event;

use Bitrix\Main\SystemException;
use Bitrix\Rest\OAuthService;
use Bitrix\Rest\Tools\Diagnostics\Event\Logger;
use Bitrix\Rest\Tools\Diagnostics\Event\LogType;
use Bitrix\Rest\Tools\Diagnostics\LoggerManager;

class ProviderOAuth implements ProviderInterface
{
	/**
	 * @var ProviderOAuth
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

	public function send(array $queryData)
	{
		try
		{
			if (!OAuthService::getEngine()->isRegistered())
			{
				OAuthService::register();
			}
		}
		catch (SystemException $e)
		{
			LoggerManager::getInstance()->getLogger()?->info(
				"\n{delimiter}\n"
				. "{date} - {host}\n{delimiter}\n"
				. "OAuth connection error\n", [
				'MESSAGE' => LogType::OAUTH_ERROR->value,
				'REQUEST_DATA' => $queryData,
			]);
		}

		if (OAuthService::getEngine()->isRegistered())
		{
			$result = OAuthService::getEngine()->getClient()->sendEvent($queryData);

			if (is_array($result) && isset($result['error'], $result['error_description']))
			{
				LoggerManager::getInstance()->getLogger()?->info(
					"\n{delimiter}\n"
					. "{date} - {host}\n{delimiter}\n"
					. "Failed to send events\n"
					. "Error: {error}", [
					'error' => $result['error'],
					'REQUEST_DATA' => $queryData,
					'errorDescription' => $result['error_description'],
					'MESSAGE' => LogType::FAILED_SEND_TO_SQS->value,
					'RESPONSE_DATA' => $result,
				]);
			}
			else
			{
				foreach ($queryData as $item)
				{
					LoggerManager::getInstance()->getLogger()?->info(
						"\n{delimiter}\n"
						. "{date} - {host}\n{delimiter}\n"
						. "Event sends oauth\n"
						. "EventName: {eventName}"
						. "Result:\n"
						. "{result}", [
						'eventName' => $item['query']['QUERY_DATA']['event'] ?? null,
						'result' => $result,
						'CLIENT_ID' => $item['client_id'] ?? null,
						'METHOD' => $item['query']['QUERY_DATA']['event'] ?? null,
						'EVENT_ID' => $item['query']['QUERY_DATA']['event_handler_id'] ?? null,
						'MESSAGE' => LogType::SEND_SQS->value,
						'REQUEST_DATA' => $item,
						'RESPONSE_DATA' => $result,
					]);
				}
			}
		}
	}
}