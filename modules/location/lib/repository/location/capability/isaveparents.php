<?php

namespace Bitrix\Location\Repository\Location\Capability;

use Bitrix\Location\Entity\Location;
use Bitrix\Main\Result;

/**
 * Interface ISaveParents
 * @package Bitrix\Location\Repository\Location\Capability
 */
interface ISaveParents
{
	/**
	 * @param Location\Parents $parents
	 * @return Result
	 */
	public function saveParents(Location\Parents $parents): Result;
}