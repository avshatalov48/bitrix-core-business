<?php
namespace Bitrix\Rest\Event;

use Bitrix\Rest\OAuthService;

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
			OAuthService::getEngine()->getClient()->sendEvent($queryData);
		}
	}
}