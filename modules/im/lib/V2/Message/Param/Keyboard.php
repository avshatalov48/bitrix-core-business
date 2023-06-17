<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im;
use Bitrix\Im\V2\Message\Param;
use Bitrix\Main\ArgumentException;

class Keyboard extends Param
{
	protected ?Im\Bot\Keyboard $keyboard;

	/**
	 * @param Im\Bot\Keyboard $value
	 * @return static
	 */
	public function setValue($value): self
	{
		if ($value instanceof Im\Bot\Keyboard)
		{
			$this->keyboard = $value;
		}
		elseif (!empty($value))
		{
			$this->keyboard = Im\Bot\Keyboard::getKeyboardByJson($value);
		}

		if ($this->keyboard)
		{
			$this->value = $this->keyboard->getArray();
			$this->jsonValue = $this->keyboard->getJson();
		}

		return $this;
	}

	/**
	 * @return array|null
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function saveValueFilter($value)
	{
		return '';
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function loadValueFilter($value)
	{
		if (!empty($value))
		{
			$value = Im\Text::decodeEmoji($value);
		}
		else
		{
			$value = null;
		}

		return $value;
	}


	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function saveJsonFilter($value)
	{
		return $this->jsonValue;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function loadJsonFilter($value)
	{
		if (!empty($value))
		{
			try
			{
				$this->value = \Bitrix\Main\Web\Json::decode($value);
			}
			catch (ArgumentException $ext)
			{}
		}
		else
		{
			$value = null;
		}

		return $value;
	}

	/**
	 * @return array|null
	 */
	public function toRestFormat(): ?array
	{
		return $this->getValue();
	}

	/**
	 * @return array|null
	 */
	public function toPullFormat(): ?array
	{
		return $this->getValue();
	}
}
