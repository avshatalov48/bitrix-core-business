<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Dispatch;

use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Message;
use Bitrix\Sender\Transport;

Loc::loadMessages(__FILE__);

/**
 * Class DurationCountBased
 * @package Bitrix\Sender\Dispatch
 */
class DurationCountBased
{
	/** @var Message\Adapter $message Message. */
	private $message;

	/** @var integer $count Count. */
	private $count;

	/**
	 * Create instance.
	 *
	 * @param Message\Adapter $message Message.
	 *
	 * @return static
	 */
	public static function create(Message\Adapter $message)
	{
		return new static($message);
	}

	/**
	 * Constructor.
	 *
	 * @param Message\Adapter $message Message.
	 */
	public function __construct(Message\Adapter $message)
	{
		$this->message = $message;
	}

	/**
	 * Get interval in seconds.
	 *
	 * @param integer $count Count.
	 * @return integer
	 */
	public function getInterval($count)
	{
		if (!$count)
		{
			return 0;
		}

		$this->count = $count;
		/** @var Transport\iLimiter $limiter Limiter. */
		$limiter = current($this->getLimiters());
		if (!$limiter)
		{
			return 0;
		}

		$limit = $limiter->getLimit();
		if (!$limit)
		{
			return 0;
		}

		$timeouts = $this->getLimitTimeouts($limiter);
		$unit = $this->getLimitUnit($limiter);

		if ($timeouts)
		{
			$count = $this->count % ($limit * $timeouts);
		}
		else
		{
			$count = $this->count;
		}

		return ($timeouts * $unit) + ($count * $this->message->getSendDuration());
	}

	/**
	 * Get interval in seconds.
	 *
	 * @param integer $count Count.
	 * @return integer
	 */
	public function getIntervalDefault($count)
	{
		$interval = $this->getInterval($count);
		if ($interval)
		{
			return $interval;
		}

		return ceil(0.01 * $count);
	}

	protected function getLimitUnit(Transport\iLimiter $limiter)
	{
		return Transport\CountLimiter::getUnitInterval($limiter->getUnit());
	}

	protected function getLimitTimeouts(Transport\iLimiter $limiter)
	{
		$count = $this->count;
		$limit = $limiter->getLimit();
		$count -= $limit - $limiter->getCurrent();
		if (!$count || !$limit)
		{
			return 0;
		}

		return intval($count / $limit);
	}

	protected function getLimiters()
	{
		$transport = $this->message->getTransport();
		if (!$transport)
		{
			return array();
		}

		return $transport->getLimiters($this->message);
	}
}