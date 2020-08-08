<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


use Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\ICmd;

interface ICompany extends ICmd
{
	public function adds($list);
	public function getList($select=[], $filter, $order=[], $pageNavigation);
}