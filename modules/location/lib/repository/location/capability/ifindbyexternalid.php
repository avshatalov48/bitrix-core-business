<?php

namespace Bitrix\Location\Repository\Location\Capability;

use Bitrix\Location\Entity\Location;

/**
 * Interface IFindByExternalId
 * @package Bitrix\Location\Repository\Location\Capability
 */
interface IFindByExternalId
{
	/**
	 * @param string $externalId
	 * @param string $sourceCode
	 * @param string $languageId
	 * @return Location|null|bool
	 */
	public function findByExternalId(string $externalId, string $sourceCode,  string $languageId);
}