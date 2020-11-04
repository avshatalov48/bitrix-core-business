<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\Sale;


use Bitrix\Sale\Exchange\Integration\Rest;

class StatisticsProvider extends Rest\RemoteProxies\Base
	implements IStatisticsProvider
{
	public function add(array $fields)
	{
		return $this
			->cmd( Rest\Cmd\Registry::SALE_INTEGRATION_STATISTIC_PROVIDER_ADD_NAME, [
				'fields' => $fields])
			->call();
	}

	public function getList($select=[], $filter, $order=[], $pageNavigation='')
	{
		return $this
			->cmd( Rest\Cmd\Registry::SALE_INTEGRATION_STATISTIC_PROVIDER_LIST_NAME, [
					'select' => $select,
					'filter' => $filter,
					'order' => $order,
					'pageNavigation' => $pageNavigation]
			)
			->call();
	}
}