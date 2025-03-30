<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Option;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Validation\ValidationService;
use Bitrix\Socialnetwork\Collab\Control\Option\Command\DeleteOptionsCommand;
use Bitrix\Socialnetwork\Collab\Control\Option\Command\SetOptionsCommand;
use Bitrix\Socialnetwork\Collab\Internals\CollabOptionTable;
use Exception;

class OptionService
{
	protected ValidationService $validationService;

	public function __construct()
	{
		$this->init();
	}

	public function set(SetOptionsCommand $command): Result
	{
		$validationResult = $this->validationService->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		// $deleteCommand = (new DeleteOptionsCommand())
		// 	->setCollabId($command->getCollabId());

		// $deleteResult = $this->delete($deleteCommand);
		// if (!$deleteResult->isSuccess())
		// {
		// 	return $deleteResult;
		// }

		$result = new Result();

		$uniqueFields = ['COLLAB_ID', 'NAME'];

		$options = $command->getOptions()->getValue();
		foreach ($options as $option)
		{
			$insert = [
				'COLLAB_ID' => $command->getCollabId(),
				'NAME' => $option->getName(),
				'VALUE' => $option->getValue(),
			];

			$update = [
				'VALUE' => $option->getValue(),
			];

			try
			{
				CollabOptionTable::merge($insert, $update, $uniqueFields);
			}
			catch (Exception $e)
			{
				$result->addError(Error::createFromThrowable($e));

				return $result;
			}

			$applyResult = $option->apply($command->getCollabId());

			if (!$applyResult->isSuccess())
			{
				return $applyResult;
			}
		}

		return $result;
	}

	public function delete(DeleteOptionsCommand $command): Result
	{
		$validationResult = $this->validationService->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		$result = new Result();

		try
		{
			CollabOptionTable::deleteByFilter(['COLLAB_ID' => $command->getCollabId()]);
		}
		catch (Exception $e)
		{
			$result->addError(Error::createFromThrowable($e));
		}

		return $result;
	}

	protected function init(): void
	{
		$this->validationService = ServiceLocator::getInstance()->get('main.validation.service');
	}
}