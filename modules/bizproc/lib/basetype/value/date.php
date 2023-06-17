<?php

namespace Bitrix\Bizproc\BaseType\Value;

use Bitrix\Main;

class Date implements \JsonSerializable
{
	protected $timestamp;
	protected $offset;

	const SERIALIZED_PATTERN = '#(.+)\s\[([0-9\-]+)\]#i';

	public function __construct($dateFormatted = null, $offset = 0)
	{
		$offset = (int) $offset;

		if ($dateFormatted === null)
		{
			$this->timestamp = (new Main\Type\Date())->getTimestamp();
		}
		elseif (is_numeric($dateFormatted))
		{
			$this->timestamp = (int) $dateFormatted;
		}
		else
		{
			if (preg_match(static::SERIALIZED_PATTERN, $dateFormatted, $matches))
			{
				$dateFormatted = $matches[1];
				$offset = (int) $matches[2];
			}

			try
			{
				$date = new Main\Type\Date($dateFormatted);
				$this->checkYear($date);

				$this->timestamp = $date->getTimestamp() - $offset;
			}
			catch (Main\ObjectException $exception)
			{
				try
				{
					$this->timestamp = (new Main\Type\Date($dateFormatted, DATE_ISO8601))->getTimestamp() - $offset;
				}
				catch (Main\ObjectException $exception)
				{
					$this->timestamp = null;
				}
			}
		}

		$this->offset = $offset;
	}

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function getOffset()
	{
		return $this->offset;
	}

	public function __toString()
	{
		return date($this->getFormat(), $this->getTimestamp() + $this->offset);
	}

	public static function fromSystemObject(Main\Type\Date $date)
	{
		return new static($date->getTimestamp());
	}

	public function toSystemObject()
	{
		return Main\Type\Date::createFromTimestamp($this->getTimestamp() + $this->offset);
	}

	public function serialize()
	{
		return sprintf('%s [%d]', $this->__toString(), $this->offset);
	}

	public static function isSerialized($dateString)
	{
		if (is_string($dateString) && preg_match(static::SERIALIZED_PATTERN, $dateString))
		{
			return true;
		}
		return false;
	}

	public function getFormat()
	{
		return Main\Type\Date::getFormat();
	}

	protected function checkYear(Main\Type\Date $date)
	{
		if ($date->getTimestamp() < 0)
		{
			$y = (int) $date->format('Y');
			$m = (int) $date->format('m');
			$d = (int) $date->format('d');
			if (0 <= $y && $y <= 69)
			{
				$y += 2000;
				$date->setDate($y, $m, $d);
			}
			elseif (70 <= $y && $y <= 100)
			{
				$y += 1900;
				$date->setDate($y, $m, $d);
			}
		}
	}

	public function jsonSerialize()
	{
		return $this->serialize();
	}
}