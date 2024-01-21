<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im;
use Bitrix\Im\V2\Message\Param;
use Bitrix\Im\V2\Result;
use Bitrix\Main\ArgumentException;

class Keyboard extends Param
{
	protected ?Im\Bot\Keyboard $keyboard;
	protected bool $isValid = true;

	/**
	 * @param Im\Bot\Keyboard $value
	 * @return static
	 */
	public function setValue($value): self
	{
		if ($value === null || $value === $this->getDefaultValue())
		{
			return $this->unsetValue();
		}

		if ($value instanceof Im\Bot\Keyboard)
		{
			$this->keyboard = $value;
		}
		elseif (!empty($value))
		{
			$this->keyboard = Im\Bot\Keyboard::getKeyboardByJson($value);
			$this->isValid = $this->keyboard !== null;
		}

		if (isset($this->keyboard))
		{
			$this->value = $this->keyboard->getArray();
			$this->jsonValue = $this->keyboard->getJson();
		}

		return $this;
	}

	/**
	 * @return array|string
	 */
	public function getValue()
	{
		return $this->value ?? $this->getDefaultValue();
	}

	public function getDefaultValue()
	{
		return 'N';
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
	public function toRestFormat()
	{
		return $this->getValue();
	}

	/**
	 * @return array|null
	 */
	public function toPullFormat()
	{
		return $this->getValue();
	}

	/**
	 * @return Result
	 */
	public function isValid(): Result
	{
		$result = new Result();

		if ($this->isValid && (!isset($this->keyboard) || $this->keyboard->IsAllowSize()))
		{
			return $result;
		}

		return $result->addError(new ParamError(ParamError::KEYBOARD_ERROR));
	}
}
