<?php

namespace Bitrix\Bizproc\Calc\Libs;

use Bitrix\Bizproc\Calc\Arguments;
use Bitrix\Main\Localization\Loc;

class LogicLib extends BaseLib
{
	public function getFunctions(): array
	{
		return [
			'true' => [
				'args' => false,
				'func' => 'callTrue',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_TRUE_DESCRIPTION'),
			],
			'false' => [
				'args' => false,
				'func' => 'callFalse',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_FALSE_DESCRIPTION'),
			],
			'if' => [
				'args' => true,
				'func' => 'callIf',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_IF_DESCRIPTION'),
			],
			'and' => [
				'args' => true,
				'func' => 'callAnd',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_AND_DESCRIPTION'),
			],
			'not' => [
				'args' => true,
				'func' => 'callNot',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_NOT_DESCRIPTION'),
			],
			'or' => [
				'args' => true,
				'func' => 'callOr',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_OR_DESCRIPTION'),
			],
		];
	}

	public function callTrue()
	{
		return true;
	}

	public function callFalse()
	{
		return false;
	}

	public function callIf(Arguments $args)
	{
		$expression = (boolean)$args->getFirst();
		$ifTrue = $args->getSecond();
		$ifFalse = $args->getThird();

		return $expression ? $ifTrue : $ifFalse;
	}

	public function callNot(Arguments $args)
	{
		return !$args->getFirst();
	}

	public function callAnd(Arguments $args)
	{
		$array = $args->getFlatArray();

		return count(array_filter($array)) === count($array);
	}

	public function callOr(Arguments $args)
	{
		$array = $args->getFlatArray();

		return count(array_filter($array)) > 0;
	}
}
