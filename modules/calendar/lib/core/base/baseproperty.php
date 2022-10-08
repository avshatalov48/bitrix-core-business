<?php

namespace Bitrix\Calendar\Core\Base;

abstract class BaseProperty implements Property
{
	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->toString();
	}

	/**
	 * @return array
	 */
	abstract public function getFields(): array;

	/**
	 * @return string
	 */
	abstract public function toString(): string;
}