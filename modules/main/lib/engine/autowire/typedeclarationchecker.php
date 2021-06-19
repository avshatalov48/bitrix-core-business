<?php

namespace Bitrix\Main\Engine\AutoWire;

final class TypeDeclarationChecker
{
	/** @var \ReflectionNamedType */
	private $type;
	/** @var mixed */
	private $desiredValue;

	public function __construct(\ReflectionNamedType $type, $desiredValue)
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

		if ($this->type->getName() === gettype($this->desiredValue))
		{
			return true;
		}

		//gettype returns 'double' when type is float :)
		if (is_float($this->desiredValue) && $this->type->getName() === 'float')
		{
			return true;
		}

		if ($this->desiredValue === null && $this->type->allowsNull())
		{
			return true;
		}

		switch ($this->type->getName())
		{
			case 'bool':
				return is_scalar($this->desiredValue);
			case 'float':
			case 'int':
				return is_numeric($this->desiredValue);
			case 'string':
				return is_string($this->desiredValue);
		}

		return false;
	}
}