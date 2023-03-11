<?php

namespace Bitrix\Bizproc\Calc\Libs;

use Bitrix\Bizproc\Calc\Arguments;
use Bitrix\Main\Localization\Loc;

class ArrayLib extends BaseLib
{
	function getFunctions(): array
	{
		return [
			'implode' => [
				'args' => true,
				'func' => 'callImplode',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_IMPLODE_DESCRIPTION'),
			],
			'explode' => [
				'args' => true,
				'func' => 'callExplode',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_EXPLODE_DESCRIPTION'),
			],
			'merge' => [
				'args' => true,
				'func' => 'callMerge',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_MERGE_DESCRIPTION'),
			],
			'shuffle' => [
				'args' => true,
				'func' => 'callShuffle',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_SHUFFLE_DESCRIPTION'),
			],
			'firstvalue' => [
				'args' => true,
				'func' => 'callFirstValue',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_FIRSTVALUE_DESCRIPTION'),
			],
			'swirl' => [
				'args' => true,
				'func' => 'callSwirl',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_SWIRL_DESCRIPTION'),
			],
		];
	}

	public function callImplode(Arguments $args)
	{
		$glue = (string)$args->getFirst();
		$pieces = \CBPHelper::makeArrayFlat($args->getSecond());

		if (!$pieces)
		{
			return '';
		}

		return implode($glue, $pieces);
	}

	public function callExplode(Arguments $args)
	{
		$delimiter = $args->getFirst();
		$str = $args->getSecond();

		if (is_array($str))
		{
			$str = reset($str);
		}

		if (is_array($delimiter))
		{
			$delimiter = reset($delimiter);
		}

		if (empty($delimiter) || !is_scalar($str) || !is_scalar($delimiter))
		{
			return null;
		}

		$str = (string)$str;

		return explode($delimiter, $str);
	}

	public function callMerge(Arguments $args)
	{
		$arrays = $args->getArray();

		foreach ($arrays as &$a)
		{
			$a = is_object($a) ? [$a] : (array)$a;
		}

		return call_user_func_array('array_merge', $arrays);
	}

	public function callShuffle(Arguments $args)
	{
		$array = array_filter($args->getFlatArray(), fn ($arg) => $arg !== null);
		shuffle($array);

		return $array;
	}

	public function callFirstValue(Arguments $args)
	{
		$array = $args->getFlatArray();

		return $array[0] ?? null;
	}

	public function callSwirl(Arguments $args)
	{
		$array = array_filter($args->getFlatArray(), fn ($arg) => $arg !== null);

		if (count($array) <= 1)
		{
			return $array;
		}

		return array_merge(array_slice($array, 1), [$array[0]]);
	}
}
