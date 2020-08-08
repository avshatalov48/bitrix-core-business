<?php
namespace Bitrix\Main\Security\Mfa;

use Bitrix\Main\Config\Option;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Security\OtpException;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class TotpAlgorithm extends OtpAlgorithm
{
	const SYNC_WINDOW = 180;
	protected static $type = 'totp';

	protected $interval = 30;
	// ToDo: option here! May be just merge with HOTP window?
	protected $window = 2;
	protected $requireTwoCode = false;

	public function __construct()
	{
		$interval = (int) Option::get('security', 'totp_interval');
		if ($interval && $interval > 0)
			$this->interval = $interval;
	}

	/**
	 * @inheritDoc
	 */
	public function verify($input, $params = null, $time = null)
	{
		$input = (string) $input;

		if($params === null)
		{
			$params = '0:0';
		}

		if (!preg_match('#^\d+$#D', $input))
			throw new ArgumentOutOfRangeException('input', 'string with numbers');

		list($userOffset, $lastTimeCode) = explode(':', $params);
		$userOffset = (int) $userOffset;
		$lastTimeCode = (int) $lastTimeCode;

		if ($time === null)
			$timeCode = $this->timecode(time());
		else
			$timeCode = $this->timecode((int) $time);

		$checkOffsets = array();
		// First of all we must check input for provided offset
		$checkOffsets[] = $userOffset;
		if ($userOffset)
		{
			// If we failed on previous step and have user offset - try current time, may be user syncing time on device
			$checkOffsets[] = 0;
		}

		if ($this->window)
		{
			// Otherwise, try deal with clock drifting
			$checkOffsets = array_merge(
				$checkOffsets,
				range($userOffset - $this->window, $userOffset + $this->window)
			);
		}

		$isSuccess = false;
		$resultOffset = 0;
		$resultTimeCode = 0;

		foreach($checkOffsets as $offset)
		{
			$code = $timeCode + $offset;
			// Disallow authorization in the past. Must prevent replay attacks.
			if ($lastTimeCode && $code <= $lastTimeCode)
				continue;

			if ($this->isStringsEqual($input, $this->generateOTP($code)))
			{
				$isSuccess = true;
				$resultOffset = $offset;
				$resultTimeCode = $code;
				break;
			}
		}

		if ($isSuccess === true)
			return array($isSuccess, sprintf('%d:%d', $resultOffset, $resultTimeCode));

		return array($isSuccess, null);
	}

	/**
	 * @inheritDoc
	 */
	public function generateUri($label, array $opts = array())
	{
		$opts += array('period' => $this->getInterval());
		return parent::generateUri($label, $opts);
	}

	/**
	 * Make OTP counter from provided timestamp
	 *
	 * @param int $timestamp Timestamp.
	 * @return int
	 */
	public function timecode($timestamp)
	{
		return (int) ( (((int) $timestamp * 1000) / ($this->getInterval() * 1000)));
	}

	/**
	 * @param int $interval
	 * @return $this
	 */
	public function setInterval($interval)
	{
		$this->interval = (int)$interval;
		return $this;
	}
	/**
	 * Return used interval in counter generation
	 *
	 * @return int
	 */
	protected function getInterval()
	{
		return $this->interval;
	}

	/**
	 * @param int $window
	 * @return $this
	 */
	public function setWindow($window)
	{
		$this->window = (int)$window;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getSyncParameters($inputA, $inputB)
	{
		$offset = 0;
		$this->window = 0;

		$isSuccess = false;

		if (!$isSuccess)
		{
			// Before detect clock drift we must check current time :-)
			list($isSuccess,) = $this->verify($inputA, $offset);
		}

		if (!$isSuccess)
		{
			// Otherwise try to calculate resynchronization
			$offset = -self::SYNC_WINDOW;
			for($i = $offset; $i < self::SYNC_WINDOW; $i++)
			{
				list($isSuccess,) = $this->verify($inputA, $offset);
				if ($isSuccess)
				{
					break;
				}
				$offset++;
			}
		}

		if ($offset === self::SYNC_WINDOW)
			throw new OtpException('Cannot synchronize this secret key with the provided password values.');

		return sprintf('%d:%d', $offset, 0);
	}
}
