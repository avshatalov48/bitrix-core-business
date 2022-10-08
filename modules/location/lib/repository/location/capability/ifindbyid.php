<?php

namespace Bitrix\Location\Repository\Location\Capability;

use Bitrix\Location\Entity\Location;

/**
 * Interface IFindById
 * @package Bitrix\Location\Repository
 */
interface IFindById
{
	/**
	 * @param int $id
	 * @param string $languageId
	 * @return Location|null|bool
	 */
	public function findById(int $id, string $languageId);
}