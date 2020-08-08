<?php
namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;

use Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\ICmd;

interface IActivity extends ICmd
{
	public function adds($list);
}