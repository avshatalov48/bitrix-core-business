<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


use Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\ICmd;

interface IDeal extends ICmd
{
	public function adds($list);
	public function contactItemsGet($id);
	public function contactItemsSet($id, $items);

}