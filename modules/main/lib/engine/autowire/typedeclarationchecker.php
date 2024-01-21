<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\AutoWire;

final class TypeDeclarationChecker
{
	private \ReflectionNamedType $type;
	private mixed $desiredValue;

	public function __construct(\ReflectionNamedType $type, mixed $desiredValue)
	{
		$this->type = $type;
		$this->desiredValue = $desiredValue;
	}

	public function isSatisfied(): bool
	{
		if (!$this->type->isBuiltin())
		{
			return false;
		}

		if ($this->type->getName() === \gettype($this->desiredValue))
		{
			return true;
		}

		//gettype returns 'double' when type is float :)
		if (\is_float($this->desiredValue) && $this->type->getName() === 'float')
		{
			return true;
		}

		if ($this->desiredValue === null && $this->type->allowsNull())
		{
			return true;
		}

		return match ($this->type->getName())
		{
			'bool' => \is_scalar($this->desiredValue),
			'float', 'int' => \is_numeric($this->desiredValue),
			'string' => \is_string($this->desiredValue),
			default => false,
		};
	}

	public function isArray(): bool
	{
		return $this->type->getName() === 'array';
	}
}