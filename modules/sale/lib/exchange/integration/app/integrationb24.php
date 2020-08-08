<?php
namespace Bitrix\Sale\Exchange\Integration\App;


class IntegrationB24 extends Base
{
	public function getCode()
	{
		return 'bitrix.eshop';
	}

	public function getClientId()
	{
		return 'app.5e26f4d9b86957.52282815';
	}

	public function getClientSecret()
	{
		return 'SLAIylxloCY7pu91VbaYzNaTeh75DAdSG16sjy8uM5Xs0vy4dl';
	}

	public function getAppUrl()
	{
		$server = \Bitrix\Main\Application::getInstance()
			->getContext()
			->getServer();

		return $server->get('HTTP_ORIGIN').'/bitrix/services/sale/b24integration/push.php';
	}
}