<?php

namespace Bitrix\Socialnetwork\Control\Member;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Result;
use Bitrix\Main\Validation\ValidationService;
use Bitrix\Socialnetwork\Control\Member\Command\MembersCommand;
use Bitrix\Socialnetwork\Control\Member\Trait\GetMembersTrait;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup;
use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractMemberService
{
	use GetMembersTrait;

	protected ValidationService $validationService;

	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ObjectNotFoundException
	 */
	public function __construct()
	{
		$this->validationService = ServiceLocator::getInstance()->get('main.validation.service');
	}

	abstract protected function inviteImplementation(MembersCommand $command, Workgroup $group): Result;

	abstract protected function addImplementation(MembersCommand $command, Workgroup $group): Result;

	abstract protected function deleteImplementation(MembersCommand $command, Workgroup $group): Result;

	abstract protected function addModeratorsImplementation(MembersCommand $command, Workgroup $group): Result;

	abstract protected function deleteModeratorsImplementation(MembersCommand $command, Workgroup $group): Result;

	abstract protected function getRegistry(): GroupRegistry;

	public function invite(MembersCommand $command): Result
	{
		$validationResult = $this->validationService->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		$result = new Result();

		$group = $this->getRegistry()->get($command->getGroupId());
		if ($group === null)
		{
			$result->addError(new Error('No such group'));

			return $result;
		}

		return $this->inviteImplementation($command, $group);
	}

	public function add(MembersCommand $command): Result
	{
		$validationResult = $this->validationService->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		$result = new Result();

		$group = $this->getRegistry()->get($command->getGroupId());
		if ($group === null)
		{
			$result->addError(new Error('No such group'));

			return $result;
		}

		return $this->addImplementation($command, $group);
	}

	public function delete(MembersCommand $command): Result
	{
		$validationResult = $this->validationService->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		$result = new Result();

		$group = $this->getRegistry()->get($command->getGroupId());
		if ($group === null)
		{
			$result->addError(new Error('No such group'));

			return $result;
		}

		return $this->deleteImplementation($command, $group);
	}

	public function addModerators(MembersCommand $command): Result
	{
		$result = new Result();

		$validationResult = $this->validationService->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		$group = $this->getRegistry()->get($command->getGroupId());
		if ($group === null)
		{
			$result->addError(new Error('No such group'));

			return $result;
		}

		return $this->addModeratorsImplementation($command, $group);
	}

	public function deleteModerators(MembersCommand $command): Result
	{
		$result = new Result();

		$validationResult = $this->validationService->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		$group = $this->getRegistry()->get($command->getGroupId());
		if ($group === null)
		{
			$result->addError(new Error('No such group'));

			return $result;
		}

		return $this->deleteModeratorsImplementation($command, $group);
	}

}