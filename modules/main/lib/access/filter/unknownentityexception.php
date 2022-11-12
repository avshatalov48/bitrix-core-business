<?php

namespace Bitrix\Main\Access\Filter;

use Bitrix\Main\SystemException;

/**
 * Exceptions for handling situations when try to get information from an access filter on an entity unknown to it.
 */
class UnknownEntityException extends SystemException
{
	/**
	 * @param string $entity
	 * @param AccessFilter $accessFilter
	 */
	public function __construct(string $entity, AccessFilter $accessFilter)
	{
		$accessFilterName = get_class($accessFilter);

		parent::__construct(
			"Unknown entity '{$entity}' for access filter '{$accessFilterName}'"
		);
	}
}
