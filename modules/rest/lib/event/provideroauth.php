<?php
namespace Bitrix\Rest\Event;

use Bitrix\Rest\OAuthService;
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
		if(OAuthService::getEngine()->isRegistered())
		{
			$result = OAuthService::getEngine()->getClient()->sendEvent($queryData);
			$logger = LoggerManager::getInstance()->getLogger();
			if ($logger)
			{
				$logger->debug(
					"\n{delimiter}\n"
					. "{date} - {host}\n{delimiter}\n"
					. "Event sends oauth\n"
					. "Count: {eventCount}"
					. "Result:\n"
					. "{result}",
					[
						'eventCount' => count($queryData),
						'result' => $result,
					]
				);
			}
		}
	}
}