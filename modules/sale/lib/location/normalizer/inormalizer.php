<?php
namespace Bitrix\Sale\Location\Normalizer;

/**
 * Interface for location names normalizers
 * Interface INormalizer
 * @package Bitrix\Sale\Location\Normalizer
 */
interface INormalizer
{
	/**
	 * @param string $name Location name
	 * @return string
	 */
	public function normalize($name);
}