<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Dispatch;

use Bitrix\Main\Localization\Loc;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class Duration
 * @package Bitrix\Sender\Dispatch
 */
class Duration
{
	/** @var Entity\Letter $letter Letter. */
	private $letter;

	/** @var DurationCountBased $durationCountBased Count based duration. */
	private $durationCountBased;

	/** @var DurationDateBased $durationDateBased Date based duration. */
	private $durationDateBased;

	/**
	 * Constructor.
	 *
	 * @param Entity\Letter $letter Letter.
	 */
	public function __construct(Entity\Letter $letter)
	{
		$this->letter = $letter;
		$this->durationCountBased = new DurationCountBased($letter->getMessage());
		$this->durationDateBased = new DurationDateBased($letter);
	}

	/**
	 * Get date.
	 *
	 * @return DateTime
	 */
	public function getDate()
	{
		$date = new DateTime;
		$date->add($this->getInterval() . ' seconds');
		return $date;
	}

	/**
	 * Get count.
	 *
	 * @return integer
	 */
	protected function getCount()
	{
		return $this->letter->getCounter()->getUnsent();
	}

	/**
	 * Get interval in seconds.
	 *
	 * @return integer
	 */
	public function getInterval()
	{
		$count = $this->getCount();
		$interval = $this->durationCountBased->getInterval($count);
		if (!$interval)
		{
			$interval = $this->durationDateBased->getInterval($count);
		}

		return $interval;
	}

	/**
	 * Get formatted interval.
	 *
	 * @return string
	 */
	public function getFormatted()
	{
		return $this->format($this->getDate());
	}

	/**
	 * Get formatted minimal interval.
	 *
	 * @return string
	 */
	public static function getFormattedMinimalInterval()
	{
		return Loc::getMessage('SENDER_DISPATCH_DURATION_LESS_HOUR');
	}

	/**
	 * Get formatted maximal interval.
	 *
	 * @return string
	 */
	public static function getFormattedMaximalInterval()
	{
		return Loc::getMessage('SENDER_DISPATCH_DURATION_MORE_3_DAYS');
	}

	/**
	 * Get minimal interval in seconds.
	 *
	 * @return integer
	 */
	public static function getMinimalInterval()
	{
		return 3600;
	}

	/**
	 * Get maximal interval in seconds.
	 *
	 * @return integer
	 */
	public static function getMaximalInterval()
	{
		return 3600 * 24 * 3;
	}

	/**
	 * Get warn interval in seconds.
	 *
	 * @return integer
	 */
	public static function getWarnInterval()
	{
		return self::getMinimalInterval() * 10;
	}

	/**
	 * Get formatted interval.
	 *
	 * @return string
	 */
	public function getFormattedInterval()
	{
		if ($this->getInterval() < self::getMinimalInterval())
		{
			return self::getFormattedMinimalInterval();
		}
		elseif ($this->getInterval() > self::getMaximalInterval())
		{
			return self::getFormattedMaximalInterval();
		}
		else
		{
			$formatted = \FormatDate('Hdiff', $this->getDate());
			if (mb_substr($formatted, 0, 1) === '-')
			{
				$formatted = mb_substr($formatted, 1);
			}

			return $formatted;
		}
	}

	/**
	 * Format date.
	 *
	 * @param DateTime $dateTime Date.
	 * @return string
	 */
	public function format(DateTime $dateTime = null)
	{
		if (!$dateTime)
		{
			return '';
		}

		return \FormatDate('d F, H:i', $dateTime);
	}
}