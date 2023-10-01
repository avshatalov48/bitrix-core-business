<?php

namespace Bitrix\Bizproc\Calc;

class Parser
{
	private const Operation = 0;
	private const Variable = 1;
	private const Constant = 2;

	private \CBPActivity $activity;
	private array $errors = [];
	private array $priority = [
		'(' => 0,
		')' => 1,
		'@' => 2,
		';' => 2,
		'=' => 3,
		'<' => 3,
		'>' => 3,
		'<=' => 3,
		'>=' => 3,
		'<>' => 3,
		'&' => 4,
		'+' => 5,
		'-' => 5,
		'*' => 6,
		'/' => 6,
		'^' => 7,
		'%' => 8,
		'-m' => 9,
		'+m' => 9,
		' ' => 10,
		':' => 11,
		'f' => 12,
	];

	// Allowable functions
	private array $functions;

	private array $errorMessages = [
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

	public function __construct(\CBPActivity $activity)
	{
		$this->activity = $activity;
		$this->functions = Functions::getList();
	}

	public function getActivity(): \CBPActivity
	{
		return $this->activity;
	}

	private function getVariableValue($variable)
	{
		$variable = trim($variable);
		if (!preg_match(\CBPActivity::ValuePattern, $variable))
		{
			return null;
		}

		return $this->activity->parseValue($variable);
	}

	private function setError($errorCode, $message = ''): void
	{
		$this->errors[] = [$errorCode, str_replace('#STR#', $message, $this->errorMessages[$errorCode])];
	}

	public function getErrors(): array
	{
		return $this->errors;
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
		if (mb_strpos($text, '=') === 0)
		{
			$text = mb_substr($text, 1);
		}
		if (mb_strpos($text, '{{=') === 0 && mb_substr($text, -2) === '}}')
		{
			$text = mb_substr($text, 3);
			$text = mb_substr($text, 0, -2);
		}

		if (!$text)
		{
			$this->setError(1);

			return false;
		}

		$notation = [];
		$stack = [];
		$prev = '';

		$isFunctionArgs = function ($stack)
		{
			return (
				isset($stack[0], $stack[1], $this->functions[$stack[1][0]]) && $stack[0][0] === '('
			);
		};

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
			\s*[\d.]+\s*                     | # Numbers
			\s*\'[^\']*\'\s*                  | # String constants in apostrophes
			\s*"[^"]*"\s*                     | # String constants in quotes
			(\s*\w+\s*\(\s*)                  | # Function names
			\s*' . \CBPActivity::ValueInternalPattern . '\s*  | # Variables
			(?<error>.+)                                # Any erroneous substring
			/xi';

		while (preg_match($preg, $text, $match))
		{
			if (isset($match['error']))
			{
				$this->setError(2, $match['error']);

				return false;
			}

			$str = trim($match[0]);
			if ($str === ",")
			{
				$str = ";";
			}

			if (isset($match[1]) && $match[1])
			{
				$str = mb_strtolower($str);
				[$name] = explode('(', $str);
				$name = trim($name);
				if (isset($this->functions[$name]))
				{
					if ($stack)
					{
						while ($this->priority['f'] <= $stack[0][1])
						{
							$op = array_shift($stack);
							$notation[] = [$op[0], self::Operation];
							if (!$stack)
							{
								break;
							}
						}
					}
					array_unshift($stack, [$name, $this->priority['f']]);
				}
				else
				{
					$this->setError(3, $name);

					return false;
				}
				$str = '(';
			}

			if ($str === '-' || $str === '+')
			{
				if (
					$prev === ''
					|| in_array($prev, ['(', ';', '=', '<=', '>=', '<>', '<', '>', '&', '+', '-', '*', '/', '^'])
				)
				{
					$str .= 'm';
				}
			}

			switch ($str)
			{
				case '(':
					array_unshift($stack, ['(', $this->priority['(']]);
					array_unshift($stack, ['@', $this->priority['@']]);
					break;
				case ')':
					$hasComma = false;
					$hasArguments = $prev !== '(';
					//trailing comma
					if ($prev === ';')
					{
						array_shift($stack);
					}

					while ($op = array_shift($stack))
					{
						if ($op[0] === '(')
						{
							break;
						}
						if ($op[0] === ';')
						{
							$hasComma = true;
						}

						$isInFunction = $isFunctionArgs($stack);

						if (
							$op[0] === '@'
							&& (
								(!$hasComma && !$isInFunction)
								|| (!$hasArguments && $isInFunction)
							)
						)
						{
							continue;
						}
						$notation[] = [$op[0], self::Operation];
					}
					if ($op === null)
					{
						$this->setError(4);

						return false;
					}
					break;
				case ';' :
				case '=' :
				case '<=':
				case '>=':
				case '<>':
				case '<' :
				case '>' :
				case '&' :
				case '+' :
				case '-' :
				case '+m':
				case '-m':
				case '*' :
				case '/' :
				case '^' :
				case '%' :
					if (!$stack)
					{
						array_unshift($stack, [$str, $this->priority[$str]]);
						if ($str === ';')
						{
							$notation[] = ['@', self::Operation];
						}
						break;
					}
					while ($this->priority[$str] <= $stack[0][1])
					{
						$op = array_shift($stack);
						$notation[] = [$op[0], self::Operation];
						if (!$stack)
						{
							break;
						}
					}
					array_unshift($stack, [$str, $this->priority[$str]]);
					break;
				default:
					if (mb_strpos($str, '0') === 0 || (int)$str)
					{
						$notation[] = [(float)$str, self::Constant];
						break;
					}
					if (mb_strpos($str, '"') === 0 || mb_strpos($str, "'") === 0)
					{
						$notation[] = [mb_substr($str, 1, -1), self::Constant];
						break;
					}
					$notation[] = [$str, self::Variable];
			}
			$text = mb_substr($text, mb_strlen($match[0]));
			$prev = $str;
		}
		while ($op = array_shift($stack))
		{
			if ($op[0] === '(')
			{
				$this->setError(5);

				return false;
			}
			$notation[] = [$op[0], self::Operation];
		}

		return $notation;
	}

	public function calculate($text)
	{
		if (!$notation = $this->getPolishNotation($text))
		{
			return null;
		}

		$stack = [];
		foreach ($notation as $item)
		{
			switch ($item[1])
			{
				case self::Constant:
					array_unshift($stack, $item[0]);
					break;
				case self::Variable:
					array_unshift($stack, $this->getVariableValue($item[0]));
					break;
				case self::Operation:
					switch ($item[0])
					{
						case '@':
							$arg = array_shift($stack);
							array_unshift($stack, [$arg]);
							break;
						case ';':
							$arg2 = array_shift($stack);
							$arg1 = array_shift($stack);
							if (!is_array($arg1) || !isset($arg1[0]))
							{
								$arg1 = [$arg1];
							}
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
							$arg2 = \CBPHelper::stringify(array_shift($stack));
							$arg1 = \CBPHelper::stringify(array_shift($stack));
							array_unshift($stack, $arg1 . $arg2);
							break;
						case '+':
							$arg2 = (float)($this->toSingleValue(array_shift($stack)));
							$arg1 = (float)($this->toSingleValue(array_shift($stack)));
							array_unshift($stack, $arg1 + $arg2);
							break;
						case '-':
							$arg2 = (float)($this->toSingleValue(array_shift($stack)));
							$arg1 = (float)($this->toSingleValue(array_shift($stack)));
							array_unshift($stack, $arg1 - $arg2);
							break;
						case '+m':
							$arg = (float)array_shift($stack);
							array_unshift($stack, $arg);
							break;
						case '-m':
							$arg = (float)array_shift($stack);
							array_unshift($stack, (-$arg));
							break;
						case '*':
							$arg2 = (float)($this->toSingleValue(array_shift($stack)));
							$arg1 = (float)($this->toSingleValue(array_shift($stack)));
							array_unshift($stack, $arg1 * $arg2);
							break;
						case '/':
							$arg2 = (float)($this->toSingleValue(array_shift($stack)));
							$arg1 = (float)($this->toSingleValue(array_shift($stack)));
							if (0 == $arg2)
							{
								$this->setError(6);

								return null;
							}
							array_unshift($stack, $arg1 / $arg2);
							break;
						case '^':
							$arg2 = (float)array_shift($stack);
							$arg1 = (float)array_shift($stack);
							array_unshift($stack, $arg1 ** $arg2);
							break;
						case '%':
							$arg = (float)array_shift($stack);
							array_unshift($stack, $arg / 100);
							break;
						default:
							$func = $this->functions[$item[0]]['func'];
							$functionArgs = new Arguments($this);
							if (!empty($this->functions[$item[0]]['args']))
							{
								$args = array_shift($stack);
								$functionArgs->setArgs(is_array($args) ? $args : [$args]);
							}

							$val = $func($functionArgs);

							$error = is_float($val) && (is_nan($val) || is_infinite($val));
							if ($error)
							{
								$this->setError(8, $item[0]);

								return null;
							}
							array_unshift($stack, $val);
					}
			}
		}
		if (count($stack) > 1)
		{
			$this->setError(7);

			return null;
		}

		return array_shift($stack);
	}

	private function toSingleValue($argument)
	{
		if (is_array($argument))
		{
			reset($argument);
			return current($argument);
		}

		return $argument;
	}
}
