<?php

namespace Bitrix\Rest\V3\Structures;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Rest\V3\Attributes\ElementType;
use Bitrix\Rest\V3\Attributes\RelationToMany;
use Bitrix\Rest\V3\Attributes\RelationToOne;
use Bitrix\Rest\V3\Attributes\ResolvedBy;
use Bitrix\Rest\V3\Dto\DtoCollection;
use Bitrix\Rest\V3\Dto\PropertyHelper;
use Bitrix\Rest\V3\Exceptions\InvalidSelectException;
use Bitrix\Rest\V3\Exceptions\UnknownDtoPropertyException;
use Bitrix\Rest\V3\Interaction\Relation;
use Bitrix\Rest\V3\Interaction\Request\ListRequest;
use Bitrix\Rest\V3\Interaction\Request\Request;
use Bitrix\Rest\V3\Schema\SchemaManager;
use Bitrix\Rest\V3\Structures\Ordering\OrderStructure;

/**
 * Used as list of DTO fields to return
 */
final class SelectStructure extends Structure
{
	use UserFieldsTrait;

	/** @var string[] $items */
	protected array $items = [];

	protected \ReflectionClass $dtoReflection;

	protected bool $multiple = false;

	protected array $relationFields = [];

	public static function create(mixed $value, string $dtoClass, ?Request $request = null): self
	{
		$structure = new self();

		$value = (array)$value;

		$dtoReflection = new \ReflectionClass($dtoClass);
		$structure->dtoReflection = $dtoReflection;

		if (!empty($value))
		{
			foreach ($value as $item)
			{
				if (!is_array($item))
				{
					if (str_starts_with($item, 'UF_'))
					{
						$structure->userFields[] = $item;

						continue;
					}

					if (strpos($item, '.') === false)
					{
						if (!PropertyHelper::isValidProperty($dtoReflection, $item))
						{
							throw new UnknownDtoPropertyException($dtoClass, $item);
						}

						$structure->items[] = $item;

						continue;
					}

					self::processRelationField($item, $structure, $request);
				}
				else
				{
					throw new InvalidSelectException($item);
				}
			}
		}

		return $structure;
	}

	public function getList($includeUserFields = false): array
	{
		return $includeUserFields ? array_merge($this->items, $this->getUserFields()) : $this->items;
	}

	private static function processRelationField(string $field, self $structure, Request $request): void
	{
		$parts = explode('.', $field, 2);
		$relationName = $parts[0];
		$remaining = $parts[1] ?? null;

		$relation = $request->getRelation($relationName);
		if ($relation === null)
		{
			if ($structure->dtoReflection->hasProperty($relationName))
			{
				$type = $structure->dtoReflection->getProperty($relationName)->getType();
				if ($type instanceof \ReflectionNamedType && !$type->isBuiltin())
				{
					if ($type->getName() === DtoCollection::class)
					{
						$elementTypeAttributes = $structure->dtoReflection->getProperty($relationName)->getAttributes(ElementType::class);
						if (empty($elementTypeAttributes))
						{
							throw new InvalidSelectException($field);
						}

						/** @var ElementType $elementTypeInstance */
						$elementTypeInstance = $elementTypeAttributes[0]->newInstance();
						$childDtoReflection = PropertyHelper::getReflection($elementTypeInstance->type);
					}
					else
					{
						$childDtoReflection = PropertyHelper::getReflection($type->getName());
					}
				}
				else
				{
					throw new InvalidSelectException($field);
				}

				$resolvedByAttributes = $childDtoReflection->getAttributes(ResolvedBy::class);
				if (empty($resolvedByAttributes))
				{
					throw new InvalidSelectException($field);
				}

				/** @var ResolvedBy $resolvedByInstance */
				$resolvedByInstance = $resolvedByAttributes[0]->newInstance();

				$attributes = $structure->dtoReflection->getProperty($relationName)->getAttributes();
				foreach ($attributes as $attribute)
				{
					if (in_array($attribute->getName(), [RelationToOne::class, RelationToMany::class], true))
					{
						$controllerData = ServiceLocator::getInstance()->get(SchemaManager::class)->getControllerDataByName($resolvedByInstance->controller);
						$arguments = $attribute->getArguments();
						if (!empty($arguments))
						{
							$relationRequest = new ListRequest($childDtoReflection->getName());
							[$fromField, $toField] = $arguments;
							if ($fromField === null || $toField === null)
							{
								throw new InvalidSelectException($field);
							}
							$relationRequest->select = self::create([], $relationRequest->getDtoClass(), $relationRequest);
							if (isset($arguments[2]['order']) && is_array($arguments[2]['order']))
							{
								$relationRequest->order = OrderStructure::create($arguments[2]['order'], $relationRequest->getDtoClass(), $relationRequest);
							}

							$method = $controllerData->getMethodUri('list');
							$relation = new Relation($relationName, $method, $fromField, $toField, $relationRequest, $attribute->getName() == RelationToMany::class);
							$relation->getRequest()->select->relationFields[] = $toField;
							$request->addRelation($relation);
							$structure->relationFields[] = $fromField;
						}
						else
						{
							throw new InvalidSelectException($field);
						}

						break;
					}
				}
			}
			else
			{
				throw new UnknownDtoPropertyException($structure->dtoReflection->name, $field);
			}
		}

		if ($remaining !== null)
		{
			$relation->getRequest()->select->items[] = $remaining;
			if (strpos($remaining, '.') !== false)
			{

				self::processRelationField($remaining, $relation->getRequest()->select, $relation->getRequest());
			}
			else
			{
				if (!PropertyHelper::isValidProperty($relation->getRequest()->select->dtoReflection, $remaining))
				{
					throw new UnknownDtoPropertyException($relation->getRequest()->select->dtoReflection->name, $remaining);
				}
			}
		}
	}

	public function getRelationFields(): array
	{
		return $this->relationFields;
	}
}
