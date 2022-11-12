<?php

namespace Bitrix\Main\Access\Filter;

use Bitrix\Main\Access\AccessibleController;

/**
 * Filter for getting items according to user rights.
 */
interface AccessFilter
{
	/**
	 * @param AccessibleController $controller
	 */
	public function __construct(AccessibleController $controller);

	/**
	 * Filter for getting elements.
	 *
	 * One instance of access filter can handle multiple entities (recommended to use 1 filter for entities that
	 * are similar in meaning and purpose). To get a filter for a specific entity, you need to pass the `entity` parameter.
	 *
	 * If the user has full access, then you can return an empty array (i.e. there is no filtering).
	 * If the user does not have access at all, then you can return a deliberately false filter that will
	 * not return records (for example, `ID IS NULL').
	 *
	 * @param string $entity
	 * @param array $params
	 *
	 * @return array
	 *
	 * @throws UnknownEntityException if they are trying to get a filter for an unknown entity.
	 */
	public function getFilter(string $entity, array $params = []): array;
}
