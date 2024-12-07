<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper;

use Bitrix\Socialnetwork\Control\Command\AbstractCommand;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\MapInterface;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\MapMany;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\MapOne;
use ReflectionClass;
use ReflectionProperty;

class Mapper
{
	public function toArray(AbstractCommand $command): array
	{
		$data = [];
		$reflection = new ReflectionClass($command);
		$properties = $reflection->getProperties();

		foreach ($properties as $property)
		{
			if (!$property->isInitialized($command))
			{
				continue;
			}

			$value = $property->getValue($command);

			$fieldMapper = $this->getFieldMapper($property);

			$fieldMapper?->map($data, $value);
		}

		return $data;
	}

	protected function getFieldMapper(ReflectionProperty $property): ?MapInterface
	{
		$mapOne = null;

		$attributes = $property->getAttributes();

		foreach ($attributes as $attributeReflection)
		{
			$attribute = $attributeReflection->newInstance();

			if ($attribute instanceof MapMany)
			{
				return $attribute;
			}

			if ($attribute instanceof MapOne)
			{
				$mapOne = $attribute;
			}
		}

		return $mapOne;
	}
}