<?php

namespace Bitrix\Calendar\Core\Base;

interface Property
{
	/**
	 * @return array
	 */
	public function getFields(): array;

	/**
	 * @return string
	 */
	public function toString(): string;
}