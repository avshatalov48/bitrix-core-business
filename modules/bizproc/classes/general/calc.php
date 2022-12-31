<?php

use Bitrix\Main;
use Bitrix\Bizproc;

class CBPCalc
{
	/** @var CBPActivity $activity */
	private $activity;
	private $arErrorsList = [];

	private static $weekHolidays;
	private static $yearHolidays;
	private static $startWorkDay;
	private static $endWorkDay;
	private static $yearWorkdays;

	// Operation priority
	private $arPriority = [
		'('  => 0,   ')'  => 1,     ';'   => 2,   '=' => 3,     '<' => 3,   '>' => 3,
		'<=' => 3,   '>=' => 3,     '<>'  => 3,   '&' => 4,     '+' => 5,   '-' => 5,
		'*'  => 6,   '/'  => 6,     '^'   => 7,   '%' => 8,     '-m' => 9,  '+m' => 9,
		' '  => 10,  ':'  => 11,    'f'   => 12,
	];

	// Allowable functions
	private $arAvailableFunctions = [
		'abs' => ['args' => true, 'func' => 'FunctionAbs'],
		'and' => ['args' => true, 'func' => 'FunctionAnd'],
		'date' => ['args' => true, 'func' => 'FunctionDate'],
		'dateadd' => ['args' => true, 'func' => 'FunctionDateAdd'],
		'datediff' => ['args' => true, 'func' => 'FunctionDateDiff'],
		'false' => ['args' => false, 'func' => 'FunctionFalse'],
		'if' => ['args' => true, 'func' => 'FunctionIf'],
		'intval' => ['args' => true, 'func' => 'FunctionIntval'],
		'floatval' => ['args' => true, 'func' => 'FunctionFloatval'],
		'numberformat' => ['args' => true, 'func' => 'FunctionNumberFormat'],
		'min' => ['args' => true, 'func' => 'FunctionMin'],
		'max' => ['args' => true, 'func' => 'FunctionMax'],
		'rand' => ['args' => true, 'func' => 'FunctionRand'],
		'round' => ['args' => true, 'func' => 'FunctionRound'],
		'ceil' => ['args' => true, 'func' => 'FunctionCeil'],
		'floor' => ['args' => true, 'func' => 'FunctionFloor'],
		'not' => ['args' => true, 'func' => 'FunctionNot'],
		'or' => ['args' => true, 'func' => 'FunctionOr'],
		'substr' => ['args' => true, 'func' => 'FunctionSubstr'],
		'strpos' => ['args' => true, 'func' => 'FunctionStrpos'],
		'strlen' => ['args' => true, 'func' => 'FunctionStrlen'],
		'implode' => ['args' => true, 'func' => 'FunctionImplode'],
		'explode' => ['args' => true, 'func' => 'FunctionExplode'],
		'randstring' => ['args' => true, 'func' => 'FunctionRandString'],
		'true' => ['args' => false, 'func' => 'FunctionTrue'],
		'convert' => ['args' => true, 'func' => 'FunctionConvert'],
		'merge' => ['args' => true, 'func' => 'FunctionMerge'],
		'addworkdays' => ['args' => true, 'func' => 'FunctionAddWorkDays'],
		'workdateadd' => ['args' => true, 'func' => 'FunctionWorkDateAdd'],
		'isworkday' => ['args' => true, 'func' => 'FunctionIsWorkDay'],
		'isworktime' => ['args' => true, 'func' => 'FunctionIsWorkTime'],
		'urlencode' => ['args' => true, 'func' => 'FunctionUrlencode'],
		'touserdate' => ['args' => true, 'func' => 'FunctionToUserDate'],
		'getuserdateoffset' => ['args' => true, 'func' => 'FunctionGetUserDateOffset'],
		'strtolower' => ['args' => true, 'func' => 'FunctionStrtolower'],
		'strtoupper' => ['args' => true, 'func' => 'FunctionStrtoupper'],
		'ucwords' => ['args' => true, 'func' => 'FunctionUcwords'],
		'ucfirst' => ['args' => true, 'func' => 'FunctionUcfirst'],
		'strtotime' => ['args' => true, 'func' => 'FunctionStrtotime'],
		'locdate' => ['args' => true, 'func' => 'FunctionLocDate'],
		'shuffle' => ['args' => true, 'func' => 'FunctionShuffle'],
		'firstvalue'=> ['args' => true, 'func' => 'FunctionFirstValue'],
		'swirl' => ['args' => true, 'func' => 'FunctionSwirl'],
		'getdocumenturl' => ['args' => true, 'func' => 'FunctionGetDocumentUrl'],
		'trim' => ['args' => true, 'func' => 'functionTrim'],
	];

	// Allowable errors
	private $arAvailableErrors = [
		0 => 'Incorrect variable name - "#STR#"',
		1 => 'Empty',
		2 => 'Syntax error "#STR#"',
		3 => 'Unknown function "#STR#"',
		4 => 'Unmatched closing bracket ")"',
		5 => 'Unmatched opening bracket "("',
		6 => 'Division by zero',
		7 => 'Incorrect order of operands',
		8 => 'Incorrect arguments of function "#STR#"',
	];

	const Operation = 0;
	const Variable = 1;
	const Constant = 2;

	public function __construct(CBPActivity $activity)
	{
		$this->activity = $activity;
	}

	private function getVariableValue($variable)
	{
		$variable = trim($variable);
		if (!preg_match(CBPActivity::ValuePattern, $variable))
			return null;

		return $this->activity->ParseValue($variable);
	}

	private function setError($errnum, $errstr = '')
	{
		$this->arErrorsList[] = [$errnum, str_replace('#STR#', $errstr, $this->arAvailableErrors[$errnum])];
	}

	public function getErrors()
	{
		return $this->arErrorsList;
	}

	/*
	Return array of polish notation
	array(
		key => array(value, self::Operation | self::Variable | self::Constant)
	)
	*/
	private function getPolishNotation($text)
	{
		$text = trim($text);
		if (mb_substr($text, 0, 1) === '=')
			$text = mb_substr($text, 1);
		if (mb_strpos($text, '{{=') === 0 && mb_substr($text, -2) == '}}')
		{
			$text = mb_substr($text, 3);
			$text = mb_substr($text, 0, -2);
		}

		if ($text == '')
		{
			$this->SetError(1);
			return false;
		}

		$arPolishNotation = $arStack = [];
		$prev = '';
		$preg = '/
			\s*\(\s*                          |
			\s*\)\s*                          |
			\s*,\s*                           | # Combine ranges of variables
			\s*;\s*                           | # Combine ranges of variables
			\s*=\s*                           |
			\s*<=\s*                          |
			\s*>=\s*                          |
			\s*<>\s*                          |
			\s*<\s*                           |
			\s*>\s*                           |
			\s*&\s*                           | # String concatenation
			\s*\+\s*                          | # Addition or unary plus
			\s*-\s*                           |
			\s*\*\s*                          |
			\s*\/\s*                          |
			\s*\^\s*                          | # Exponentiation
			\s*%\s*                           | # Percent
			\s*[\d\.]+\s*                     | # Numbers
			\s*\'[^\']*\'\s*                  | # String constants in apostrophes
			\s*"[^"]*"\s*                     | # String constants in quotes
			(\s*\w+\s*\(\s*)                  | # Function names
			\s*'.CBPActivity::ValueInternalPattern.'\s*  | # Variables
			(?<error>.+)                                # Any erroneous substring
			/xi';
		while (preg_match($preg, $text, $match))
		{
			if (isset($match['error']))
			{
				$this->SetError(2, $match['error']);
				return false;
			}

			$str = trim($match[0]);
			if ($str === ",")
				$str = ";";

			if (isset($match[1]) && $match[1])
			{
				$str = mb_strtolower($str);
				list($name, $left) = explode('(', $str);
				$name = trim($name);
				if (isset($this->arAvailableFunctions[$name]))
				{
					if (!$arStack)
					{
						array_unshift($arStack, [$name, $this->arPriority['f']]);
					}
					else
					{
						while ($this->arPriority['f'] <= $arStack[0][1])
						{
							$op = array_shift($arStack);
							$arPolishNotation[] = [$op[0], self::Operation];
							if (!$arStack)
								break;
						}
						array_unshift($arStack, [$name, $this->arPriority['f']]);
					}
				}
				else
				{
					$this->SetError(3, $name);
					return false;
				}
				$str = '(';
			}

			if ($str == '-' || $str == '+')
			{
				if ($prev == '' || in_array($prev, ['(', ';', '=', '<=', '>=', '<>', '<', '>', '&', '+', '-', '*', '/', '^']))
					$str .= 'm';
			}
			$prev = $str;

			switch ($str)
			{
				case '(':
					array_unshift($arStack, ['(', $this->arPriority['(']]);
					break;
				case ')':
					while ($op = array_shift($arStack))
					{
						if ($op[0] == '(')
							break;
						$arPolishNotation[] = [$op[0], self::Operation];
					}
					if ($op == null)
					{
						$this->SetError(4);
						return false;
					}
					break;
				case ';' :      case '=' :      case '<=':      case '>=':
				case '<>':      case '<' :      case '>' :      case '&' :
				case '+' :      case '-' :      case '+m':      case '-m':
				case '*' :      case '/' :      case '^' :      case '%' :
					if (!$arStack)
					{
						array_unshift($arStack, [$str, $this->arPriority[$str]]);
						break;
					}
					while ($this->arPriority[$str] <= $arStack[0][1])
					{
						$op = array_shift($arStack);
						$arPolishNotation[] = [$op[0], self::Operation];
						if (!$arStack)
							break;
					}
					array_unshift($arStack, [$str, $this->arPriority[$str]]);
					break;
				default:
					if (mb_substr($str, 0, 1) == '0' || (int) $str)
					{
						$arPolishNotation[] = [(float)$str, self::Constant];
						break;
					}
					if (mb_substr($str, 0, 1) == '"' || mb_substr($str, 0, 1) == "'")
					{
						$arPolishNotation[] = [mb_substr($str, 1, -1), self::Constant];
						break;
					}
					$arPolishNotation[] = [$str, self::Variable];
			}
			$text = mb_substr($text, mb_strlen($match[0]));
		}
		while ($op = array_shift($arStack))
		{
			if ($op[0] == '(')
			{
				$this->SetError(5);
				return false;
			}
			$arPolishNotation[] = [$op[0], self::Operation];
		}
		return $arPolishNotation;
	}

	public function calculate($text)
	{
		if (!$arPolishNotation = $this->GetPolishNotation($text))
			return null;

		$stack = [];
		foreach ($arPolishNotation as $item)
		{
			switch ($item[1])
			{
				case self::Constant:
					array_unshift($stack, $item[0]);
					break;
				case self::Variable:
					array_unshift($stack, $this->GetVariableValue($item[0]));
					break;
				case self::Operation:
					switch ($item[0])
					{
						case ';':
							$arg2 = array_shift($stack);
							$arg1 = array_shift($stack);
							if (!is_array($arg1) || !isset($arg1[0]))
								$arg1 = [$arg1];
							$arg1[] = $arg2;
							array_unshift($stack, $arg1);
							break;
						case '=':
							$arg2 = array_shift($stack);
							$arg1 = array_shift($stack);
							array_unshift($stack, $arg1 == $arg2);
							break;
						case '<=':
							$arg2 = array_shift($stack);
							$arg1 = array_shift($stack);
							array_unshift($stack, $arg1 <= $arg2);
							break;
						case '>=':
							$arg2 = array_shift($stack);
							$arg1 = array_shift($stack);
							array_unshift($stack, $arg1 >= $arg2);
							break;
						case '<>':
							$arg2 = array_shift($stack);
							$arg1 = array_shift($stack);
							array_unshift($stack, $arg1 != $arg2);
							break;
						case '<':
							$arg2 = array_shift($stack);
							$arg1 = array_shift($stack);
							array_unshift($stack, $arg1 < $arg2);
							break;
						case '>':
							$arg2 = array_shift($stack);
							$arg1 = array_shift($stack);
							array_unshift($stack, $arg1 > $arg2);
							break;
						case '&':
							$arg2 = (string) array_shift($stack);
							$arg1 = (string) array_shift($stack);
							array_unshift($stack, $arg1 . $arg2);
							break;
						case '+':
							$arg2 = (float) array_shift($stack);
							$arg1 = (float) array_shift($stack);
							array_unshift($stack, $arg1 + $arg2);
							break;
						case '-':
							$arg2 = (float) array_shift($stack);
							$arg1 = (float) array_shift($stack);
							array_unshift($stack, $arg1 - $arg2);
							break;
						case '+m':
							$arg = (float) array_shift($stack);
							array_unshift($stack, $arg);
							break;
						case '-m':
							$arg = (float) array_shift($stack);
							array_unshift($stack, (-$arg));
							break;
						case '*':
							$arg2 = (float) array_shift($stack);
							$arg1 = (float) array_shift($stack);
							array_unshift($stack, $arg1 * $arg2);
							break;
						case '/':
							$arg2 = (float) array_shift($stack);
							$arg1 = (float) array_shift($stack);
							if (0 == $arg2)
							{
								$this->SetError(6);
								return null;
							}
							array_unshift($stack, $arg1 / $arg2);
							break;
						case '^':
							$arg2 = (float) array_shift($stack);
							$arg1 = (float) array_shift($stack);
							array_unshift($stack, pow($arg1, $arg2));
							break;
						case '%':
							$arg = (float) array_shift($stack);
							array_unshift($stack, $arg / 100);
							break;
						default:
							$func = $this->arAvailableFunctions[$item[0]]['func'];
							if ($this->arAvailableFunctions[$item[0]]['args'])
							{
								$arg = array_shift($stack);
								$val = $this->$func($arg);
							}
							else
							{
								$val = $this->$func();
							}
							$error = is_float($val) && (is_nan($val) || is_infinite($val));
							if ($error)
							{
								$this->SetError(8, $item[0]);
								return null;
							}
							array_unshift($stack, $val);
					}
			}
		}
		if (count($stack) > 1)
		{
			$this->SetError(7);
			return null;
		}
		return array_shift($stack);
	}

	private function arrgsToArray($args)
	{
		if (!is_array($args))
			return [$args];

		$result = [];
		foreach ($args as $arg)
		{
			if (!is_array($arg))
			{
				$result[] = $arg;
			}
			else
			{
				foreach ($this->ArrgsToArray($arg) as $val)
					$result[] = $val;
			}
		}

		return $result;
	}

	/* Math */

	private function functionAbs($num)
	{
		return abs((float) $num);
	}

	private function functionRound($args)
	{
		$ar = $this->ArrgsToArray($args);
		$val = (float)array_shift($ar);
		$precision = (int)array_shift($ar);

		return round($val, $precision);
	}

	private function functionCeil($num)
	{
		return ceil((double)$num);
	}

	private function functionFloor($num)
	{
		return floor((double)$num);
	}

	private function functionMin($args)
	{
		if (!is_array($args))
			return (float) $args;

		foreach ($args as &$arg)
			$arg = (float) $arg;

		$args = $this->ArrgsToArray($args);

		return $args ? min($args) : false;
	}

	private function functionMax($args)
	{
		if (!is_array($args))
			return (float) $args;

		foreach ($args as &$arg)
			$arg = (float) $arg;

		$args = $this->ArrgsToArray($args);

		return $args ? max($args) : false;
	}

	private function functionRand($args)
	{
		$ar = $this->ArrgsToArray($args);
		$min = (int)array_shift($ar);
		$max = (int)array_shift($ar);
		if (!$max)
		{
			$max = mt_getrandmax();
		}

		return mt_rand($min, $max);
	}

	private function functionIntval($num)
	{
		return intval($num);
	}

	private function functionFloatval($num)
	{
		return floatval($num);
	}

	/* Logic */

	private function functionTrue()
	{
		return true;
	}

	private function functionFalse()
	{
		return false;
	}

	private function functionIf($args)
	{
		if (!is_array($args))
			return null;

		$expression = (boolean) array_shift($args);
		$ifTrue = array_shift($args);
		$ifFalse = array_shift($args);
		return $expression ? $ifTrue : $ifFalse;
	}

	private function functionNot($arg)
	{
		return !((boolean) $arg);
	}

	private function functionAnd($args)
	{
		if (!is_array($args))
			return (boolean) $args;

		$args = $this->ArrgsToArray($args);

		foreach ($args as $arg)
		{
			if (!$arg)
				return false;
		}
		return true;
	}

	private function functionOr($args)
	{
		if (!is_array($args))
			return (boolean) $args;

		$args = $this->ArrgsToArray($args);
		foreach ($args as $arg)
		{
			if ($arg)
				return true;
		}

		return false;
	}

	/* Date */

	private function functionDateAdd($args)
	{
		if (!is_array($args))
		{
			$args = [$args];
		}

		$ar = $this->ArrgsToArray($args);
		$date = array_shift($ar);
		$offset = $this->getDateTimeOffset($date);
		$interval = array_shift($ar);

		if (($date = $this->makeTimestamp($date)) === false)
		{
			return null;
		}

		if (empty($interval))
		{
			return $date; // new Bizproc\BaseType\Value\DateTime($date, $offset);
		}

		// 1Y2M3D4H5I6S, -4 days 5 hours, 1month, 5h

		$interval = trim($interval);
		$bMinus = false;
		if (mb_substr($interval, 0, 1) === "-")
		{
			$interval = mb_substr($interval, 1);
			$bMinus = true;
		}

		static $arMap = ["y" => "YYYY", "year" => "YYYY", "years" => "YYYY",
			"m" => "MM", "month" => "MM", "months" => "MM",
			"d" => "DD", "day" => "DD", "days" => "DD",
			"h" => "HH", "hour" => "HH", "hours" => "HH",
			"i" => "MI", "min" => "MI", "minute" => "MI", "minutes" => "MI",
			"s" => "SS", "sec" => "SS", "second" => "SS", "seconds" => "SS",
		];

		$arInterval = [];
		while (preg_match('/\s*([\d]+)\s*([a-z]+)\s*/i', $interval, $match))
		{
			$match2 = mb_strtolower($match[2]);
			if (array_key_exists($match2, $arMap))
			{
				$arInterval[$arMap[$match2]] = ($bMinus ? -intval($match[1]) : intval($match[1]));
			}

			$p = mb_strpos($interval, $match[0]);
			$interval = mb_substr($interval, $p + mb_strlen($match[0]));
		}

		$date += $offset; // to server

		$newDate = AddToTimeStamp($arInterval, $date);

		$newDate -= $offset; // to user timezone

		return new Bizproc\BaseType\Value\DateTime($newDate, $offset);
	}

	private function functionWorkDateAdd($args)
	{
		if (!is_array($args))
			$args = [$args];

		$ar = $this->ArrgsToArray($args);
		$date = array_shift($ar);
		$offset = $this->getDateTimeOffset($date);
		$paramInterval = array_shift($ar);
		$user = array_shift($ar);

		if ($user)
		{
			$date = $this->FunctionToUserDate([$user, $date]);
			$offset = $this->getDateTimeOffset($date);
		}

		if (($date = $this->makeTimestamp($date, true)) === false)
			return null;

		if (empty($paramInterval) || !CModule::IncludeModule('calendar'))
			return $date;

		$paramInterval = trim($paramInterval);
		$multiplier = 1;
		if (mb_substr($paramInterval, 0, 1) === "-")
		{
			$paramInterval = mb_substr($paramInterval, 1);
			$multiplier = -1;
		}

		$workDayInterval = $this->getWorkDayInterval();
		$intervalMap = ["d" => $workDayInterval, "day" => $workDayInterval, "days" => $workDayInterval,
						"h" => 3600, "hour" => 3600, "hours" => 3600,
						"i" => 60, "min" => 60, "minute" => 60, "minutes" => 60,
		];

		$interval = 0;
		while (preg_match('/\s*([\d]+)\s*([a-z]+)\s*/i', $paramInterval, $match))
		{
			$match2 = mb_strtolower($match[2]);
			if (array_key_exists($match2, $intervalMap))
				$interval += intval($match[1]) * $intervalMap[$match2];

			$p = mb_strpos($paramInterval, $match[0]);
			$paramInterval = mb_substr($paramInterval, $p + mb_strlen($match[0]));
		}

		if (date('H:i:s', $date) === '00:00:00')
		{
			//add start work day seconds
			$date += $this->getCalendarWorkTime()[0];
		}

		$date = $this->getNearestWorkTime($date, $multiplier);
		if ($interval)
		{
			$days = (int) floor($interval / $workDayInterval);
			$hours = $interval % $workDayInterval;

			$remainTimestamp = $this->getWorkDayRemainTimestamp($date, $multiplier);

			if ($days)
				$date = $this->addWorkDay($date, $days * $multiplier);

			if ($hours > $remainTimestamp)
			{
				$date += $multiplier < 0 ? -$remainTimestamp -60 : $remainTimestamp + 60;
				$date = $this->getNearestWorkTime($date, $multiplier) + (($hours - $remainTimestamp) * $multiplier);
			}
			else
				$date += $multiplier * $hours;
		}

		$date -= $offset;

		return new Bizproc\BaseType\Value\DateTime($date, $offset);
	}

	private function functionAddWorkDays($args)
	{
		if (!is_array($args))
			$args = [$args];

		$ar = $this->ArrgsToArray($args);
		$date = array_shift($ar);
		$offset = $this->getDateTimeOffset($date);
		$days = (int) array_shift($ar);

		if (($date = $this->makeTimestamp($date)) === false)
			return null;

		if ($days === 0 || !CModule::IncludeModule('calendar'))
			return $date;

		$date = $this->addWorkDay($date, $days);

		return new Bizproc\BaseType\Value\DateTime($date, $offset);
	}

	private function functionIsWorkDay($args)
	{
		if (!CModule::IncludeModule('calendar'))
			return null;

		if (!is_array($args))
			$args = [$args];

		$ar = $this->ArrgsToArray($args);
		$date = array_shift($ar);
		$user = array_shift($ar);

		if ($user)
		{
			$date = $this->FunctionToUserDate([$user, $date]);
		}

		if (($date = $this->makeTimestamp($date, true)) === false)
			return null;

		return !$this->isHoliday($date);
	}

	private function functionIsWorkTime($args)
	{
		if (!CModule::IncludeModule('calendar'))
			return null;

		if (!is_array($args))
			$args = [$args];

		$ar = $this->ArrgsToArray($args);
		$date = array_shift($ar);
		$user = array_shift($ar);

		if ($user)
		{
			$date = $this->FunctionToUserDate([$user, $date]);
		}

		if (($date = $this->makeTimestamp($date, true)) === false)
			return null;

		return !$this->isHoliday($date) && $this->isWorkTime($date);
	}

	private function functionDateDiff($args)
	{
		if (!is_array($args))
			$args = [$args];

		$ar = $this->ArrgsToArray($args);
		$date1 = array_shift($ar);
		$date2 = array_shift($ar);
		$format = array_shift($ar);

		if ($date1 == null || $date2 == null)
			return null;

		$date1Formatted = $this->getDateTimeObject($date1);
		$date2Formatted = $this->getDateTimeObject($date2);
		if ($date1Formatted === false || $date2Formatted === false)
		{
			return null;
		}

		$interval = $date1Formatted->diff($date2Formatted);

		return $interval === false ? null : $interval->format($format);
	}

	private function functionDate($args)
	{
		$ar = $this->ArrgsToArray($args);
		$format = array_shift($ar);
		$date = array_shift($ar);

		if (!$format || !is_string($format))
		{
			return null;
		}

		$ts = $date ? $this->makeTimestamp($date, true) : time();

		if (!$ts)
		{
			return null;
		}

		return date($format, $ts);
	}

	private function functionToUserDate($args)
	{
		if (!is_array($args))
		{
			$args = [$args];
		}

		$ar = $this->ArrgsToArray($args);
		$user = array_shift($ar);
		$date = array_shift($ar);

		if (!$user)
		{
			return null;
		}

		if (!$date)
		{
			$date = time();
		}
		elseif (($date = $this->makeTimestamp($date)) === false)
		{
			return null;
		}

		$userId = CBPHelper::ExtractUsers($user, $this->activity->GetDocumentId(), true);
		$offset = $userId ? CTimeZone::GetOffset($userId, true) : 0;

		return new Bizproc\BaseType\Value\DateTime($date, $offset);
	}

	private function functionGetUserDateOffset($args)
	{
		if (!is_array($args))
		{
			$args = [$args];
		}

		$ar = $this->ArrgsToArray($args);
		$user = array_shift($ar);

		if (!$user)
		{
			return null;
		}

		$userId = CBPHelper::ExtractUsers($user, $this->activity->GetDocumentId(), true);

		if (!$userId)
		{
			return null;
		}

		return CTimeZone::GetOffset($userId, true);
	}

	private function functionStrtotime($args)
	{
		$ar = $this->ArrgsToArray($args);
		$datetime = (string)array_shift($ar);
		$baseDate = array_shift($ar);

		$baseTimestamp = $baseDate ? $this->makeTimestamp($baseDate, true) : time();

		if (!$baseTimestamp)
		{
			return null;
		}

		$timestamp = strtotime($datetime, (int)$baseTimestamp);

		if ($timestamp === false)
		{
			return null;
		}

		return new Bizproc\BaseType\Value\DateTime($timestamp);
	}

	private function functionLocDate($args)
	{
		$ar = $this->ArrgsToArray($args);
		$format = array_shift($ar);
		$date = array_shift($ar);

		if (!$format || !is_string($format))
		{
			return null;
		}

		$reformFormat = $this->frameSymbolsInDateFormat($format);
		$timestamp = $date ? $this->makeTimestamp($date, true) : time();

		if (!$timestamp)
		{
			return null;
		}

		$formattedDate = date($reformFormat, $timestamp);

		if ($formattedDate === false)
		{
			return null;
		}

		return $this->replaceDateToLocDate($formattedDate, $reformFormat);
	}

	/* Date - Helpers */

	private function makeTimestamp($date, $appendOffset = false)
	{
		if (!$date)
		{
			return false;
		}

		//serialized date string
		if (is_string($date) && Bizproc\BaseType\Value\Date::isSerialized($date))
		{
			$date = new Bizproc\BaseType\Value\Date($date);
		}

		if ($date instanceof Bizproc\BaseType\Value\Date)
		{
			return $date->getTimestamp() + ($appendOffset? $date->getOffset() : 0);
		}

		if (intval($date)."!" === $date."!")
			return $date;

		if (($result = MakeTimeStamp($date, FORMAT_DATETIME)) === false)
		{
			if (($result = MakeTimeStamp($date, FORMAT_DATE)) === false)
			{
				if (($result = MakeTimeStamp($date, "YYYY-MM-DD HH:MI:SS")) === false)
				{
					$result = MakeTimeStamp($date, "YYYY-MM-DD");
				}
			}
		}
		return $result;
	}

	private function getWorkDayTimestamp($date)
	{
		return date('H', $date) * 3600 + date('i', $date) * 60;
	}

	private function getWorkDayRemainTimestamp($date, $multiplier = 1)
	{
		$dayTs = $this->getWorkDayTimestamp($date);
		list ($startSeconds, $endSeconds) = $this->getCalendarWorkTime();
		return $multiplier < 0 ? $dayTs - $startSeconds :$endSeconds - $dayTs;
	}

	private function getWorkDayInterval()
	{
		list ($startSeconds, $endSeconds) = $this->getCalendarWorkTime();
		return $endSeconds - $startSeconds;
	}

	private function isHoliday($date)
	{
		[$yearWorkdays] = $this->getCalendarWorkdays();
		[$weekHolidays, $yearHolidays] = $this->getCalendarHolidays();

		$dayOfYear = date('j.n', $date);
		if (in_array($dayOfYear, $yearWorkdays, true))
		{
			return false;
		}

		$dayOfWeek = date('w', $date);
		if (in_array($dayOfWeek, $weekHolidays))
		{
			return true;
		}

		$dayOfYear = date('j.n', $date);
		if (in_array($dayOfYear, $yearHolidays, true))
		{
			return true;
		}

		return false;
	}

	private function isWorkTime($date)
	{
		$dayTs = $this->getWorkDayTimestamp($date);
		list ($startSeconds, $endSeconds) = $this->getCalendarWorkTime();
		return ($dayTs >= $startSeconds && $dayTs <= $endSeconds);
	}

	private function getNearestWorkTime($date, $multiplier = 1)
	{
		$reverse = $multiplier < 0;
		list ($startSeconds, $endSeconds) = $this->getCalendarWorkTime();
		$dayTimeStamp = $this->getWorkDayTimestamp($date);

		if ($this->isHoliday($date))
		{
			$date -= $dayTimeStamp;
			$date += $reverse? -86400 + $endSeconds : $startSeconds;
			$dayTimeStamp = $reverse? $endSeconds : $startSeconds;
		}

		if (!$this->isWorkTime($date))
		{
			$date -= $dayTimeStamp;

			if ($dayTimeStamp < $startSeconds)
			{
				$date += $reverse? -86400 + $endSeconds : $startSeconds;
			}
			else
			{
				$date += $reverse? $endSeconds : 86400 + $startSeconds;
			}
		}

		if ($this->isHoliday($date))
			$date = $this->addWorkDay($date, $reverse? -1 : 1);

		return $date;
	}

	private function addWorkDay($date, $days)
	{
		$delta = 86400;
		if ($days < 0)
			$delta *= -1;

		$days = abs($days);
		$iterations = 0;

		while ($days > 0 && $iterations < 1000)
		{
			++$iterations;
			$date += $delta;

			if ($this->isHoliday($date))
				continue;
			--$days;
		}

		return $date;
	}

	private function getCalendarHolidays()
	{
		if (static::$yearHolidays === null)
		{
			$calendarSettings = CCalendar::GetSettings();
			$weekHolidays = [0, 6];
			$yearHolidays = [];

			if (isset($calendarSettings['week_holidays']))
			{
				$weekDays = ['SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6];
				$weekHolidays = [];
				foreach ($calendarSettings['week_holidays'] as $day)
					$weekHolidays[] = $weekDays[$day];
			}

			if (isset($calendarSettings['year_holidays']))
			{
				foreach (explode(',', $calendarSettings['year_holidays']) as $yearHoliday)
				{
					$date = explode('.', trim($yearHoliday));
					if (count($date) == 2 && $date[0] && $date[1])
						$yearHolidays[] = (int)$date[0] . '.' . (int)$date[1];
				}
			}
			static::$weekHolidays = $weekHolidays;
			static::$yearHolidays = $yearHolidays;
		}

		return [static::$weekHolidays, static::$yearHolidays];
	}

	private function getCalendarWorkTime()
	{
		if (static::$startWorkDay === null)
		{
			$startSeconds = 0;
			$endSeconds = 24 * 3600 - 1;

			$calendarSettings = CCalendar::GetSettings();
			if (!empty($calendarSettings['work_time_start']))
			{
				$time = explode('.', $calendarSettings['work_time_start']);
				$startSeconds = $time[0] * 3600;
				if (!empty($time[1]))
					$startSeconds += $time[1] * 60;
			}

			if (!empty($calendarSettings['work_time_end']))
			{
				$time = explode('.', $calendarSettings['work_time_end']);
				$endSeconds = $time[0] * 3600;
				if (!empty($time[1]))
					$endSeconds += $time[1] * 60;
			}
			static::$startWorkDay = $startSeconds;
			static::$endWorkDay = $endSeconds;
		}
		return [static::$startWorkDay, static::$endWorkDay];
	}

	private function getCalendarWorkdays()
	{
		if (static::$yearWorkdays === null)
		{
			$yearWorkdays = [];

			$calendarSettings = CCalendar::GetSettings();
			$calendarYearWorkdays = $calendarSettings['year_workdays'] ?? '';

			foreach (explode(',', $calendarYearWorkdays) as $yearWorkday)
			{
				$date = explode('.', trim($yearWorkday));
				if (count($date) === 2 && $date[0] && $date[1])
				{
					$yearWorkdays[] = (int)$date[0] . '.' . (int)$date[1];
				}
			}

			static::$yearWorkdays = $yearWorkdays;
		}

		return [static::$yearWorkdays];
	}

	private function getDateTimeObject($date)
	{
		if ($date instanceof Bizproc\BaseType\Value\Date)
		{
			return (new \DateTime())->setTimestamp($date->getTimestamp());
		}

		$df = Main\Type\DateTime::getFormat();
		$df2 = Main\Type\Date::getFormat();
		$date1Formatted = \DateTime::createFromFormat($df, $date);
		if ($date1Formatted === false)
		{
			$date1Formatted = \DateTime::createFromFormat($df2, $date);
			if ($date1Formatted)
			{
				$date1Formatted->setTime(0, 0);
			}
		}
		return $date1Formatted;
	}

	private function getDateTimeOffset($date)
	{
		if ($date instanceof Bizproc\BaseType\Value\Date)
		{
			return $date->getOffset();
		}
		return 0;
	}

	private function frameSymbolsInDateFormat($format)
	{
		$complexSymbols = ['j F', 'd F', 'jS F'];
		$symbols = ['D', 'l', 'F', 'M', 'r'];

		$frameRule = [];
		foreach ($symbols as $symbol)
		{
			$frameRule[$symbol] = '#' . $symbol . '#';
			$frameRule['\\' . $symbol] = '\\' . $symbol;
		}
		foreach ($complexSymbols as $symbol)
		{
			$frameRule[$symbol] = substr($symbol, 0, -1) . '#' . $symbol[-1] . '_1#';
			$frameRule['\\' . $symbol] = '\\' . substr($symbol, 0, -1) . '#' . $symbol[-1] . '#';
		}

		return strtr($format, $frameRule);
	}

	private function frameNamesInFormattedDateRFC2822($formattedDate)
	{
		$matches = [];
		$pattern = "/#(\w{3}), \d{2} (\w{3}) \d{4} \d{2}:\d{2}:\d{2} [+-]\d{4}#/";
		if (preg_match_all($pattern, $formattedDate, $matches))
		{
			foreach ($matches[0] as $key => $match)
			{
				$day = $matches[1][$key];
				$month = $matches[2][$key];

				$reformMatch = str_replace(
					[$day, $month],
					['#' . $day . '#', '#' . $month . '#'],
					$match
				);
				$reformMatch = substr($reformMatch, 1, -1);

				$formattedDate = str_replace($match, $reformMatch, $formattedDate);
			}
		}

		return $formattedDate;
	}

	private function replaceDateToLocDate($formattedDate, $format)
	{
		$lenShortName = 3;
		$dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
		$monthNames = [
			'January',
			'February',
			'March',
			'April',
			'May',
			'June',
			'July',
			'August',
			'September',
			'October',
			'November',
			'December',
		];

		if (strpos($format, '#r#') !== false)
		{
			$formattedDate = $this->frameNamesInFormattedDateRFC2822($formattedDate);
		}

		$replacementRule = [];
		foreach (array_merge($dayNames, $monthNames) as $name)
		{
			$replacementRule['#' . $name . '#'] = GetMessage(
				'BPCGCALC_LOCDATE_' . strtoupper($name)
			);
			$shortName = substr($name, 0, $lenShortName);
			$replacementRule['#' . $shortName . '#'] = GetMessage(
				'BPCGCALC_LOCDATE_' . strtoupper($shortName) . '_SHORT'
			);
		}
		foreach ($monthNames as $monthName)
		{
			$replacementRule['#' . $monthName . '_1' . '#'] = GetMessage(
				'BPCGCALC_LOCDATE_' . strtoupper($monthName) . '_1'
			);
		}

		return strtr($formattedDate, $replacementRule);
	}

	/* String & Formatting */

	private function functionNumberFormat($args)
	{
		$ar = $this->ArrgsToArray($args);
		$number = (float) array_shift($ar);
		$decimals = (int) (array_shift($ar) ?: 0);
		$decPoint = array_shift($ar);
		if ($decPoint === null)
		{
			$decPoint = '.';
		}
		$decPoint = (string) $decPoint;

		$thousandsSeparator = array_shift($ar);
		if ($thousandsSeparator === null)
		{
			$thousandsSeparator = ',';
		}
		$thousandsSeparator = (string) $thousandsSeparator;

		return number_format($number, $decimals, $decPoint, $thousandsSeparator);
	}

	private function functionRandString($args)
	{
		$ar = $this->ArrgsToArray($args);
		$len = (int)array_shift($ar);

		return \randString($len);
	}

	private function functionSubstr($args)
	{
		if (!is_array($args))
			$args = [$args];

		$ar = $this->ArrgsToArray($args);
		$str = array_shift($ar);
		$pos = (int)array_shift($ar);
		$len = (int)array_shift($ar);

		if (($str == null) || ($str === ""))
			return null;

		if ($len)
		{
			return mb_substr($str, $pos, $len);
		}

		return mb_substr($str, $pos);
	}

	private function functionStrpos($args)
	{
		$ar = $this->ArrgsToArray($args);
		$haystack = (string)array_shift($ar);

		if (empty($haystack))
		{
			return false;
		}

		$maxOffset = mb_strlen($haystack);
		$minOffset = -1 * $maxOffset;

		$needle = (string)array_shift($ar);
		$offset = max($minOffset, min($maxOffset, (int)array_shift($ar)));

		return mb_strpos($haystack, $needle, $offset);
	}

	private function functionStrlen($args)
	{
		$ar = $this->ArrgsToArray($args);
		$str = array_shift($ar);

		if (!is_scalar($str))
		{
			return null;
		}

		$str = (string) $str;
		return mb_strlen($str);
	}

	private function functionImplode($args)
	{
		$ar = (array) $args;
		$glue = (string)array_shift($ar);
		$pieces = \CBPHelper::MakeArrayFlat(array_shift($ar));

		if (!$pieces)
		{
			return '';
		}

		return implode($glue, $pieces);
	}

	private function functionExplode($args)
	{
		$ar = (array) $args;
		$delimiter = array_shift($ar);
		$str = array_shift($ar);

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

		$str = (string) $str;
		return explode($delimiter, $str);
	}

	private function functionUrlencode($args)
	{
		$ar = $this->ArrgsToArray($args);
		$str = array_shift($ar);

		if (!is_scalar($str))
		{
			if (is_array($str))
			{
				$str = implode(", ", CBPHelper::MakeArrayFlat($str));
			}
			else
			{
				return null;
			}
		}

		$str = (string) $str;
		return urlencode($str);
	}

	private function functionConvert($args)
	{
		if (!is_array($args))
			$args = [$args];

		$ar = $this->ArrgsToArray($args);
		$val = array_shift($ar);
		$type = array_shift($ar);
		$attr = array_shift($ar);

		$type = mb_strtolower($type);
		if ($type === 'printableuserb24')
		{
			$result = [];

			$users = CBPHelper::StripUserPrefix($val);
			if (!is_array($users))
				$users = [$users];

			foreach ($users as $userId)
			{
				$db = CUser::GetByID($userId);
				if ($ar = $db->GetNext())
				{
					$ix = randString(5);
					$attr = (!empty($attr) ? 'href="'.$attr.'"' : 'href="#" onClick="return false;"');
					$result[] = '<a class="feed-post-user-name" id="bp_'.$userId.'_'.$ix.'" '.$attr.' bx-post-author-id="'.$userId.'" bx-post-author-gender="'.$ar['PERSONAL_GENDER'].'" bx-tooltip-user-id="'.$userId.'">'.CUser::FormatName(CSite::GetNameFormat(false), $ar, false).'</a>';
				}
			}

			$result = implode(", ", $result);
		}
		elseif ($type == 'printableuser')
		{
			$result = [];

			$users = CBPHelper::StripUserPrefix($val);
			if (!is_array($users))
				$users = [$users];

			foreach ($users as $userId)
			{
				$db = CUser::GetByID($userId);
				if ($ar = $db->GetNext())
					$result[] = CUser::FormatName(CSite::GetNameFormat(false), $ar, false);
			}

			$result = implode(", ", $result);

		}
		else
		{
			$result = $val;
		}

		return $result;
	}

	private function functionStrtolower($args)
	{
		$ar = $this->ArrgsToArray($args);
		$str = array_shift($ar);

		if (!is_scalar($str))
		{
			return null;
		}

		return mb_strtolower((string) $str);
	}

	private function functionStrtoupper($args)
	{
		$ar = $this->ArrgsToArray($args);
		$str = array_shift($ar);

		if (!is_scalar($str))
		{
			return null;
		}

		return mb_strtoupper((string) $str);
	}

	private function functionUcwords($args)
	{
		$ar = $this->ArrgsToArray($args);
		$str = array_shift($ar);

		if (!is_scalar($str))
		{
			return null;
		}

		return mb_convert_case((string) $str, MB_CASE_TITLE);
	}

	private function functionUcfirst($args)
	{
		$ar = $this->ArrgsToArray($args);
		$str = array_shift($ar);

		if (!is_scalar($str))
		{
			return null;
		}

		return $this->mb_ucfirst((string) $str);
	}

	private function mb_ucfirst($str)
	{
		$len = mb_strlen($str);
		$firstChar = mb_substr($str, 0, 1);
		$otherChars = mb_substr($str, 1, $len - 1);
		return mb_strtoupper($firstChar) . $otherChars;
	}

	private function functionTrim($args)
	{
		$array = $this->ArrgsToArray($args);
		if (empty($array))
		{
			return null;
		}

		$result = [];
		foreach ($array as $str)
		{
			if (is_scalar($str) || (is_object($str) && method_exists($str, '__toString')))
			{
				$result[] = trim((string)$str);

				continue;
			}

			return null;
		}

		return count($result) > 1 ? $result : $result[0];
	}

	/* Complex values */

	private function functionMerge($args)
	{
		if (!is_array($args))
			$args = [];

		foreach ($args as &$a)
		{
			$a = is_object($a) ? [$a] : (array)$a;
		}
		return call_user_func_array('array_merge', $args);
	}

	private function functionShuffle($args)
	{
		if (!is_array($args) || $args === [])
		{
			return null;
		}

		$array = $this->ArrgsToArray($args);
		shuffle($array);

		return $array;
	}

	private function functionFirstValue($args)
	{
		$ar = $this->ArrgsToArray($args);

		return $ar[0] ?? null;
	}

	private function functionSwirl($args)
	{
		$ar = $this->ArrgsToArray($args);
		if (count($ar) <= 1)
		{
			return $ar[0] ?? null;
		}

		return array_merge(array_slice($ar, 1), [$ar[0]]);
	}

	private function functionGetDocumentUrl($args)
	{
		$ar = $this->ArrgsToArray($args);
		$format = array_shift($ar);
		$external = array_shift($ar);

		$url = $this->activity->workflow->getService('DocumentService')->GetDocumentAdminPage(
			$this->activity->getDocumentId()
		);
		$name = null;

		if ($external)
		{
			$url = Main\Engine\UrlManager::getInstance()->getHostUrl() . $url;
		}

		if ($format === 'bb' || $format === 'html')
		{
			$name = $this->activity->workflow->getService('DocumentService')->getDocumentName(
				$this->activity->getDocumentId()
			);
		}

		if ($format === 'bb')
		{
			return sprintf(
				'[url=%s]%s[/url]',
				$url,
				$name
			);
		}

		if ($format === 'html')
		{
			return sprintf(
				'<a href="%s" target="_blank">%s</a>',
				$url,
				htmlspecialcharsbx($name)
			);
		}

		return $url;
	}
}
