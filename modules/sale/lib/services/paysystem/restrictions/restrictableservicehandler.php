<?php

namespace Bitrix\Sale\Services\PaySystem\Restrictions;

use Bitrix\Sale\Services\Base\RestrictionInfoCollection;

interface RestrictableServiceHandler
{
	public function getRestrictionList(): RestrictionInfoCollection;
}