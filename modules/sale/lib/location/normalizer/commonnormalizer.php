<?
namespace Bitrix\Sale\Location\Normalizer;

/**
 * Class CommonNormalizer
 * @package Bitrix\Sale\Location\Normalizer
 * Delete all except letters and spaces, trim and converts to uppercase.
 */
class CommonNormalizer implements INormalizer
{
	/**
	 * @inheritdoc
	 */
	public function normalize($string)
	{
		$result = $string;

		$result = preg_replace('/([^\w\s]|_)/iu', ' ', $result);

		$result = preg_replace('/\s+/iu', ' ', (string)$result);
		$result = trim($result);
		$result = mb_strtoupper($result);
		return $result;
	}
}
