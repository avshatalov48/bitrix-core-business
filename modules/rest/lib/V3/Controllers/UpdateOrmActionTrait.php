<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Rest\V3\Exceptions\Validation\RequiredFieldInRequestException;
use Bitrix\Rest\V3\Interaction\Request\UpdateRequest;
use Bitrix\Rest\V3\Interaction\Response\UpdateResponse;

trait UpdateOrmActionTrait
{
	use OrmActionTrait;
	use ValidateDtoTrait;

	public function updateAction(UpdateRequest $request): UpdateResponse
	{
		if ($request->id === null && $request->filter === null)
		{
			throw new RequiredFieldInRequestException('id || filter');
		}

		$repository = $this->getOrmRepositoryByRequest($request);
		$result = $request->id !== null ?
			$repository->update($request->id, $request->fields->getAsDto()) :
			$repository->updateMulti($request->filter, $request->fields->getAsDto());
		return new UpdateResponse($result);
	}
}
