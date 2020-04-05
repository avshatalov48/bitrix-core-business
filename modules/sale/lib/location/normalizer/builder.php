<?php

namespace Bitrix\Sale\Location\Normalizer;

/**
 * Class Builder
 * @package Bitrix\Sale\Location\Normalizer
 * Build location name normalizer
 */
class Builder implements IBuilder
{
	/**
	 * @param string $lang Language identifier
	 * @return INormalizer
	 */
	public static function build($lang)
	{
		return new Normalizer([
			new CommonNormalizer(),
			new LanguageNormalizer($lang),
			new SpaceNormalizer()
		]);
	}
}