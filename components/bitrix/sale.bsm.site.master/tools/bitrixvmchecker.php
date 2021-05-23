<?php
namespace Bitrix\Sale\BsmSiteMaster\Tools;

/**
 * Class BitrixVmChecker
 * @package Bitrix\Sale\BsmSiteMaster\Tools
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