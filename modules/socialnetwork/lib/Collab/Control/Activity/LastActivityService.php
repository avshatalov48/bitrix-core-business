<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Activity;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Validation\ValidationService;
use Bitrix\Socialnetwork\Collab\Control\Activity\Command\DeleteLastActivityCommand;
use Bitrix\Socialnetwork\Collab\Control\Activity\Command\SetLastActivityCommand;
use Bitrix\Socialnetwork\Collab\Internals\CollabLastActivityTable;
use Exception;

class LastActivityService
{
	protected ValidationService $validationService;

	public function __construct()
	{
		$this->init();
	}

	public function set(SetLastActivityCommand $command): Result
	{
		$validationResult = $this->validationService->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		$activityDate = $command->getDate() ?? new DateTime();
		$insert = [
			'USER_ID' => $command->getUserId(),
			'COLLAB_ID' => $command->getCollabId(),
			'ACTIVITY_DATE' => $activityDate,
		];

		$update = [
			'COLLAB_ID' => $command->getCollabId(),
			'ACTIVITY_DATE' => $activityDate,
		];

		$result = new Result();

		try
		{
			CollabLastActivityTable::merge($insert, $update);
		}
		catch (Exception $e)
		{
			$result->addError(Error::createFromThrowable($e));
		}

		return $result;
	}

	public function delete(DeleteLastActivityCommand $command): Result
	{
		$validationResult = $this->validationService->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		$filter = [];

		$collabId = $command->getCollabId();
		if ($collabId > 0)
		{
			$filter['COLLAB_ID'] = $collabId;
		}

		$userIds = $command->getUserIds();
		if (!empty($userIds))
		{
			$filter['@USER_ID'] = $userIds;
		}

		$result = new Result();
		if (empty($filter))
		{
			return $result;
		}

		try
		{
			CollabLastActivityTable::deleteByFilter($filter);
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