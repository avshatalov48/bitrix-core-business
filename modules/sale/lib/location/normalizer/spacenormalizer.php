<?
namespace Bitrix\Sale\Location\Normalizer;

/**
 * Erase spaces from name
 * Class SpaceNormalizer
 * @package Bitrix\Sale\Location\Normalizer
 */
class SpaceNormalizer implements INormalizer
{
	/**
	 * @inheritdoc
	 */
	public function normalize($string)
	{
		return str_replace(' ', '', $string);
	}
}