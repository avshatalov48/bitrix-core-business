<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

namespace Bitrix\Main\DB;

use Bitrix\Main\Application;

/**
 * Class SqlExpression
 *
 * @package Bitrix\Main\DB
 */
class SqlExpression
{
	/** @var string */
	protected $expression;

	/** @var array */
	protected $args = array();

	protected $pattern = '/([^\\\\]|^)(\?[#sifv]?)/';

	protected $i;

	/** @var  Connection */
	protected $connection;

	/**
	 * @param string $expression Sql expression.
	 * @param string,... $args Substitutes.
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function __construct()
	{
		$args = func_get_args();

		if (!isset($args[0]))
		{
			throw new \Bitrix\Main\ArgumentException('No pattern has been found for SqlExpression');
		}

		$this->expression = $args[0];

		for ($i = 1, $n = count($args); $i < $n; $i++)
		{
			$this->args[] = $args[$i];
		}
	}

	/**
	 * Returns $expression with replaced placeholders.
	 *
	 * @return string
	 */
	public function compile()
	{
		$this->i = -1;

		if (strpos($this->expression, '\\') === false)
		{
			// regular case
			return preg_replace_callback($this->pattern, array($this, 'execPlaceholders'), $this->expression);
		}
		else
		{
			// handle escaping \ and \\
			$parts = explode('\\\\', $this->expression);

			foreach ($parts as &$part)
			{
				if (!empty($part))
				{
					$part = preg_replace_callback($this->pattern, array($this, 'execPlaceholders'), $part);
				}
			}

			$parts = str_replace('\\?', '?', $parts);

			return implode('\\\\', $parts);
		}
	}

	/**
	 * Used by compile method to replace placeholders with values.
	 *
	 * @param array $matches Matches found by preg_replace.
	 *
	 * @return string
	 */
	protected function execPlaceholders($matches)
	{
		$sqlHelper = $this->getConnection()->getSqlHelper();

		$this->i++;

		$pre = $matches[1];
		$ph = $matches[2];

		if (array_key_exists($this->i, $this->args))
		{
			$value = $this->args[$this->i];

			if ($value === null && $ph !== '?#')
			{
				$value = 'NULL';
			}
			elseif ($ph == '?' || $ph == '?s')
			{
				$value = "'" . $sqlHelper->forSql($value) . "'";
			}
			elseif ($ph == '?#')
			{
				$value = $sqlHelper->quote($value);
			}
			elseif ($ph == '?v')
			{
				$value = $sqlHelper->values($value);
			}
			elseif ($ph == '?i')
			{
				$value = (int) $value;
			}
			elseif ($ph == '?f')
			{
				$value = (float) $value;
			}

			return $pre . $value;
		}

		return $matches[0];
	}

	public function __toString()
	{
		return $this->compile();
	}

	/**
	 * @return Connection
	 */
	public function getConnection()
	{
		if ($this->connection === null)
		{
			$this->connection = Application::getConnection();
		}

		return $this->connection;
	}

	/**
	 * @param Connection $connection
	 */
	public function setConnection($connection)
	{
		$this->connection = $connection;
	}
}
