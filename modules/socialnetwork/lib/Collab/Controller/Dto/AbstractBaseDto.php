<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller\Dto;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Socialnetwork\Collab\Controller\Meta\MetaTypeConverter;
use Bitrix\Socialnetwork\Collab\Controller\Trait\ConvertRequestToArrayTrait;
use ReflectionClass;
use ReflectionProperty;

abstract class AbstractBaseDto implements Arrayable
{
	use ConvertRequestToArrayTrait;

	public static function createFromRequest(mixed $request): static
	{
		$dto = [];

		$requestData = static::convertRequest($request);

		$reflection = new ReflectionClass(static::class);

		foreach ($requestData as $key => $value)
		{
			if ($reflection->hasProperty($key))
			{
				$dto[$key] = static::cast($key, $value);
			}
		}

		return new static(...$dto);
	}

	protected static function cast(string $key, mixed $value): mixed
	{
		$reflection = new ReflectionProperty(static::class, $key);
		$type = $reflection->getType()?->getName();
		if ($type === null)
		{
			return $value;
		}

		$metaType = MetaTypeConverter::getMetaType(static::class, $key);
		if ($metaType !== null)
		{
			return MetaTypeConverter::convert($metaType, $value);
		}

		return match ($type)
		{
			'int' => (int)$value,
			'float' => (float)$value,
			'string' => (string)$value,
			'array' => (array)$value,
			'bool' => (bool)$value,
			default => $value,
		};
	}

	public function toArray(): array
	{
		$data = [];
		foreach ($this as $key => $value)
		{
			if ($value !== null)
			{
				$data[$key] = $value;
			}
		}

		return $data;
	}
}