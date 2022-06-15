<?php

namespace Bitrix\Catalog\v2\Fields\TypeCasters;

use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

// ToDo check it with real world on product/sku editing

/**
 * Class MapTypeCaster
 *
 * @package Bitrix\Catalog\v2\Fields\TypeCasters
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class MapTypeCaster implements TypeCasterContract
{
	public const NOTHING = 'Nothing';

	public const STRING = 'String';
	public const NULLABLE_STRING = 'NullableString';

	public const INT = 'Int';
	public const NULLABLE_INT = 'NullableInt';
	public const MULTI_INT = 'MultiInt';
	public const NULLABLE_MULTI_INT = 'NullableMultiInt';

	public const FLOAT = 'Float';
	public const NULLABLE_FLOAT = 'NullableFloat';

	public const BOOLEAN = 'Boolean';
	public const Y_OR_N = 'YesOrNo';
	public const Y_OR_N_OR_D = 'YesOrNoOrDefault';

	public const DATE = 'Date';
	public const DATETIME = 'DateTime';

	private $fieldMap = [];

	public function __construct(array $fieldMap = null)
	{
		if (!empty($fieldMap))
		{
			$this->fieldMap = $fieldMap;
		}
	}

	private function castToNothing($value)
	{
		return $value;
	}

	private function castToString($value): string
	{
		return (string)$value;
	}

	private function castToNullableString($value): ?string
	{
		if ($value !== null)
		{
			$value = $this->castToString($value);
		}

		return $value;
	}

	private function castToInt($value): int
	{
		return (int)$value;
	}

	private function castToNullableInt($value): ?int
	{
		if ($value !== null)
		{
			$value = $this->castToInt($value);
		}

		return $value;
	}

	private function castToMultiInt($value): array
	{
		$result = [];
		if (!is_array($value))
		{
			$value = [$value];
		}
		foreach ($value as $item)
		{
			if ($item === '' || $item === null)
			{
				continue;
			}
			$result[] = (int)$item;
		}

		return $result;
	}

	private function castToNullableMultiInt($value): ?array
	{
		if ($value !== null)
		{
			$value = $this->castToMultiInt($value);
			if (empty($value))
			{
				$value = [];
			}
		}

		return $value;
	}

	private function castToFloat($value): float
	{
		return (float)$value;
	}

	private function castToNullableFloat($value): ?float
	{
		if ($value !== null)
		{
			$value = $this->castToFloat($value);
		}

		return $value;
	}

	private function castToYesOrNo($value): string
	{
		if (is_bool($value))
		{
			return $value ? 'Y' : 'N';
		}

		return (string)$value === 'Y' ? 'Y' : 'N';
	}

	private function castToYesOrNoOrDefault($value): string
	{
		if (is_bool($value))
		{
			return $value ? 'Y' : 'N';
		}

		$value = (string)$value;

		if ($value !== 'Y' && $value !== 'D')
		{
			$value = 'N';
		}

		return $value;
	}

	private function castToBoolean($value): bool
	{
		return (bool)$value;
	}

	private function castToDate($value): ?Date
	{
		if ($value !== null && $value !== '')
		{
			return new Date($value);
		}

		return null;
	}

	private function castToDateTime($value): ?DateTime
	{
		if ($value !== null && $value !== '')
		{
			return new DateTime($value);
		}

		return null;
	}

	public function cast($name, $value)
	{
		if ($this->has($name))
		{
			if (is_string($this->fieldMap[$name]))
			{
				$castMethod = "castTo{$this->fieldMap[$name]}";
				if (is_callable([$this, $castMethod]))
				{
					return $this->$castMethod($value);
				}
			}

			if (is_callable($this->fieldMap[$name]))
			{
				return $this->fieldMap[$name]($value);
			}

			throw new NotSupportedException(sprintf(
				'Could not find casting {%s} for field {%s}.',
				$this->fieldMap[$name], $name
			));
		}

		return $value;
	}

	public function has($name): bool
	{
		return isset($this->fieldMap[$name]);
	}
}