<?php

namespace Bitrix\Catalog\v2\AgentContract;

class AccessController
{
	public static function check(): bool
	{
		$saleModulePermissions = \CMain::GetGroupRight('sale');
		return $saleModulePermissions >= 'W';
	}
}
