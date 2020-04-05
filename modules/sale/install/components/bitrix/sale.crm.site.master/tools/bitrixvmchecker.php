<?php
namespace Bitrix\Sale\CrmSiteMaster\Tools;

/**
 * Class BitrixVmChecker
 * @package Bitrix\Sale\CrmSiteMaster\Tools
 */
class BitrixVmChecker
{
	/**
	 * @return bool
	 */
	public function isVm()
	{
		return getenv('BITRIX_VA_VER') ? true : false;
	}
}