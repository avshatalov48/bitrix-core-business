<?php

namespace Bitrix\Rest\V3\Dto;

use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Rest\V3\Data\OrmRepository;
use Bitrix\Rest\V3\Exceptions\Internal\UnknownDtoPropertyInternalException;
use Bitrix\Rest\V3\Structures\UserFieldsTrait;

abstract class Dto implements Arrayable
{
	use UserFieldsTrait;

	public function __set(string $name, $value): void
	{
		if (str_starts_with($name, 'UF_'))
		{
			$this->userFields[$name] = $value;

			return;
		}
		throw new UnknownDtoPropertyInternalException($name, static::class);
	}

	public function __get(string $name): mixed
	{
		if (str_starts_with($name, 'UF_'))
		{
			return $this->userFields[$name] ?? null;
		}

		return $this->$name;
	}

	public function toArray(bool $rawData = false): array
	{
		$values = [];

		foreach (PropertyHelper::getProperties($this) as $property)
		{
			if ($property->isInitialized($this))
			{
				$values[$property->getName()] = $this->{$property->getName()};
			}
		}

		foreach ($this->userFields as $key => $value)
		{
			$values[$key] = $value;
		}

		if ($rawData)
		{
			return $values;
		}

		foreach ($values as $propertyName => $value)
		{
			if ($value instanceof DateTime)
			{
				$values[$propertyName] = $value->format(DATE_ATOM);
			}
			elseif ($value instanceof Date)
			{
				$values[$propertyName] = $value->format('Y-m-d');
			}
			else
			{
				$values[$propertyName] = $value;
			}
		}
		return $values;
	}
}
