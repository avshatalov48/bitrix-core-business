<?php

namespace Bitrix\Bizproc\Calc\Libs;

use Bitrix\Bizproc\Calc\Arguments;
use Bitrix\Main\Localization\Loc;

class MathLib extends BaseLib
{
	function getFunctions(): array
	{
		return [
			'abs' => [
				'args' => true,
				'func' => 'callAbs',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_ABS_DESCRIPTION'),
			],
			'intval' => [
				'args' => true,
				'func' => 'callIntval',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_INTVAL_DESCRIPTION'),
			],
			'floatval' => [
				'args' => true,
				'func' => 'callFloatval',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_FLOATVAL_DESCRIPTION'),
			],
			'min' => [
				'args' => true,
				'func' => 'callMin',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_MIN_DESCRIPTION'),
			],
			'max' => [
				'args' => true,
				'func' => 'callMax',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_MAX_DESCRIPTION'),
			],
			'rand' => [
				'args' => true,
				'func' => 'callRand',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_RAND_DESCRIPTION'),
			],
			'round' => [
				'args' => true,
				'func' => 'callRound',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_ROUND_DESCRIPTION'),
			],
			'ceil' => [
				'args' => true,
				'func' => 'callCeil',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_CEIL_DESCRIPTION'),
			],
			'floor' => [
				'args' => true,
				'func' => 'callFloor',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_FLOOR_DESCRIPTION'),
			],
		];
	}

	public function callAbs(Arguments $args)
	{
		return abs((float)$args->getFirst());
	}

	public function callRound(Arguments $args)
	{
		$val = (float)$args->getFirst();
		$precision = (int)$args->getSecond();

		return round($val, $precision);
	}

	public function callCeil(Arguments $args)
	{
		return ceil((double)$args->getFirst());
	}

	public function callFloor(Arguments $args)
	{
		return floor((double)$args->getFirst());
	}

	public function callMin(Arguments $args)
	{
		$array = array_filter($args->getFlatArray(), fn($item) => is_numeric($item));
		$array = array_map(
			fn($item) => (float)$item,
			$array
		);

		return $array ? min($array) : false;
	}

	public function callMax(Arguments $args)
	{
		$array = array_filter($args->getFlatArray(), fn($item) => is_numeric($item));
		$array = array_map(
			fn($item) => (float)$item,
			$array
		);

		return $array ? max($array) : false;
	}

	public function callRand(Arguments $args)
	{
		$min = (int)$args->getFirst();
		$max = (int)$args->getSecond();

		if (!$max || !is_finite($max))
		{
			$max = mt_getrandmax();
		}

		return mt_rand($min, $max);
	}

	public function callIntval(Arguments $args)
	{
		return intval($args->getFirst());
	}

	public function callFloatval(Arguments $args)
	{
		return floatval($args->getFirst());
	}
}
