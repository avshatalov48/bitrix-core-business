<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Rest\V3\Dto\Dto;

trait ValidateDtoTrait
{
	protected function validateDto(Dto $dto): bool
	{
		$validation = ServiceLocator::getInstance()->get('main.validation.service');
		$result = $validation->validate($dto);

		if ($result->isSuccess())
		{
			return true;
		}
		else
		{
			$this->addErrors($result->getErrors());
			return false;
		}
	}

}
