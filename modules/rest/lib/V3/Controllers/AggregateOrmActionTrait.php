<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Rest\V3\Exceptions\ClassRequireAttributeException;
use Bitrix\Rest\V3\Interaction\Request\AggregateRequest;
use Bitrix\Rest\V3\Interaction\Response\AggregateResponse;

trait AggregateOrmActionTrait
{
	use OrmActionTrait;

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws ClassRequireAttributeException
	 */
	final public function aggregateAction(AggregateRequest $request): AggregateResponse
	{
		$repository = $this->getOrmRepositoryByRequest($request);
		$result = $repository->getAllWithAggregate($request->select, $request->filter);
		return new AggregateResponse($result);
	}
}
