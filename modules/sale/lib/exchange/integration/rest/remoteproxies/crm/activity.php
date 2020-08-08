<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


use Bitrix\Sale\Exchange\Integration\Rest;

class Activity extends Rest\RemoteProxies\Base
	implements IActivity
{
	public function adds($list)
	{
		return $this
			->batch(Rest\Cmd\Registry::CRM_ACTIVITY_ADD_NAME, $list)
			->call();
	}
}