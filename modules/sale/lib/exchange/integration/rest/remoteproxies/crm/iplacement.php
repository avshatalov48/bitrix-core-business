<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


use Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\ICmd;

interface IPlacement extends ICmd
{
	public function binds(array $list);
	public function unbinds(array $list);
}