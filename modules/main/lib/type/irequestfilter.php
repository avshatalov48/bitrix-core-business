<?php
namespace Bitrix\Main\Type;

interface IRequestFilter
{
	/**
	 * @param array $values
	 * @return array
	 */
	function filter(array $values);
}
