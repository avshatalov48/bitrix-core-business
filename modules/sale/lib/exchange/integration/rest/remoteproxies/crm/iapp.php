<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


use Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\ICmd;

interface IApp extends ICmd
{
	public function optionSet($options);
}