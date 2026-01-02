<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Rest\V3\Exceptions\Validation\RequiredFieldInRequestException;
use Bitrix\Rest\V3\Interaction\Request\DeleteRequest;
use Bitrix\Rest\V3\Interaction\Response\DeleteResponse;

trait DeleteOrmActionTrait
{
	use OrmActionTrait;

	final public function deleteAction(DeleteRequest $request): DeleteResponse
	{
		if ($request->id === null && $request->filter === null)
		{
			throw new RequiredFieldInRequestException('id || filter');
		}
		$repository = $this->getOrmRepositoryByRequest($request);
		$result = $request->id !== null ? $repository->delete($request->id) : $repository->deleteMulti($request->filter);
		return new DeleteResponse($result);
	}
}
