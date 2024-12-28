<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Decorator;

use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Command\DeleteCommand;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Exception\GroupNotAddedException;
use Bitrix\Socialnetwork\Control\Exception\GroupNotDeletedException;
use Bitrix\Socialnetwork\Control\Exception\GroupNotUpdatedException;
use Bitrix\Socialnetwork\Control\GroupResult;

class AccessDecorator extends AbstractGroupServiceDecorator
{
	/**
	 * @throws GroupNotAddedException
	 */
	public function add(AddCommand $command): GroupResult
	{
		$result = new GroupResult();

		$controller = $command->getAccessControllerByInitiator();
		if ($controller === null)
		{
			throw new GroupNotAddedException('Access controller not found');
		}

		$model = $controller->getModel($command);
		$action = $controller->getDictionary()->create();

		if (!$controller->check($action, $model))
		{
			$result->addErrors($controller->getErrors());

			return $this->source->finalizeAddResult($result);
		}

		return $this->source->add($command);
	}

	/**
	 * @throws GroupNotUpdatedException
	 */
	public function update(UpdateCommand $command): GroupResult
	{
		$result = new GroupResult();

		$controller = $command->getAccessControllerByInitiator();
		if ($controller === null)
		{
			throw new GroupNotUpdatedException('Access controller not found');
		}

		$action = $controller->getDictionary()->update();
		$model = $controller->getModel($command);
		$entity = $this->source->registry->get($command->getId());

		if (!$controller->check($action, $model, $entity))
		{
			$result->addErrors($controller->getErrors());

			return $this->source->finalizeUpdateResult($result);
		}

		return $this->source->update($command);
	}

	/**
	 * @throws GroupNotDeletedException
	 */
	public function delete(DeleteCommand $command): GroupResult
	{
		$result = new GroupResult();

		$controller = $command->getAccessControllerByInitiator();
		if ($controller === null)
		{
			throw new GroupNotDeletedException('Access controller not found');
		}

		$action = $controller->getDictionary()->delete();

		if (!$controller->checkByItemId($action, $command->getId()))
		{
			$result->addErrors($controller->getErrors());

			return $this->finalizeDeleteResult($result);
		}

		return $this->source->delete($command);
	}
}