<?php
namespace Bitrix\Location\Entity\Address\Normalizer;

/**
 * Interface for location names normalizers
 * Interface INormalizer
 * @package Bitrix\Location\Entity\Address\Normalizer
 * @internal
 */
interface INormalizer
{
	/**
	 * @param string $name Location name
	 * @return string
	 */
	public function normalize(string $name): string;
}