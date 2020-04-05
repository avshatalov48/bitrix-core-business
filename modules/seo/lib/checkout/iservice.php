<?php

namespace Bitrix\Seo\Checkout;

/**
 * Interface IService
 * @package Bitrix\Seo\Checkout
 */
interface IService
{
	/**
	 * @param string $type
	 * @return string
	 */
	public static function getEngineCode($type);

	/**
	 * @return array
	 */
	public static function getTypes();

	/**
	 * @param string $type
	 * @return AuthAdapter
	 */
	public static function getAuthAdapter($type);
}