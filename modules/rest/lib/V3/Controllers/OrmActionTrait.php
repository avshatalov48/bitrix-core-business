<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Rest\V3\Data\OrmRepository;
use Bitrix\Rest\V3\Interaction\Request\Request;

trait OrmActionTrait
{
	public function getOrmRepositoryByRequest(Request $request): OrmRepository
	{
		return new OrmRepository($request->getDtoClass());
	}
}
