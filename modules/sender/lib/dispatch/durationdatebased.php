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
 * Class DurationDateBased
 * @package Bitrix\Sender\Dispatch
 */
class DurationDateBased
{
	/** @var Entity\Letter $letter Letter. */
	private $letter;

	/**
	 * Constructor.
	 *
	 * @param Entity\Letter $letter Letter.
	 */
	public function __construct(Entity\Letter $letter)
	{
		$this->letter = $letter;
	}

	/**
	 * Get interval in seconds.
	 *
	 * @param integer $count Count.
	 * @return integer
	 */
	public function getInterval($count)
	{
		$sent = $this->letter->getCounter()->getSent();
		if (!$sent)
		{
			return 0;
		}

		return ceil(($this->getElapsedInterval() / $sent) * $count);
	}

	/**
	 * Get elapsed interval in seconds.
	 *
	 * @return integer
	 */
	private function getElapsedInterval()
	{
		/** @var DateTime $start */
		$start = $this->letter->get('DATE_SEND');
		if (!$start)
		{
			return 0;
		}

		$now = new DateTime;
		return $now->getTimestamp() - $start->getTimestamp();
	}
}