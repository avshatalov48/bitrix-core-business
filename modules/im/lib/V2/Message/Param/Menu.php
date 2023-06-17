<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im;
use Bitrix\Im\V2\Message\Param;
use Bitrix\Main\ArgumentException;

class Menu extends Param
{
	protected ?Im\Bot\ContextMenu $menu;

	/**
	 * @param Im\Bot\ContextMenu $value
	 * @return static
	 */
	public function setValue($value): self
	{
		if ($value instanceof Im\Bot\ContextMenu)
		{
			$this->menu = $value;
		}
		elseif (!empty($value))
		{
			$this->menu = Im\Bot\ContextMenu::getByJson($value);
		}

		if ($this->menu)
		{
			$this->value = $this->menu->getArray();
			$this->jsonValue = $this->menu->getJson();
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
