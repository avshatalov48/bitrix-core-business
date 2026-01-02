<?php

namespace Bitrix\Rest\V3\Interaction\Request;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\SystemException;
use Bitrix\Rest\V3\Attributes\OrmEntity;
use Bitrix\Rest\V3\Dto\Dto;
use Bitrix\Rest\V3\Exceptions\InvalidJsonException;
use Bitrix\Rest\V3\Exceptions\Validation\RequiredFieldInRequestException;
use Bitrix\Rest\V3\Interaction\Relation;
use Bitrix\Rest\V3\Structures\Structure;
use ReflectionClass;
use ReflectionNamedType;

abstract class Request
{
	protected ?string $ormEntityClass = null;

	/**
	 * @var Relation[]
	 */
	protected array $relations = [];

	public function __construct(protected string $dtoClass)
	{

	}

	public function getRelations(): array
	{
		return $this->relations;
	}

	public function getRelation(string $relationName): ?Relation
	{
		return $this->relations[$relationName] ?? null;
	}

	public function addRelation(Relation $relation): void
	{
		$this->relations[$relation->getName()] = $relation;
	}

	/**
	 * @param HttpRequest $httpRequest
	 * @param string $dtoClass
	 * @return Request
	 * @throws InvalidJsonException
	 * @throws RequiredFieldInRequestException
	 * @throws SystemException
	 * @see Dto
	 */
	public static function create(HttpRequest $httpRequest, string $dtoClass): self
	{
		$request = new static($dtoClass);

		// input data
		try
		{
			$httpRequest->decodeJsonStrict();
			$input = $httpRequest->getJsonList();
		}
		catch (ArgumentException)
		{
			throw new InvalidJsonException();
		}

		// properties of request
		$reflection = new ReflectionClass($request);
		$properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

		// set input data into the request
		foreach ($properties as $property)
		{
			if (!$property->getType() instanceof ReflectionNamedType)
			{
				continue;
			}

			$propertyName = $property->getName();
			$propertyType = $property->getType()->getName();
			$isOptional = $property->getType()->allowsNull();

			if (!isset($input[$propertyName]))
			{
				if (!$isOptional)
				{
					// field not found, but it is required
					throw new RequiredFieldInRequestException($propertyName);
				}

				continue;
			}

			if (is_subclass_of($propertyType, Structure::class))
			{
				// validate with Dto
				$value = $propertyType::create($input[$propertyName], $dtoClass, $request);
			}
			else
			{
				$value = $input[$propertyName];
			}

			$request->{$propertyName} = $value;
		}

		return $request;
	}

	public function getDtoClass(): string
	{
		return $this->dtoClass;
	}

	public function getOrmEntityClass(): ?string
	{
		return $this->ormEntityClass;
	}
}
