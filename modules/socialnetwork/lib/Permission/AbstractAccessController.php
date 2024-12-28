<?php

declare(strict_types=1);


namespace Bitrix\Socialnetwork\Permission;

use Bitrix\Main\Access\AccessibleController;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\BaseAccessController;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Socialnetwork\Permission\Trait\AccessErrorTrait;
use Bitrix\Socialnetwork\Permission\Trait\AccessUserTrait;
use ReflectionClass;
use ReflectionException;

abstract class AbstractAccessController extends BaseAccessController implements AccessErrorInterface
{
	use AccessUserTrait;
	use AccessErrorTrait;

	abstract public function getModel(array|Arrayable $data): AccessModelInterface;
	abstract public function getDictionary(): AccessDictionaryInterface;

	public function forward(AccessibleController|string $controller, string $action, AccessibleItem $item, mixed $parameters = null): bool
	{
		$controllerInstance = $this->createController($controller);

		$isAccess = $controllerInstance->check($action, $item, $parameters);

		if (!$isAccess && $controllerInstance instanceof AccessErrorInterface)
		{
			$this->addErrors(...$controllerInstance->getErrors());
		}

		return $isAccess;
	}

	public function addErrors(Error ...$errors): void
	{
		foreach ($errors as $error)
		{
			$this->addError($error->getCode(), $error->getMessage());
		}
	}

	/**
	 * @throws ArgumentException
	 */
	protected function createController(AccessibleController|string $controller): AccessibleController
	{
		if ($controller instanceof AccessibleController)
		{
			return $controller;
		}

		try
		{
			$reflection = new ReflectionClass($controller);

			/** @var AccessibleController $controllerInstance */
			$controllerInstance = $reflection->newInstance(userId: $this->user->getUserId());
		}
		catch (ReflectionException $e)
		{
			throw new ArgumentException($e->getMessage(), $e->getCode());
		}

		if (!$controllerInstance instanceof AccessibleController)
		{
			throw new ArgumentException('Wrong controller class');
		}

		return $controllerInstance;
	}
}