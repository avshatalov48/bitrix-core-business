<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im\V2\Message\Param;
use Bitrix\Main;

class DateTime extends Param
{
	/**
	 * @param mixed $value
	 * @return static
	 */
	public function setValue($value): self
	{
		if ($value instanceof Main\Type\DateTime)
		{
			$this->value = $value;
		}
		elseif ($value instanceof \DateTime)
		{
			$this->value = Main\Type\DateTime::createFromPhp($value);
		}
		else
		{
			$this->value = Main\Type\DateTime::createFromTimestamp((int)$value);
		}

		return $this;
	}

	/**
	 * @return Main\Type\DateTime|null
	 */
	public function getValue(): ?Main\Type\DateTime
	{
		if (!empty($this->value))
		{
			if ($this->value instanceof Main\Type\DateTime)
			{
				return $this->value;
			}
			else
			{
				return Main\Type\DateTime::createFromTimestamp((int)$this->value);
			}
		}

		return null;
	}

	/**
	 * @return string|null
	 */
	public function toRestFormat(): ?string
	{
		if ($this->getValue() instanceof Main\Type\DateTime)
		{
			return $this->getValue()->format('c');
		}

		return null;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function saveValueFilter($value)
	{
		if ($value instanceof Main\Type\DateTime)
		{
			return $value->getTimestamp();
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function loadValueFilter($value)
	{
		if (!($value instanceof Main\Type\DateTime))
		{
			return Main\Type\DateTime::createFromTimestamp((int)$value);
		}

		return $value;
	}
}
