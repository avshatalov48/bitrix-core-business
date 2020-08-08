<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


use Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\ICmd;

interface IContact extends ICmd
{
	public function adds($list);
	public function getList($select=[], $filter, $order=[], $pageNavigation);
}