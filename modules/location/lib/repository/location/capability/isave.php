<?php

namespace Bitrix\Location\Repository\Location\Capability;

use Bitrix\Location\Entity\Location;

/**
 * Interface ISave
 * @package Bitrix\Location\Repository\Location
 */
interface ISave
{
	/**
	 * @param Location $location
	 * @return \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult
	 */
	public function save(Location $location);
}