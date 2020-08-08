<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Bitrix24\Limitation;

use Bitrix\Main\Config;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Message;
use Bitrix\Sender\Dispatch\Semantics;
use Bitrix\Sender\Entity\Letter;
use Bitrix\Sender\Integration\Im\Notification;
use Bitrix\Sender\Internals\Model;

Loc::loadMessages(__FILE__);

/**
 * Class Rating
 * @package Bitrix\Sender\Integration\Bitrix24\Limitation
 */
class Rating
{
	/** @var int $base Base count of mail. */
	protected static $base = 1000;

	/**
	 * Regulate: downgrade or block.
	 *
	 * @return void
	 */
	public static function regulate()
	{
		$ratio = self::getRatio();
		if ($ratio >= self::getBlockRate())
		{
			self::block();
		}
	}

	/**
	 * Calculate.
	 *
	 * @return void
	 */
	public static function calculate()
	{
		$updateTimestamp = (int) self::getParam('date_update', 0);
		$dateToday = new Date();
		if ($dateToday->getTimestamp() <= $updateTimestamp)
		{
			return;
		}
		self::setParam('date_update', $dateToday->getTimestamp());
		if (!$updateTimestamp)
		{
			return;
		}


		$yesterdayData = Model\DailyCounterTable::getRowByDate(1);
		if (!$yesterdayData)
		{
			return;
		}

		// don't upgrade if error count more than 10% of sent
		$countSent = (int) $yesterdayData['SENT_CNT'];
		$countError = (int) $yesterdayData['ERROR_CNT'];
		if ($countSent > 0 && ($countError / $countSent) > 0.1)
		{
			$countSent -= $countError;
		}

		$ratio = self::getRatio(1);
		if ($ratio >= self::getBlockRate())
		{
			self::block();
		}
		elseif ($ratio >= self::getDownRate())
		{
			self::downgrade();
		}
		elseif ($countSent >= DailyLimit::instance()->getLimit())
		{
			self::upgrade();
		}
	}

	/**
	 * Upgrade.
	 *
	 * @return void
	 */
	public static function upgrade()
	{
		$limit = DailyLimit::instance()->getLimit();
		$limit = $limit ?: self::getInitialLimit();
		$limit = $limit * self::getLimitMultiplier();
		if ($limit > self::getMaxLimit())
		{
			return;
		}

		DailyLimit::instance()->setLimit($limit);

		Notification::create()
			->withMessage(self::getNotifyText('upgraded'))
			->toAllAuthors()
			->send();
	}

	/**
	 * Downgrade.
	 *
	 * @param bool $isNotify Is notify.
	 * @return void
	 */
	public static function downgrade($isNotify = true)
	{
		DailyLimit::instance()->setLimit(self::getInitialLimit());

		if ($isNotify)
		{
			Notification::create()
				->withMessage(self::getNotifyText('downgraded'))
				->toAllAuthors()
				->send();
		}
	}

	/**
	 * Block.
	 *
	 * @return void
	 */
	public static function block()
	{
		$letters = Model\LetterTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=STATUS' => Semantics::getWorkStates(),
				'=MESSAGE_CODE' => Message\iBase::CODE_MAIL
			)
		));

		$letter = new Letter();
		foreach ($letters as $letterData)
		{
			$letter->load($letterData['ID']);
			if (!$letter->getId())
			{
				continue;
			}

			$state = $letter->getState();
			if ($state->canPause())
			{
				$state->pause();
			}
			else if ($state->canReady())
			{
				$state->ready();
			}
			else if ($state->canStop())
			{
				$state->stop();
			}
		}

		self::downgrade(false);
		self::setParam('blocked', 'Y');

		Notification::create()
			->withMessage(self::getNotifyText('blocked'))
			->toAllAuthors()
			->send();
	}

	/**
	 * Is blocked.
	 *
	 * @return bool
	 */
	public static function isBlocked()
	{
		return self::getParam('blocked', 'N') === 'Y';
	}

	/**
	 * Get downgrade rate.
	 *
	 * @return float
	 */
	public static function getDownRate()
	{
		return round(self::getParam('down_over_abuses', 8) / self::$base, 3);
	}

	/**
	 * Get downgrade rate.
	 *
	 * @return float
	 */
	public static function getBlockRate()
	{
		$abusesPer1000Mails = self::getParam('block_over_abuses', 20);
		return round($abusesPer1000Mails / self::$base, 3);
	}

	/**
	 * Get initial limit.
	 *
	 * @return int
	 */
	public static function getInitialLimit()
	{
		return (int) self::getParam('initial', 1000);
	}

	/**
	 * Get max limit.
	 *
	 * @return float
	 */
	public static function getMaxLimit()
	{
		return (int) self::getParam('max', 16000);
	}

	/**
	 * Get limit multiplier.
	 *
	 * @return float
	 */
	public static function getLimitMultiplier()
	{
		return (float) self::getParam('multiplier', 2);
	}

	/**
	 * Get ratio.
	 *
	 * @param integer $daysLeft Days left.
	 * @return float
	 */
	protected static function getRatio($daysLeft = 0)
	{
		$result = Model\DailyCounterTable::getRowByDate($daysLeft);
		$sentCount = $result ? (int) $result['SENT_CNT'] - (int) $result['ERROR_CNT'] : 0;
		$abuseCount = $result ? $result['ABUSE_CNT'] : 0;
		if (!$sentCount || $sentCount < 200 || !$abuseCount)
		{
			return 0;
		}

		return round($abuseCount / $sentCount, 3);
	}

	/**
	 * Get parameter.
	 *
	 * @param string $name Name.
	 * @param mixed $defaultValue Default value.
	 * @return mixed
	 */
	protected static function getParam($name, $defaultValue = 0)
	{
		return Config\Option::get('sender', "~r_limit_$name", $defaultValue);
	}

	/**
	 * Set parameter.
	 *
	 * @param string $name Name.
	 * @param mixed $value Value.
	 * @return void
	 */
	protected static function setParam($name, $value)
	{
		Config\Option::set('sender', "~r_limit_$name", $value);
	}

	/**
	 * Set notify text.
	 *
	 * @param string $code Code.
	 * @return string
	 */
	public static function getNotifyText($code)
	{
		$code = mb_strtoupper($code);
		return Loc::getMessage("SENDER_INTEGRATION_BITRIX24_RATING_{$code}1");
	}
}