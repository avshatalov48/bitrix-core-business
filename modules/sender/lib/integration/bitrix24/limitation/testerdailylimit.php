<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Bitrix24\Limitation;

use Bitrix\Sender\Internals\Model;

/**
 * Class TesterDailyLimit
 * @package Bitrix\Sender\Integration\Bitrix24\Limitation
 */
class TesterDailyLimit extends DailyLimit
{
	/**
	 * Get current.
	 *
	 * @return integer
	 */
	public function getCurrent()
	{
		return Model\DailyCounterTable::getCurrentFieldValue('TEST_SENT_CNT');
	}

	/**
	 * Get limit.
	 *
	 * @return integer
	 */
	public function getLimit()
	{
		return intval(parent::getLimit() / 10);
	}

	/**
	 * Set limit.
	 *
	 * @param int $limit Limit.
	 * @return void
	 */
	public function setLimit($limit)
	{

	}

	/**
	 * Increment sent mails.
	 *
	 * @return void
	 */
	public static function increment()
	{
		Model\DailyCounterTable::incrementFieldValue('TEST_SENT_CNT');
	}
}