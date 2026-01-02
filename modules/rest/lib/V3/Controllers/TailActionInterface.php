<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Rest\V3\Interaction\Request\TailRequest;
use Bitrix\Rest\V3\Interaction\Response\ListResponse;

interface TailActionInterface
{
	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function tailAction(TailRequest $request): ListResponse;
}
