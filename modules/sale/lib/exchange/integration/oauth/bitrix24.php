<?php


namespace Bitrix\Sale\Exchange\Integration\OAuth;


use Bitrix\Sale\Exchange\Integration\App;
use Bitrix\Sale\Exchange\Integration\Settings;

class Bitrix24 extends Client
{
	public function __construct(array $options = [])
	{
		parent::__construct($options);

		$app = $this->getApplication();
		$this->clientId = $app->getClientId();
		$this->clientSecret = $app->getClientSecret();
	}

	protected function getBaseAccessTokenUrl()
	{
		return Settings::getOAuthAccessTokenUrl();
	}

	protected function getApplication()
	{
		return new App\IntegrationB24();
	}
}