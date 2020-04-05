<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Stat;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\DateTime;

class Filter
{
	CONST PERIOD_WEEK = 7;
	CONST PERIOD_MONTH = 30;
	CONST PERIOD_MONTH_3 = 90;
	CONST PERIOD_MONTH_6 = 180;
	CONST PERIOD_MONTH_12 = 365;

	protected $values = array(
		'authorId' => null,
		'chainId' => null,
		'mailingId' => null,
		'postingId' => null,
		'periodFrom' => null,
		'periodTo' => null,
		'period' => null
	);

	public function __construct(array $values = array())
	{
		foreach ($values as $name => $value)
		{
			$this->set($name, $value);
		}
	}

	public function set($name, $value = null)
	{
		if (!array_key_exists($name, $this->values))
		{
			throw new ArgumentException("Unknown filter \"$name\"");
		}

		if ($value === 'all')
		{
			$value = null;
		}

		if ($name == 'period')
		{
			$this->setPeriod($value);
		}

		$this->values[$name] = $value;
	}

	public function get($name)
	{
		if (!array_key_exists($name, $this->values))
		{
			throw new ArgumentException("Unknown filter \"$name\"");
		}

		return $this->values[$name];
	}

	public function getNames()
	{
		return array_keys($this->values);
	}

	public function getMappedArray(array $map, array $filter = array())
	{
		foreach ($map as $name => $mappedName)
		{
			if (!array_key_exists($name, $this->values))
			{
				throw new ArgumentException("Unknown filter \"$name\"");
			}

			if (!$this->values[$name])
			{
				if (isset($filter[$mappedName]))
				{
					unset($filter[$mappedName]);
				}

				continue;
			}

			$filter[$mappedName] = $this->values[$name];
		}

		return $filter;
	}

	public function clear()
	{
		foreach ($this->values as $name => $value)
		{
			$this->values[$name] = null;
		}
	}

	protected function setPeriod($period = self::PERIOD_MONTH)
	{
		$date = new DateTime();
		$date->add('-' . $period . ' DAY');
		$this->set('periodFrom', $date);
		$this->set('periodTo');
	}
}

