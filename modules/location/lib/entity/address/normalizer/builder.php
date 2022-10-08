<?php

namespace Bitrix\Location\Entity\Address\Normalizer;

/**
 * Build location name normalizer
 *
 * Class Builder
 * @package Bitrix\Location\Entity\Address\Normalizer
 * @internal
 */
class Builder implements IBuilder
{
	/**
	 * @param string $lang Language id.
	 * @return INormalizer
	 */
	public static function build(string $lang): INormalizer
	{
		return new Normalizer([
			new CommonNormalizer(),
			new LanguageNormalizer($lang),
			new SpaceNormalizer()
		]);
	}
}