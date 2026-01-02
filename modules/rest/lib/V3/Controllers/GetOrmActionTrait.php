<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Rest\V3\Exceptions\EntityNotFoundException;
use Bitrix\Rest\V3\Interaction\Request\GetRequest;
use Bitrix\Rest\V3\Interaction\Response\GetResponse;
use Bitrix\Rest\V3\Structures\Filtering\FilterStructure;

trait GetOrmActionTrait
{
	use OrmActionTrait;

	final public function getAction(GetRequest $request): GetResponse
	{
		$dto = $this->getOrmRepositoryByRequest($request)->getOneWith($request->select, (new FilterStructure())->where('id', $request->id));
		if ($dto === null)
		{
			throw new EntityNotFoundException($request->id);
		}

		return new GetResponse($dto);
	}
}
