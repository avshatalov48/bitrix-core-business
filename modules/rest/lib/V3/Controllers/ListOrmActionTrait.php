<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Rest\V3\Interaction\Request\ListRequest;
use Bitrix\Rest\V3\Interaction\Response\ListResponse;

trait ListOrmActionTrait
{
	use OrmActionTrait;

	final public function listAction(ListRequest $request): ListResponse
	{
		$collection = $this->getOrmRepositoryByRequest($request)->getAll(
			$request->select,
			$request->filter,
			$request->order,
			$request->pagination,
		);

		return new ListResponse($collection);
	}
}
