<?
namespace Bitrix\Location\Entity\Address\Normalizer;

class NullNormalizer implements INormalizer
{
	/**
	 * @inheritdoc
	 */
	public function normalize($string)
	{
		return $string;
	}
}