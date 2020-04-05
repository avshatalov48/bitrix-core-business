<?php

namespace Bitrix\Sale\Location\Normalizer;

/**
 * For building locations names normalizers
 * Interface IBuilder
 * @package Bitrix\Sale\Location\Normalizer
 */
interface IBuilder
{
	/**
	 * @param string $lang Language id.
	 * @return INormalizer
	 */
	public static function build($lang);
}