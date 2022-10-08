<?php

namespace Bitrix\Location\Repository\Location\Capability;

use Bitrix\Location\Entity\Location;
use Bitrix\Main\Result;

/**
 * Interface IDelete
 * @package Bitrix\Location\Repository
 */
interface IDelete
{
	/**
	 * @param Location $location
	 * @return Result
	 *  todo: with inheritance or not
	 */
	public function delete(Location $location): Result;
}