<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Rest\V3\Exceptions\Validation\DtoValidationException;
use Bitrix\Rest\V3\Interaction\Request\AddRequest;
use Bitrix\Rest\V3\Interaction\Response\AddResponse;

trait AddOrmActionTrait
{
	use OrmActionTrait;
	use ValidateDtoTrait;

	final public function addAction(AddRequest $request): AddResponse
	{
		// convert fields to dto
		$dto = $request->fields->getAsDto();

		// validate
		if (!$this->validateDto($dto))
		{
			throw new DtoValidationException($this->getErrors());
		}

		$repository = $this->getOrmRepositoryByRequest($request);
		$response = $repository->add($dto);

		return new AddResponse($response);
	}
}
