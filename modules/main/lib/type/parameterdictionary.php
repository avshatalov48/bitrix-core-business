<?php

namespace Bitrix\Main\Type;

use Bitrix\Main\NotSupportedException;

class ParameterDictionary extends Dictionary
{
	/**
	 * @var array
	 */
	protected $rawValues = null;

	protected function setValuesNoDemand(array $values)
	{
		if ($this->rawValues === null)
		{
			$this->rawValues = $this->values;
		}
		$this->values = $values;
	}

	/**
	 * Returns original value of any variable by its name. Null if variable is not set.
	 *
	 * @param string $name
	 * @return string | null
	 */
	public function getRaw($name)
	{
		if ($this->rawValues === null)
		{
			if (isset($this->values[$name]) || array_key_exists($name, $this->values))
			{
				return $this->values[$name];
			}
		}
		else
		{
			if (isset($this->rawValues[$name]) || array_key_exists($name, $this->rawValues))
			{
				return $this->rawValues[$name];
			}
		}

		return null;
	}

	public function toArrayRaw()
	{
		return $this->rawValues;
	}

	/**
	 * Offset to set
	 */
	public function offsetSet($offset, $value)
	{
		throw new NotSupportedException("Can not set readonly values.");
	}

	/**
	 * Offset to unset
	 */
	public function offsetUnset($offset): void
	{
		throw new NotSupportedException("Can not unset readonly values.");
	}

	public function setValues($values)
	{
		throw new NotSupportedException("Can not set readonly values.");
	}
}
