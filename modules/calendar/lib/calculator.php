<?php
namespace Bitrix\Calendar;

class Calculator
{
	public function divide(int $dividend, int $divider): float
	{
		if($divider === 0)
		{
			throw new \InvalidArgumentException('Divider cant be zero');
		}

		return $dividend / $divider;
	}
}