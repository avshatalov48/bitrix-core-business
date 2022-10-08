<?
namespace Bitrix\Location\Entity\Address\Normalizer;

/**
 * Erase spaces from name
 *
 * Class SpaceNormalizer
 * @package Bitrix\Location\Entity\Address\Normalizer
 * @inernal
 */
class SpaceNormalizer implements INormalizer
{
	/**
	 * @inheritdoc
	 */
	public function normalize(string $string): string
	{
		return str_replace(' ', '', $string);
	}
}