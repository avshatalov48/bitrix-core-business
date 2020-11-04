<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\Sale;


use Bitrix\Sale\Exchange\Integration\Rest;

class Statistics extends Rest\RemoteProxies\Base
	implements IStatistics
{

	public function modify(array $fields)
	{
		return $this
			->cmd( Rest\Cmd\Registry::SALE_INTEGRATION_STATISTIC_MODIFY_NAME, [
				'fields' => $fields])
			->call();
	}
}