<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\Sale;


use Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\ICmd;

interface IStatisticsProvider extends ICmd
{
	public function add(array $fields);
	public function getList($select=[], $filter, $order=[], $pageNavigation);
}