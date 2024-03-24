<?php

namespace Bitrix\Bizproc;

use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Exception\ValidationException;

class Validator
{
	const TYPE_ARRAY = 'array';
	const TYPE_NUMERIC = 'numeric';
	const TYPE_STRING = 'string';

	protected array $dirtyValues;
	protected array $pureValues = [];

	public function __construct(array $values)
	{
		Loc::loadMessages(__FILE__);
		$this->dirtyValues = $values;
	}

	public function getPureValues(): array
	{
		return $this->pureValues;
	}

	protected function validate(string $type, $value): bool
	{
		if ($type === self::TYPE_ARRAY)
		{
			return is_array($value);
		}
		elseif ($type === self::TYPE_NUMERIC)
		{
			return is_numeric($value);
		}
		else
		{
			return is_string($value);
		}
	}

	public function validateArray(string $name, string $keyType = self::TYPE_NUMERIC,
		string $valueType = self::TYPE_STRING): self
	{
		if (!isset($this->dirtyValues[$name]))
		{
			return $this;
		}

		foreach ($this->dirtyValues[$name] as $key => $value)
		{
			if (!$this->validate($keyType, $key))
			{
				throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_ARRAY_KEY",
					["#name#" => $name, '#val#' => $key]));
			}
			if (!$this->validate($valueType, $value))
			{
				throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_ARRAY_VAL",
					["#name#" => $name, '#val#' => $key]));
			}
		}

		if (!is_array($this->dirtyValues[$name]))
		{
			throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_IS_ARRAY", ["#name#" => $name]));
		}

		$this->setPureValue($name);
		return $this;
	}

	public function validateNumeric(string $name, int $min = 0, int $max = 0): self
	{
		if (!isset($this->dirtyValues[$name]))
		{
			return $this;
		}

		if (!is_numeric($this->dirtyValues[$name]))
		{
			throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_IS_NUM", ["#name#" => $name]));
		}

		$val = (int)$this->dirtyValues[$name];

		if ($min && $val < $min)
		{
			throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_NUM_MIN",
				["#name#" => $name, '#min#' => $min]));
		}

		if ($max && $val > $max)
		{
			throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_NUM_MAX",
				["#name#" => $name, '#max#' => $max]));
		}

		$this->setPureValue($name);
		return $this;
	}

	public function validateString(string $name, int $minLen = 0, int $maxLen = 0): self
	{
		if (!isset($this->dirtyValues[$name]))
		{
			return $this;
		}

		$val = $this->dirtyValues[$name];

		if (!is_string($val))
		{
			throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_IS_STRING", ["#name#" => $name]));
		}

		if ($minLen && mb_strlen($val) < $minLen)
		{
			throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_STRING_MIN",
				["#name#" => $name, '#min#' => $minLen]));
		}

		if ($maxLen && mb_strlen($val) > $maxLen)
		{
			throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_STRING_MAX",
				["#name#" => $name, '#max#' => $maxLen]));
		}

		$this->setPureValue($name);
		return $this;
	}

	public function validateRegExp(string $name, string $pattern): self
	{
		if (!isset($this->dirtyValues[$name]))
		{
			return $this;
		}

		if (!preg_match($pattern, $this->dirtyValues[$name]))
		{
			throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_REGEXP",
				["#name#" => $name, "#pattern#" => $pattern]));
		}

		$this->setPureValue($name);
		return $this;
	}

	public function validateEnum(string $name, array $enum): self
	{
		if (!isset($this->dirtyValues[$name]))
		{
			return $this;
		}

		if (!in_array($this->dirtyValues[$name], $enum))
		{
			throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_ENUM",
				["#name#" => $name, "#enum#" => implode('", "', $enum)]));
		}

		$this->setPureValue($name);
		return $this;
	}

	public function validateRequire(string $name): self
	{
		if (!isset($this->dirtyValues[$name]))
		{
			throw new ValidationException(Loc::getMessage("BIZPROC_VALIDATOR_REQUIRE", ["#name#" => $name]));
		}

		$this->setPureValue($name);
		return $this;
	}

	public function setDefault(string $name, $value): self
	{
		if (!isset($this->dirtyValues[$name]) && !isset($this->pureValues[$name]))
		{
			$this->pureValues[$name] = $value;
		}

		return $this;
	}

	public function setPureValue($name): self
	{
		if (isset($this->dirtyValues[$name]) && !isset($this->pureValues[$name]))
		{
			$this->pureValues[$name] = $this->dirtyValues[$name];
		}

		return $this;
	}

	public function convertToInt(string $name): self
	{
		if (isset($this->dirtyValues[$name]))
		{
			$this->dirtyValues[$name] = (int)$this->dirtyValues[$name];
		}

		if (isset($this->pureValues[$name]))
		{
			$this->pureValues[$name] = (int)$this->pureValues[$name];
		}

		return $this;
	}
}