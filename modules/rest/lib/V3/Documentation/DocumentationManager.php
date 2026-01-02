<?php

namespace Bitrix\Rest\V3\Documentation;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Rest\V3\Attributes\Editable;
use Bitrix\Rest\V3\Attributes\ElementType;
use Bitrix\Rest\V3\Attributes\Sortable;
use Bitrix\Rest\V3\Documentation\Attributes\Deprecated;
use Bitrix\Rest\V3\Dto\Dto;
use Bitrix\Rest\V3\Dto\DtoCollection;
use Bitrix\Rest\V3\Dto\PropertyHelper;
use Bitrix\Rest\V3\Interaction\Request\AddRequest;
use Bitrix\Rest\V3\Interaction\Request\AggregateRequest;
use Bitrix\Rest\V3\Interaction\Request\DeleteRequest;
use Bitrix\Rest\V3\Interaction\Request\GetRequest;
use Bitrix\Rest\V3\Interaction\Request\ListRequest;
use Bitrix\Rest\V3\Interaction\Request\Request;
use Bitrix\Rest\V3\Interaction\Request\TailRequest;
use Bitrix\Rest\V3\Interaction\Request\UpdateRequest;
use Bitrix\Rest\V3\Interaction\Response\AddResponse;
use Bitrix\Rest\V3\Interaction\Response\AggregateResponse;
use Bitrix\Rest\V3\Interaction\Response\ArrayResponse;
use Bitrix\Rest\V3\Interaction\Response\BooleanResponse;
use Bitrix\Rest\V3\Interaction\Response\DeleteResponse;
use Bitrix\Rest\V3\Interaction\Response\GetResponse;
use Bitrix\Rest\V3\Interaction\Response\ListResponse;
use Bitrix\Rest\V3\Interaction\Response\Response;
use Bitrix\Rest\V3\Interaction\Response\UpdateResponse;
use Bitrix\Rest\V3\Schema\ControllerData;
use Bitrix\Rest\V3\Schema\ModuleManager;
use Bitrix\Rest\V3\Schema\SchemaManager;
use Bitrix\Rest\V3\Structures\Aggregation\AggregationType;
use CRestApiServer;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;

class DocumentationManager
{
	private SchemaManager $schemaManager;

	private ModuleManager $moduleManager;

	private array $reflections = [];

	private const AVAILABLE_DEFAULT_RESPONSES = [
		ArrayResponse::class,
		GetResponse::class,
		ListResponse::class,
		AddResponse::class,
		BooleanResponse::class,
		DeleteResponse::class,
		UpdateResponse::class,
		AggregateResponse::class,
	];


	public function __construct()
	{
		$this->schemaManager = ServiceLocator::getInstance()->get(SchemaManager::class);
		$this->moduleManager = ServiceLocator::getInstance()->get(ModuleManager::class);
	}
	/**
	 * @throws ReflectionException
	 */
	public function generateDataForJson(): array
	{
		$file = [
			'openapi' => '3.0.0',
			'info' => [
				'title' => 'Bitrix24 REST V3 API',
			],
			'tags' => [],
			'paths' => [],
			'components' => ['schemas' => []],
		];

		$customModuleSchemas = $this->getCustomModuleSchemas();
		$customModuleMethods = $this->getCustomModuleMethods();
		$customModuleRoutes = $this->schemaManager->getRouteAliases();

		$moduleControllers = $this->schemaManager->getControllersByModules();

		foreach ($moduleControllers as $moduleId => $controllers)
		{
			$file['tags'][] = [
				'name' => $moduleId,
				'description' => $moduleId . ' module methods',
			];
			/** @var ControllerData $controllerData */
			foreach ($controllers as $controllerData)
			{
				if ($controllerData->getDto() !== null)
				{
					$file['components']['schemas'][$controllerData->getDto()->getShortName()] =
						$customModuleSchemas[$moduleId][$controllerData->getDto()->getShortName()]
						?? $this->getDtoProperties($controllerData->getDto())
					;

				}
				$this->addControllerMethodsToFile($controllerData, $customModuleMethods, $file);
			}
		}

		foreach ($customModuleRoutes as $customRoute => $moduleRoute)
		{
			if (isset($file['paths'][$moduleRoute]))
			{
				$file['paths'][$customRoute] = $file['paths'][$moduleRoute];
			}
		}

		return $file;
	}

	private function getCustomModuleSchemas(): array
	{
		$documentationSchemas = [];
		$moduleConfigs = $this->moduleManager->getConfigs();
		foreach ($moduleConfigs as $moduleId => $config)
		{
			if (!empty($config['documentation']['schemas']) && is_array($config['documentation']['schemas']))
			{
				foreach ($config['documentation']['schemas'] as $schemaObject => $schemaClass)
				{
					if (is_subclass_of($schemaClass, SchemaProvider::class))
					{
						$class = new $schemaClass();
						$documentationSchemas[$moduleId][$schemaObject] = $class->getDocumentation();
					}
				}
			}
		}

		return $documentationSchemas;
	}

	private function getCustomModuleMethods(): array
	{
		$documentationMethods = [];
		$moduleConfigs = $this->moduleManager->getConfigs();
		foreach ($moduleConfigs as $moduleId => $config)
		{
			if (!empty($config['documentation']['methods']) && is_array($config['documentation']['methods']))
			{
				foreach ($config['documentation']['methods'] as $methodUri => $methodDocumentationClass)
				{
					if (is_subclass_of($methodDocumentationClass, MethodProvider::class))
					{
						$class = new $methodDocumentationClass();
						$documentationMethods[$moduleId][$methodUri] = $class->getDocumentation();
					}
				}
			}
		}

		return $documentationMethods;
	}

	/**
	 * @throws ReflectionException
	 */
	private function addControllerMethodsToFile(ControllerData $controllerData, array $customModuleMethods, array &$file): void
	{
		$methods = $controllerData->getController()->getMethods(ReflectionMethod::IS_PUBLIC);
		foreach ($methods as $method)
		{
			if (str_ends_with($method->name, 'Action') && $method->getReturnType() instanceof ReflectionNamedType)
			{
				$returnType = $method->getReturnType()->getName();

				if (!is_subclass_of($returnType, Response::class))
				{
					continue;
				}

				$methodName = substr($method->name, 0, -6); // cut Action
				$methodUri = $controllerData->getMethodUri($methodName);

				if (isset($customModuleMethods[$controllerData->getModuleId()][$methodUri]))
				{
					$methodData = $customModuleMethods[$controllerData->getModuleId()][$methodUri];
				}
				else
				{
					$methodData = $this->getMethodData($method, $returnType, $controllerData);
					if ($methodData === null)
					{
						continue; // skip unknown return types
					}
				}

				$file['paths'][$methodUri]['post'] = $methodData;
			}
		}
	}

	private function getResponseProperties(string $returnTypeClass, ?ReflectionClass $dtoReflection): array
	{
		$getResponseByClass = function (string $responseClass) use ($dtoReflection)
		{
			return match ($responseClass)
			{
				ArrayResponse::class => [
					'type' => 'object',
				],
				GetResponse::class => [
					'type' => 'object',
					'properties' => $dtoReflection ? [
						'item' => [
							'$ref' => '#/components/schemas/' . $dtoReflection->getShortName(),
						],
					] : [],
				],
				ListResponse::class => [
					'type' => 'array',
					'items' => $dtoReflection ? [
						'$ref' => '#/components/schemas/' . $dtoReflection->getShortName(),
					] : [],
				],
				AddResponse::class => [
					'type' => 'object',
					'properties' => [
						'id' => 'int64',
					],
				],
				BooleanResponse::class, DeleteResponse::class, UpdateResponse::class => [
					'type' => 'object',
					'properties' => [
						'result' => 'boolean',
					],
				],
				AggregateResponse::class => [
					'type' => 'object',
					'properties' => [
						'result' => [
							'type' => 'object',
							'properties' => $this->aggregateProperties(),
						],
					],
				],
				default => [],
			};
		};

		if (in_array($returnTypeClass, self::AVAILABLE_DEFAULT_RESPONSES, true))
		{
			return $getResponseByClass($returnTypeClass);
		}

		foreach (self::AVAILABLE_DEFAULT_RESPONSES as $responseClass)
		{
			if (is_subclass_of($returnTypeClass, $responseClass))
			{
				return $getResponseByClass($responseClass);
			}
		}

		return [];
	}

	private function aggregateProperties(): array
	{
		$aggregationProperties = [];
		foreach (AggregationType::cases() as $aggregationType)
		{
			$aggregationProperties[$aggregationType->value] = [
				'type' => 'object',
				'properties' => [
					'field' => 'string',
				],
			];
		}

		return $aggregationProperties;
	}

	private function getDtoProperty(ReflectionProperty $dtoProperty): array
	{
		if (!$dtoProperty->getType() instanceof ReflectionNamedType)
		{
			return [
				'type' => 'unknown',
				'format' => 'unknown',
			];
		}

		$type = $dtoProperty->getType()->getName();

		$types = [
			'float' => ['type' => 'float'],
			'array' => ['type' => 'array'],
			'bool' => ['type' => 'boolean'],
			'int' => ['type' => 'integer', 'format' => 'int64'],
			'string' => ['type' => 'string'],
			DateTime::class => ['type' => 'string', 'format' => 'date-time'],
			Date::class => ['type' => 'string', 'format' => 'date'],
		];

		if (isset($types[$type]))
		{
			return $types[$type];
		}
		else if ($type === DtoCollection::class)
		{
			return $this->getDtoCollectionProperty($dtoProperty);
		}

		if (isset($this->reflections[$type]) && $this->reflections[$type]->isSubclassOf(Dto::class))
		{
			return ['$ref' => '#/components/schemas/' . $this->reflections[$type]->getShortName()];
		}
		else
		{
			return [
				'type' => 'unknown',
				'format' => 'unknown',
			];
		}
	}

	private function getDtoCollectionProperty(ReflectionProperty $dtoProperty): array
	{
		foreach ($dtoProperty->getAttributes(ElementType::class) as $propertyAttribute)
		{
			/** @var ElementType $instance */
			$instance = $propertyAttribute->newInstance();
			if (!isset($this->reflections[$instance->type]))
			{
				return [
					'type' => 'array',
					'items' => [
						'type' => 'object',
						'properties' => [
							'id' => [
								'type' => 'string',
							],
						],
					],
				];
			}

			$dtoCollectionPropertyReflection = $this->reflections[$instance->type];

			return [
				'type' => 'array',
				'items' => [
					'$ref' => '#/components/schemas/' . $dtoCollectionPropertyReflection->getShortName(),
				],
			];
		}

		return [];
	}

	private function getDtoProperties(ReflectionClass $dtoReflection): array
	{
		$dtoProperties = $dtoReflection->getProperties(ReflectionProperty::IS_PUBLIC);
		$result = [
			'type' => 'object',
			'properties' => [],
		];

		foreach ($dtoProperties as $dtoProperty)
		{
			$result['properties'][$dtoProperty->getName()] = $this->getDtoProperty($dtoProperty);
			if (!$dtoProperty->getType()->allowsNull())
			{
				$result['required'][] = $dtoProperty->getName();
			}
		}

		return $result;
	}

	private function getRequestTypeProperties(ReflectionParameter $parameter): array
	{
		if (!$parameter->getType() instanceof ReflectionNamedType)
		{
			return [
				'type' => 'unknown',
			];
		}

		$result = match($parameter->getType()->getName())
		{
			'int' => [
				'type' => 'integer',
				'example' => 1,
			],
			'string' => [
				'type' => 'string',
				'example' => 'string',
			],
			'float' => [
				'type' => 'float',
				'example' => 1.0,
			],
			'bool' => [
				'type' => 'boolean',
				'example' => true,
			],
			'array' => [
				'type' => 'array',
			],
		};

		if (!$parameter->getType()->allowsNull())
		{
			$result['required'] = true;
		}
		else
		{
			$result['nullable'] = true;
		}

		if ($parameter->isDefaultValueAvailable())
		{
			$result['example'] = $parameter->getDefaultValue();
		}

		return $result;
	}

	private function getRequestClassProperties(string $requestClass, ?ReflectionClass $dto): array
	{
		$examples = function (string $type, bool $allowNull = false) use ($requestClass, $dto)
		{
			$baseArray = match ($type)
			{
				'id' => [
					'type' => 'integer',
					'example' => 1,
				],
				'cursor' => [
					'type' => 'object',
					'example' => [
						'field' => 'id',
						'value' => 0,
						'order' => 'ASC'
					],
				],
				'filter' => [
					'type' => 'array',
					'example' => [['id', '>=', 1], ['id', 1], ['id', 'in', [1, 2, 3]]],
				],
				'select' => [
					'type' => 'array',
					'items' => [
						'type' => 'string',
						'example' => array_map(fn($property) => $property->getName(), PropertyHelper::getProperties($dto)),
					],
				],
				'fields' => [
					'type' => 'object',
					'properties' => array_reduce(
						PropertyHelper::getPropertiesWithAttribute($dto, Editable::class),
						function ($data, $property)
						{
							$data[$property->getName()] = [
								'type' => $property->getType()->getName(),
								'format' => 'format',
								'example' => 'example',
							];

							return $data;
						},
						[],
					),
				],
				'order' => [
					'type' => 'object',
					'properties' => array_reduce(
						PropertyHelper::getPropertiesWithAttribute($dto, Sortable::class),
						function ($data, $property)
						{
							$data[$property->getName()] = [
								'type' => 'string',
								'example' => 'ASC',
							];

							return $data;
						},
						[],
					),
				],
				'pagination' => [
					'type' => 'object',
					'properties' => [
						'page' => ['type' => 'integer', 'example' => 2],
						'limit' => ['type' => 'integer', 'example' => 20],
						'offset' => ['type' => 'integer', 'example' => 0],
					],
				],
				'aggregate' => [
					'type' => 'object',
					'properties' => [
						'count' => ['type' => 'array', 'items' => ['type' => 'string'], 'example' => ['id']],
						'min' => ['type' => 'array', 'items' => ['type' => 'string'], 'example' => ['id']],
						'max' => ['type' => 'array', 'items' => ['type' => 'string'], 'example' => ['id']],
						'avg' => ['type' => 'array', 'items' => ['type' => 'string'], 'example' => ['id']],
						'sum' => ['type' => 'array', 'items' => ['type' => 'string'], 'example' => ['id']],
						'countDistinct' => ['type' => 'array', 'items' => ['type' => 'string'], 'example' => ['id']],
					],
				],
				default => [],
			};

			if ($allowNull)
			{
				$baseArray['nullable'] = true;
			}
			else
			{
				$baseArray['required'] = true;
			}

			return $baseArray;
		};

		return match ($requestClass)
		{
			GetRequest::class => [
				'id' => $examples('id'),
				'select' => $examples('select', true),
			],
			ListRequest::class => [
				'select' => $examples('select', true),
				'filter' => $examples('filter', true),
				'order' => $examples('order', true),
				'pagination' => $examples('pagination', true),
			],
			TailRequest::class => [
				'select' => $examples('select', true),
				'filter' => $examples('filter', true),
				'cursor' => $examples('cursor', true),
			],
			AddRequest::class => [
				'fields' => $examples('fields', true),
			],
			UpdateRequest::class => [
				'id' => $examples('id', true),
				'filter' => $examples('filter'),
				'fields' => $examples('fields'),
			],
			DeleteRequest::class => [
				'id' => $examples('id'),
				'filter' => $examples('filter'),
			],
			AggregateRequest::class => [
				'select' => $examples('aggregate'),
			],
			default => [],
		};
	}

	/**
	 * @throws ReflectionException
	 */
	private function getMethodData(ReflectionMethod $method, string $returnTypeClass, ControllerData $controllerData): ?array
	{
		$properties = [];
		$methodData = [];
		$deprecated = !empty($method->getAttributes(Deprecated::class));
		$parameters = $method->getParameters();
		foreach ($parameters as $parameter)
		{
			if ($parameter->hasType())
			{
				if (!$parameter->getType()->isBuiltin())
				{
					$requestTypeReflection = new ReflectionClass($parameter->getType()->getName());
					if ($requestTypeReflection->isSubclassOf(Request::class))
					{
						if ($controllerData->getDto() !== null)
						{
							array_push($properties, $this->getRequestClassProperties($parameter->getType()->getName(), $controllerData->getDto()));
						}
					}
					else
					{
						return null; // skip unknown request types
					}
				}
				else
				{
					$properties[$parameter->getName()] = $this->getRequestTypeProperties($parameter);
				}
			}
		}

		if ($deprecated)
		{
			$methodData['deprecated'] = true;
		}

		$methodData['tags'] = [$controllerData->getModuleId()];

		$methodData['requestBody'] = [
			'content' => [
				'application/json' => [
					'schema' => [
						'type' => 'object',
						'properties' => $properties,
					],
				],
			],
		];

		$methodData['responses'] = [
			200 => [
				'description' => 'Success response',
				'content' => [
					'application/json' => [
						'schema' => [
							'type' => 'object',
							'properties' => [
								'result' => $this->getResponseProperties($returnTypeClass, $controllerData->getDto()),
							],
						],
					],
				],
			],
		];

		return $methodData;
	}
}
