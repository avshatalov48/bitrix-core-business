<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\Validation\ValidationService;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Control\Command\AbstractCommand;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Command\DeleteCommand;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Handler\Add\AddHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\Delete\DeleteHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\Update\UpdateHandlerInterface;
use Bitrix\Socialnetwork\Control\Operation\AddOperation;
use Bitrix\Socialnetwork\Control\Operation\DeleteOperation;
use Bitrix\Socialnetwork\Control\Operation\UpdateOperation;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup;

abstract class AbstractGroupService
{
	protected GroupRegistry $registry;
	protected ValidationService $validationService;

	public function __construct()
	{
		$this->init();
	}

	/** @return AddHandlerInterface[] */
	abstract protected function getAddHandlers(): array;

	/** @return UpdateHandlerInterface[] */
	abstract protected function getUpdateHandlers(): array;

	/** @return DeleteHandlerInterface[] */
	abstract protected function getDeleteHandlers(): array;

	public function add(AddCommand $command): GroupResult
	{
		$checkResult = $this->checkAddCommand($command);
		if (!$checkResult->isSuccess())
		{
			return $this->finalizeAddResult($checkResult);
		}

		$result = new GroupResult();

		$validationResult = $this->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $this->finalizeAddResult($validationResult);
		}

		$operationResult = (new AddOperation($command))->run();
		if (!$operationResult->isSuccess())
		{
			return $this->finalizeAddResult($operationResult);
		}

		$entity = $operationResult->getGroup();
		if ($entity === null)
		{
			return $this->finalizeAddResult($operationResult);
		}

		$result->merge($operationResult);

		$handlerResult = $this->runAddHandlers($command, $entity);

		$this->sendAddEvent($command, $entity);

		$result->merge($handlerResult);

		$entity = $this->getEntityById($entity->getId());

		$result->setGroup($entity);

		return $this->finalizeAddResult($result);
	}

	public function update(UpdateCommand $command): GroupResult
	{
		$checkResult = $this->checkUpdateCommand($command);
		if (!$checkResult->isSuccess())
		{
			return $this->finalizeUpdateResult($checkResult);
		}

		$result = new GroupResult();

		$validationResult = $this->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $this->finalizeUpdateResult($validationResult);
		}

		$entityBefore = $this->getEntityById($command->getId(), false);
		if ($entityBefore === null)
		{
			$result->addError(new Error('Group not found'));

			return $this->finalizeUpdateResult($result);
		}

		$this->sendBeforeUpdateEvent($command, $entityBefore);

		$operationResult = (new UpdateOperation($command))->run();
		if (!$operationResult->isSuccess())
		{
			return $this->finalizeUpdateResult($operationResult);
		}

		$entityAfter = $operationResult->getGroup();
		if ($entityAfter === null)
		{
			return $this->finalizeUpdateResult($operationResult);
		}

		$result->merge($operationResult);

		$handlerResult = $this->runUpdateHandlers($command, $entityBefore, $entityAfter);

		$this->sendUpdateEvent($command, $entityBefore, $entityAfter);

		$result->merge($handlerResult);

		$entity = $this->getEntityById($entityAfter->getId());

		$result->setGroup($entity);

		return $this->finalizeUpdateResult($result);
	}

	public function delete(DeleteCommand $command): GroupResult
	{
		$checkResult = $this->checkDeleteCommand($command);
		if (!$checkResult->isSuccess())
		{
			return $this->finalizeDeleteResult($checkResult);
		}

		$result = new GroupResult();

		$validationResult = $this->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $this->finalizeDeleteResult($validationResult);
		}

		$entityBefore = $this->getEntityById($command->getId(), false);
		if ($entityBefore === null)
		{
			$result = new GroupResult();
			$result->addError(new Error('Group not found'));

			return $this->finalizeDeleteResult($result);
		}

		$operationResult = (new DeleteOperation($command))->run();

		if (!$operationResult->isSuccess())
		{
			return $this->finalizeDeleteResult($operationResult);
		}

		$result->merge($operationResult);

		$handlerResult = $this->runDeleteHandlers($command, $entityBefore);

		$this->sendDeleteEvent($command, $entityBefore);

		$result->merge($handlerResult);

		return $this->finalizeDeleteResult($result);
	}

	protected function checkAddCommand(AddCommand $command): GroupResult
	{
		return new GroupResult();
	}

	protected function checkUpdateCommand(UpdateCommand $command): GroupResult
	{
		return new GroupResult();
	}

	protected function checkDeleteCommand(DeleteCommand $command): GroupResult
	{
		return new GroupResult();
	}

	protected function finalizeAddResult(GroupResult $result): GroupResult
	{
		return $result;
	}

	protected function finalizeUpdateResult(GroupResult $result): GroupResult
	{
		return $result;
	}

	protected function finalizeDeleteResult(GroupResult $result): GroupResult
	{
		return $result;
	}

	protected function sendAddEvent(AddCommand $command, Workgroup $entity): void
	{

	}

	protected function sendBeforeUpdateEvent(UpdateCommand $command, Workgroup $entity): void
	{

	}

	protected function sendUpdateEvent(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): void
	{

	}

	protected function sendDeleteEvent(DeleteCommand $command, Workgroup $entityBefore): void
	{

	}

	protected function init(): void
	{
		$this->validationService = ServiceLocator::getInstance()->get('main.validation.service');
		$this->registry = GroupRegistry::getInstance();
	}

	private function runAddHandlers(AddCommand $command, Workgroup $entity): GroupResult
	{
		$id = $entity->getId();

		$result = new GroupResult();
		foreach ($this->getAddHandlers() as $handler)
		{
			$handlerResult = $handler->add($command, $entity);
			if ($handlerResult->isGroupChanged())
			{
				$entity = $this->registry->invalidate($id)->get($id);
				if ($entity === null)
				{
					$result->addError(new Error('Collab lost during add'));

					return $result;
				}
			}
			$result->merge($handlerResult);
		}

		return $result;
	}

	private function runUpdateHandlers(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): GroupResult
	{
		$id = $command->getId();

		$result = new GroupResult();
		foreach ($this->getUpdateHandlers() as $handler)
		{
			$handlerResult = $handler->update($command, $entityBefore, $entityAfter);
			if ($handlerResult->isGroupChanged())
			{
				$entityAfter = $this->registry->invalidate($id)->get($id);
				if ($entityAfter === null)
				{
					$result->addError(new Error('Collab lost during update'));

					return $result;
				}
			}

			$result->merge($handlerResult);
		}

		return $result;
	}

	private function runDeleteHandlers(DeleteCommand $command, Workgroup $entityBefore): GroupResult
	{
		$result = new GroupResult();
		foreach ($this->getDeleteHandlers() as $handler)
		{
			$handlerResult = $handler->delete($command, $entityBefore);
			$result->merge($handlerResult);
		}

		return $result;
	}

	private function getEntityById(int $id, bool $force = true): ?Workgroup
	{
		if ($force)
		{
			$this->registry->invalidate($id);
		}

		return $this->registry->get($id);
	}

	private function validate(AbstractCommand $command): GroupResult
	{
		$result = new GroupResult();

		$validationResult = $this->validationService->validate($command);
		if (!$validationResult->isSuccess())
		{
			$result->merge($validationResult);

			return $result;
		}

		return $result;
	}
}