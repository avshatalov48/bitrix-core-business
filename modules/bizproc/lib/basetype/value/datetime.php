<?php

namespace Bitrix\Bizproc\BaseType\Value;

use Bitrix\Main;

class DateTime extends Date
{
	public function __construct($dateFormatted = null, $offset = 0)
	{
		$offset = (int) $offset;

		if ($dateFormatted === null)
		{
			$this->timestamp = time();
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
				$datetime = new Main\Type\DateTime($dateFormatted);
				$this->checkYear($datetime);

				$this->timestamp = $datetime->getTimestamp() - $offset;
			}
			catch (Main\ObjectException $exception)
			{
				try
				{
					$this->timestamp = (new Main\Type\DateTime($dateFormatted, DATE_ISO8601))->getTimestamp() - $offset;
				}
				catch (Main\ObjectException $exception)
				{
					$this->timestamp = null;
				}
			}
		}

		$this->offset = $offset;
	}

	public function toSystemObject()
	{
		return Main\Type\DateTime::createFromTimestamp($this->getTimestamp());
	}

	public function getFormat()
	{
		return Main\Type\DateTime::getFormat();
	}
}