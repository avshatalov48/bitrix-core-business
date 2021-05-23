<?php

namespace Bitrix\Seo\BusinessSuite;

use Bitrix\Seo\Retargeting\IService;

interface IInternalService extends IService
{
	/**
	 * get type by engineCode
	 *
	 * @param string $engineCode
	 *
	 * @return string|null
	 */
	public static function getTypeByEngine(string $engineCode) : ?string;

	/**
	 * check if service can use as internal
	 * @return bool
	 */
	public static function canUseAsInternal() : bool;

	/**
	 * get seoproxy method prefix
	 * @return string
	 */
	public static function getMethodPrefix() : string;
}