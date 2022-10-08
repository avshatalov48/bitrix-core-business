<?php

namespace Bitrix\Location\Entity\Location;

use Bitrix\Location\Entity\Location\Collection;

/**
 * Interface ILocationRelated
 * @package Bitrix\Location
 */
interface IHasLocation
{
	/**
	 * @return int
	 */
	public function getId(): int;

	/**
	 * @return string
	 */
	public function getLocationEntityType(): string;

	/**
	 * @return Collection
	 */
	public function getLocationCollection(): Collection;
}
