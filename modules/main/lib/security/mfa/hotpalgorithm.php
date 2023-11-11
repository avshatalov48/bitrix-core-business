<?php

namespace Bitrix\Main\Security\Mfa;

use Bitrix\Main\Config\Option;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Security\OtpException;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class HotpAlgorithm extends OtpAlgorithm
{
	const SYNC_WINDOW = 15000;
	protected static $type = 'hotp';
	protected $window = 10;

	public function __construct()
	{
		$window = (int)Option::get('security', 'hotp_user_window', 10);
		if ($window && $window > 0)
		{
			$this->window = $window;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function verify($input, $params = null)
	{
		$input = (string)$input;

		if (!preg_match('#^\d+$#D', $input))
		{
			throw new ArgumentOutOfRangeException('input', 'string with numbers');
		}

		$counter = (int)$params;
		$result = false;
		$window = $this->window;
		while ($window--)
		{
			if ($this->isStringsEqual($input, $this->generateOTP($counter)))
			{
				$result = true;
				break;
			}
			$counter++;
		}

		if ($result === true)
		{
			return [true, $counter + 1];
		}

		return [false, null];
	}

	/**
	 * @inheritDoc
	 */
	public function generateUri($label, array $opts = [])
	{
		$opts += ['counter' => 1];
		return parent::generateUri($label, $opts);
	}

	/**
	 * @inheritDoc
	 */
	public function getSyncParameters($inputA, $inputB)
	{
		$counter = 0;
		$this->window = 1;
		for ($i = 0; $i < self::SYNC_WINDOW; $i++)
		{
			[$verifyA,] = $this->verify($inputA, $counter);
			[$verifyB,] = $this->verify($inputB, $counter + 1);
			$counter++;
			if ($verifyA && $verifyB)
			{
				break;
			}
		}

		if ($i === self::SYNC_WINDOW)
		{
			throw new OtpException('Cannot synchronize this secret key with the provided password values.');
		}

		return $counter;
	}
}
