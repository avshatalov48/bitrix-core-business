<?php
namespace Bitrix\Sale\CrmSiteMaster\Tools;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

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