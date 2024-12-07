<?php
namespace Bitrix\Location\Entity\Address\Normalizer;

use Bitrix\Main\Text\Encoding;

/**
 * Class CommonNormalizer
 * @package Bitrix\Location\Entity\Address\Normalizer
 * Delete all except letters and spaces, trim and converts to uppercase.
 * @internal
 */
class CommonNormalizer implements INormalizer
{
	/**
	 * @inheritdoc
	 */
	public function normalize(string $string): string
	{
		$result = $string;

		$result = preg_replace('/([^\w\s]|_)/iu', ' ', $result);

		$result = preg_replace('/\s+/iu', ' ', $result);
		$result = trim($result);
		return mb_strtoupper($result);
	}
}