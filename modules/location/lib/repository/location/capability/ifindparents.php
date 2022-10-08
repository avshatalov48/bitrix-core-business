<?php

namespace Bitrix\Location\Repository\Location\Capability;

use Bitrix\Location\Entity\Location;
use Bitrix\Location\Entity\Location\Parents;

/**
 * Interface IFindParent
 * @package Bitrix\Location\Repository
 */
interface IFindParents
{
	/**
	 * @param Location $location
	 * @return Parents
	 */
	public function findParents(Location $location, string $languageId);
}