<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Bitrix\Main\ArgumentException;

/**
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Range
 */
class Range
{
	protected int $start;
	protected int $end;

	public function __construct(?int $start, ?int $end, int $size)
	{
		if ($start === null && $end !== null)
		{
			// Returning requested suffix of the file
			$this->start = $size - $end;
			$this->end = $size - 1;
		}
		elseif ($start !== null && $end === null)
		{
			// Returning all bytes with offset
			$this->start = $start;
			$this->end = $size - 1;
		}
		else
		{
			$this->start = (int)$start;
			$this->end = (int)$end;
		}

		if ($this->start > $this->end || $this->start >= $size)
		{
			throw new ArgumentException('Requested Range Not Satisfiable');
		}

		if ($this->end >= $size)
		{
			$this->end = $size - 1;
		}
	}

	public function getStart(): int
	{
		return $this->start;
	}

	public function getEnd(): int
	{
		return $this->end;
	}

	/**
	 * @param string $header
	 * @param int $size
	 * @return Range[] | null
	 */
	public static function createFromString(string $header, int $size): ?array
	{
		if (str_contains($header, '='))
		{
			[$unit, $value] = explode("=", $header);
			if (strtolower(trim($unit)) === 'bytes')
			{
				$ranges = [];
				foreach (explode(',', $value) as $range)
				{
					if (str_contains($range, '-'))
					{
						[$start, $end] = explode('-', $range);

						if (!is_numeric($start))
						{
							$start = null;
						}
						if (!is_numeric($end))
						{
							$end = null;
						}

						try
						{
							$ranges[] = new Range($start, $end, $size);
						}
						catch (ArgumentException)
						{
							// all ranges must be correct
							return null;
						}
					}
				}

				return $ranges ?: null;
			}
		}

		return null;
	}
}
