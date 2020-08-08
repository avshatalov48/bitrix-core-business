<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


use Bitrix\Sale\Exchange\Integration\Rest;

class Placement extends Rest\RemoteProxies\Base
	implements IPlacement
{
	public function binds(array $list)
	{
		return $this
			->batch(Rest\Cmd\Registry::APP_PLACEMENT_BIND_NAME, $list)
			->call();
	}

	public function unbinds(array $list)
	{
		return $this
			->batch(Rest\Cmd\Registry::APP_PLACEMENT_UNBIND_NAME, $list)
			->call();
	}
}