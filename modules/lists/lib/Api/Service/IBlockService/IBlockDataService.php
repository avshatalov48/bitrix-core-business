<?php

namespace Bitrix\Lists\Api\Service\IBlockService;

use Bitrix\Lists\Api\Data\IBlockService\IBlockElementToAdd;
use Bitrix\Lists\Api\Request\IBlockService\AddIBlockElementRequest;
use Bitrix\Lists\Api\Response\IBlockService\AddIBlockElementResponse;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Error;

final class IBlockDataService
{
	public function getIBlockElementToAddObject(
		AddIBlockElementRequest $request,
		AddIBlockElementResponse $response
	): ?IBlockElementToAdd
	{
		$elementToAdd = null;
		try
		{
			$elementToAdd = IBlockElementToAdd::createFromRequest($request);
		}
		catch (ArgumentOutOfRangeException $exception)
		{
			$response->addError(new Error($exception->getMessage(), $exception->getParameter()));
		}

		return $elementToAdd;
	}
}
