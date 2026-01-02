<?php

namespace Bitrix\Rest\V3\Schema;

use Bitrix\Main\ClassLocator;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Rest\V3\Attributes\DtoType;
use Bitrix\Rest\V3\Attributes\Scope;
use Bitrix\Rest\V3\CacheManager;
use Bitrix\Rest\V3\Controllers\RestController;
use Bitrix\Rest\V3\Dto\Dto;
use Bitrix\Rest\V3\Exceptions\TooManyAttributesException;
use ReflectionClass;
use ReflectionMethod;

final class SchemaManager
{
	private const ROUTING_CACHE_KEY = 'rest.v3.SchemaManager.routing.cache.key';
	private const CONTROLLERS_DATA_CACHE_KEY = 'rest.v3.SchemaManager.controllersData.cache.key';
	private const METHOD_DESCRIPTIONS_CACHE_KEY = 'rest.v3.SchemaManager.methodDescriptions.cache.key';

	public const CACHE_DIR = 'rest/v3';

	private array $reflections = [];

	public function getRouteAliases(): array
	{
		$routes = CacheManager::get(self::ROUTING_CACHE_KEY);
		if ($routes === null)
		{
			$modulesConfig = ServiceLocator::getInstance()->get(ModuleManager::class)->getConfigs();

			foreach ($modulesConfig as $config) {
				if (empty($config['routes']) || !is_array($config['routes'])) {
					continue;
				}

				foreach ($config['routes'] as $route => $routeMethod) {
					$routes[$route] = strtolower($routeMethod);
				}
			}

			CacheManager::set(self::ROUTING_CACHE_KEY, $routes);
		}

		return $routes;
	}

	public function getMethodDescriptions(): array
	{
		$methodDescriptions = CacheManager::get(self::METHOD_DESCRIPTIONS_CACHE_KEY);
		if ($methodDescriptions === null)
		{
			$methodDescriptions = [
				'batch' => [
					'method' => 'execute',
					'controller' => null,
					'scope' => \CRestUtil::GLOBAL_SCOPE,
					'moduleId' => 'rest',
				]
			];

			$controllersData = $this->getControllersData()['byName'];

			foreach ($controllersData as $controllerData)
			{
				foreach ($controllerData->getController()->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
				{
					if (!str_ends_with($method->name, 'Action'))
					{
						continue;
					}

					$scope = $controllerData->getModuleId();
					$attributes = $method->getAttributes(Scope::class);

					if (!empty($attributes))
					{
						/** @var Scope $scopeAttribute */
						$scopeAttribute = $attributes[0]->newInstance();
						$scope = $scopeAttribute->value;
					}

					$methodName = str_replace('Action', '', $method->name);
					$actionUri = $controllerData->getMethodUri($methodName);

					$methodDescriptions[$actionUri] = [
						'method' => $methodName,
						'controller' => $controllerData->getController()->name,
						'scope' => $scope,
						'moduleId' => $controllerData->getModuleId(),
					];

					CacheManager::set($this->getActionCacheKey($actionUri), $methodDescriptions[$actionUri]);
				}
			}

			CacheManager::set(self::METHOD_DESCRIPTIONS_CACHE_KEY, $methodDescriptions);
		}

		foreach ($methodDescriptions as $actionUri => $methodDescriptionData)
		{
			$methodDescriptions[$actionUri] = new MethodDescription(
				$methodDescriptionData['method'],
				$methodDescriptionData['controller'],
				$methodDescriptionData['scope'],
				$methodDescriptionData['moduleId']
			);
		}

		return $methodDescriptions;
	}

	public function getMethodDescription(string $actionUri): ?MethodDescription
	{
		$methodDescriptionData = CacheManager::get($this->getActionCacheKey($actionUri));
		if ($methodDescriptionData === null)
		{
			$methodDescription = $this->getMethodDescriptions()[$actionUri] ?? null;
		}
		else
		{
			$methodDescription = new MethodDescription(
				$methodDescriptionData['method'],
				$methodDescriptionData['controller'],
				$methodDescriptionData['scope'],
				$methodDescriptionData['moduleId']
			);
		}

		return $methodDescription;
	}

	private function getDtoClassFromAttributes(ReflectionClass $controllerReflection): ?string
	{
		$dtoTypeAttributes = $controllerReflection->getAttributes(DtoType::class);
		if (count($dtoTypeAttributes) > 1)
		{
			throw new TooManyAttributesException($controllerReflection->getName(), DtoType::class, 1);
		}

		foreach ($dtoTypeAttributes as $attribute)
		{
			/** @var DtoType $instance */
			$instance = $attribute->newInstance();
			if (!isset($this->reflections[$instance->type]))
			{
				$dtoReflection = new ReflectionClass($instance->type);
				if (!$dtoReflection->isSubclassOf(Dto::class))
				{
					return null;
				}
				$this->reflections[$instance->type] = $dtoReflection;
			}

			return $instance->type;
		}

		return null;
	}

	public function getControllerDataByName(string $name): ?ControllerData
	{
		$controllerCacheData = CacheManager::get($this->getControllerCacheKey($name));
		if ($controllerCacheData === null)
		{
			$controllersData = $this->getControllersData();
			$controllerData = $controllersData['byName'][$name] ?? null;
		}
		else
		{
			$controllerData = new ControllerData(...$controllerCacheData);
		}
		return $controllerData;
	}

	public function getControllersByModules(): array
	{
		$controllersData = $this->getControllersData();
		return $controllersData['byModule'] ?? [];
	}

	private function getControllersData(): array
	{
		$controllersData = [
			'byName' => [],
			'byModule' => [],
		];

		$items = CacheManager::get(self::CONTROLLERS_DATA_CACHE_KEY);
		if ($items !== null)
		{
			foreach ($items as $controllerCacheData)
			{
				$controllerData = new ControllerData(...$controllerCacheData);
				$controllersData['byName'][$controllerData->getController()->getName()] =
				$controllersData['byModule'][$controllerData->getModuleId()][$controllerData->getController()->getName()] = $controllerData;
			}
		}
		else
		{
			$controllersCacheData = [];
			$modulesConfig = ServiceLocator::getInstance()->get(ModuleManager::class)->getConfigs();

			foreach ($modulesConfig as $moduleId => $config)
			{
				if (empty($config['defaultNamespace']))
				{
					continue;
				}

				$namespaces = array_merge(
					[$config['defaultNamespace']],
					(array)($config['namespaces'] ?? []),
				);

				if (!Loader::includeModule($moduleId))
				{
					continue;
				}

				foreach ($namespaces as $namespace)
				{
					$classes = ClassLocator::getClassesByNamespace($namespace);
					foreach ($classes as $class)
					{
						$controllerReflection = new ReflectionClass($class);
						if (!$controllerReflection->isSubclassOf(RestController::class))
						{
							continue;
						}

						$dtoClass = $this->getDtoClassFromAttributes($controllerReflection);

						$controllerCacheData = [
							'moduleId' => $moduleId,
							'controllerClass' => $class,
							'dtoClass' => $dtoClass,
							'namespace' => $namespace,
						];

						CacheManager::set($this->getControllerCacheKey($controllerReflection->getName()), $controllerCacheData);
						$controllersCacheData[$controllerReflection->getName()] = $controllerCacheData;
						$controllerData = new ControllerData(...$controllerCacheData);
						$controllersData['byName'][$controllerData->getController()->getName()] =
						$controllersData['byModule'][$controllerData->getModuleId()][$controllerData->getController()->getName()] = $controllerData;
					}
				}
			}

			CacheManager::set(self::CONTROLLERS_DATA_CACHE_KEY, $controllersCacheData);
		}

		return $controllersData;
	}

	private function getControllerCacheKey(string $controllerName): string
	{
		return self::CONTROLLERS_DATA_CACHE_KEY . '.' . $controllerName;
	}

	private function getActionCacheKey(string $action): string
	{
		return self::METHOD_DESCRIPTIONS_CACHE_KEY . '.' . $action;
	}
}
