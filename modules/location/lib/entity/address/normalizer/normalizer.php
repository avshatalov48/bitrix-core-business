<?php
namespace Bitrix\Location\Entity\Address\Normalizer;

use Bitrix\Main\ArgumentOutOfRangeException;

/**
 * Normalize Locations names for search and mapping purposes.
 *
 * Class Normalizer
 * @package Bitrix\Location\Entity\Address\Normalizer
 * @internal
 */
class Normalizer implements INormalizer
{
	/** @var INormalizer [] */
	protected $normalizers = [];

	/**
	 * Normalizer constructor.
	 * @param INormalizer[] $normalizers
	 * @throws ArgumentOutOfRangeException
	 */
	public function __construct(array $normalizers)
	{
		foreach($normalizers as $normalizer)
		{
			$this->addNormalizer($normalizer);
		}
	}

	/**
	 * @param INormalizer $normalizer
	 * @throws ArgumentOutOfRangeException
	 */
	protected function addNormalizer(INormalizer $normalizer): void
	{
		$this->normalizers[] = $normalizer;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public function normalize(string $string): string
	{
		$result = $string;

		foreach($this->normalizers as $normalizer)
		{
			$result = $normalizer->normalize($result);
		}

		return $result;
	}
}