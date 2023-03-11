<?php

namespace Bitrix\Bizproc\Calc\Libs;

use Bitrix\Main;
use Bitrix\Bizproc\Calc\Arguments;
use Bitrix\Main\Localization\Loc;

class StringLib extends BaseLib
{
	function getFunctions(): array
	{
		return [
			'numberformat' => [
				'args' => true,
				'func' => 'callNumberFormat',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_NUMBER_FORMAT_DESCRIPTION'),
			],
			'substr' => [
				'args' => true,
				'func' => 'callSubstr',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_SUBSTR_DESCRIPTION'),
			],
			'strpos' => [
				'args' => true,
				'func' => 'callStrpos',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_STRPOS_DESCRIPTION'),
			],
			'strlen' => [
				'args' => true,
				'func' => 'callStrlen',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_STRLEN_DESCRIPTION'),
			],
			'randstring' => [
				'args' => true,
				'func' => 'callRandString',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_RANDSTRING_DESCRIPTION'),
			],
			'strtolower' => [
				'args' => true,
				'func' => 'callStrtolower',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_STRTOLOWER_DESCRIPTION'),
			],
			'strtoupper' => [
				'args' => true,
				'func' => 'callStrtoupper',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_STRTOUPPER_DESCRIPTION'),
			],
			'ucwords' => [
				'args' => true,
				'func' => 'callUcwords',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_UCWORDS_DESCRIPTION'),
			],
			'ucfirst' => [
				'args' => true,
				'func' => 'callUcfirst',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_UCFIRST_DESCRIPTION'),
			],
			'trim' => [
				'args' => true,
				'func' => 'callTrim',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_TRIM_DESCRIPTION'),
			],
			'urlencode' => [
				'args' => true,
				'func' => 'callUrlencode',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_URLENCODE_DESCRIPTION'),
			],
		];
	}

	public function callNumberFormat(Arguments $args)
	{
		$number = (float)$args->getFirst();
		$decimals = (int)($args->getSecond() ?: 0);
		$decPoint = $args->getThird();

		if ($decPoint === null)
		{
			$decPoint = '.';
		}
		$decPoint = (string)$decPoint;

		$thousandsSeparator = $args->getArg(3);
		if ($thousandsSeparator === null)
		{
			$thousandsSeparator = ',';
		}
		$thousandsSeparator = (string)$thousandsSeparator;

		return number_format($number, $decimals, $decPoint, $thousandsSeparator);
	}

	public function callRandString(Arguments $args)
	{
		$len = (int)$args->getFirst();

		return Main\Security\Random::getString($len, true);
	}

	public function callSubstr(Arguments $args)
	{
		$str = $args->getFirst();
		$pos = (int)$args->getSecond();
		$len = (int)$args->getThird();

		if (is_array($str))
		{
			return null;
		}

		if (!$str)
		{
			return '';
		}

		if ($len)
		{
			return mb_substr($str, $pos, $len);
		}

		return mb_substr($str, $pos);
	}

	public function callStrpos(Arguments $args)
	{
		$haystack = (string)$args->getFirst();

		if (empty($haystack))
		{
			return false;
		}

		$maxOffset = mb_strlen($haystack);
		$minOffset = -1 * $maxOffset;

		$needle = (string)$args->getSecond();
		$offset = max($minOffset, min($maxOffset, (int)$args->getThird()));

		return mb_strpos($haystack, $needle, $offset);
	}

	public function callStrlen(Arguments $args)
	{
		$str = $args->getFirst();

		if (!is_scalar($str))
		{
			return null;
		}

		$str = (string)$str;

		return mb_strlen($str);
	}

	public function callUrlencode(Arguments $args)
	{
		$str = $args->getFirst();

		if (!is_scalar($str))
		{
			if (is_array($str))
			{
				$str = implode(", ", \CBPHelper::MakeArrayFlat($str));
			}
			else
			{
				return null;
			}
		}

		$str = (string)$str;

		return urlencode($str);
	}

	public function callStrtolower(Arguments $args)
	{
		$str = $args->getFirst();

		if (!is_scalar($str))
		{
			return null;
		}

		return mb_strtolower((string)$str);
	}

	public function callStrtoupper(Arguments $args)
	{
		$str = $args->getFirst();

		if (!is_scalar($str))
		{
			return null;
		}

		return mb_strtoupper((string)$str);
	}

	public function callUcwords(Arguments $args)
	{
		$str = $args->getFirst();

		if (!is_scalar($str))
		{
			return null;
		}

		return mb_convert_case((string)$str, MB_CASE_TITLE);
	}

	public function callUcfirst(Arguments $args)
	{
		$str = $args->getFirst();

		if (!is_scalar($str))
		{
			return null;
		}

		return $this->mbUcFirst((string)$str);
	}

	private function mbUcFirst($str)
	{
		$len = mb_strlen($str);
		$firstChar = mb_substr($str, 0, 1);
		$otherChars = mb_substr($str, 1, $len - 1);

		return mb_strtoupper($firstChar) . $otherChars;
	}

	public function callTrim(Arguments $args)
	{
		$array = $args->getFlatArray();

		$result = [];
		foreach ($array as $str)
		{
			if (is_scalar($str) || (is_object($str) && method_exists($str, '__toString')))
			{
				$result[] = trim((string)$str);
			}
		}

		return count($result) > 1 ? $result : ($result[0] ?? '');
	}
}
