<?php

namespace Bitrix\Location\Entity\Address\Normalizer;

/**
 * Location name normalizers builder
 *
 * Interface IBuilder
 * @package Bitrix\Location\Entity\Address\Normalizer
 * @internal
 */
interface IBuilder
{
	/**
	 * Build normalizer
	 *
	 * @param string $lang Language id.
	 * @return INormalizer
	 */
	public static function build(string $lang): INormalizer;
}