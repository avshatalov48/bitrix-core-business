<?php

namespace Bitrix\Main\Access\Filter\Factory;

use Bitrix\Main\Access\AccessibleController;
use Bitrix\Main\Access\Filter\AccessFilter;
use Bitrix\Main\Access\Filter\FilterFactory;
use ReflectionClass;

class FilterControllerFactory implements FilterFactory
{
	protected const SUFFIX = 'Filter';

	public function createFromAction(string $action, AccessibleController $controller): ?AccessFilter
	{
		$className = $this->getClassName($action, $controller);
		if (!$className  || !class_exists($className ))
		{
			return null;
		}

		$ref = new ReflectionClass($className);
		if ($ref->implementsInterface(AccessFilter::class))
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
		$class = new \ReflectionClass($controller);
		$namespace = $class->getNamespaceName();

		return $namespace.'\\'.static::SUFFIX.'\\';
	}
}
