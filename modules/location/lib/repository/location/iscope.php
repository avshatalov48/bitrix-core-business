<?php

namespace Bitrix\Location\Repository\Location;

interface IScope
{
	/**
	 * Check  is scope satisfy
	 * @param int $scope
	 * @return bool
	 */
	public function isScopeSatisfy(int $scope): bool;
}
