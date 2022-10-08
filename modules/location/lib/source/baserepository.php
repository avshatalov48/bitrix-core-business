<?php

namespace Bitrix\Location\Source;

use Bitrix\Location\Repository\Location\IScope;

/**
 * Class BaseRepository
 * @package Bitrix\Location\Source
 * @internal
 */
abstract class BaseRepository implements IScope
{
	/**
	 * @inheritDoc
	 */
	public function isScopeSatisfy(int $scope): bool
	{
		return $scope === LOCATION_SEARCH_SCOPE_ALL || $scope === LOCATION_SEARCH_SCOPE_EXTERNAL;
	}
}
