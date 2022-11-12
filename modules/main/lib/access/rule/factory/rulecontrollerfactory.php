<?php

namespace Bitrix\Main\Access\Rule\Factory;

use Bitrix\Main\Access\AccessibleController;
use Bitrix\Main\Access\Rule\RuleFactory;
use Bitrix\Main\Access\Rule\RuleInterface;
use ReflectionClass;

class RuleControllerFactory implements RuleFactory
{
	protected const SUFFIX = 'Rule';

	public function createFromAction(string $action, AccessibleController $controller): ?RuleInterface
	{
		$className = $this->getClassName($action, $controller);
		if (!$className  || !class_exists($className ))
		{
			return null;
		}

		$ref = new ReflectionClass($className);
		if ($ref->implementsInterface(RuleInterface::class))
		{
			return $ref->newInstance($controller);
		}

		return null;
	}

	protected function getClassName(string $action, AccessibleController $controller): ?string
	{
		$action = explode('_', $action);
		$action = array_map(fn($el) => ucfirst(mb_strtolower($el)), $action);

		return $this->getNamespace($controller) . implode($action) . static::SUFFIX;
	}

	protected function getNamespace(AccessibleController $controller): string
	{
		$class = new ReflectionClass($controller);
		$namespace = $class->getNamespaceName();

		return $namespace.'\\'.static::SUFFIX.'\\';
	}
}
