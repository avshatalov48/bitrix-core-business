<?php

namespace Bitrix\Calendar\Core\Base;

abstract class PropertyCollection extends Collection implements Property
{
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * @return array[]
	 */
	public function getFields(): array
	{
		return [
			'collection' => $this->collection,
		];
	}

	/**
	 * @param string $separator
	 * @return string
	 */
	public function toString(string $separator = ', '): string
	{
		return implode($separator, $this->collection);
	}
}