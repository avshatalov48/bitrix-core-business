<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


use Bitrix\Sale\Exchange\Integration\Rest;

class App extends Rest\RemoteProxies\Base
	implements IApp
{
	public function optionSet($options)
	{
		return $this
			->cmd( Rest\Cmd\Registry::APP_OPTIONS_ADD_NAME, [
				'options' => $options])
			->call();
	}
}