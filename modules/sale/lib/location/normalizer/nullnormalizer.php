<?
namespace Bitrix\Sale\Location\Normalizer;

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