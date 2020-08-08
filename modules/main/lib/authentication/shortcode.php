<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\Authentication;

use Bitrix\Main;
use Bitrix\Main\Security\Mfa;
use Bitrix\Main\Authentication\Internal\UserAuthCodeTable;

class ShortCode
{
	/** @var Context */
	protected $context;
	protected $type;
	/** @var Internal\EO_UserAuthCode */
	protected $code;
	protected $checkInterval = 300; //seconds, a half of the real time window
	protected $resendInterval = 60; //seconds

	/**
	 * ShortCode constructor.
	 * @param Context $context Contains userId
	 * @param string $type Currently 'email' only
	 */
	public function __construct(Context $context, $type = UserAuthCodeTable::TYPE_EMAIL)
	{
		$this->context = $context;
		$this->type = $type;

		if(!$this->load())
		{
			throw new Main\ObjectException("User probably not found: ".$context->getUserId());
		}
	}

	/**
	 * Generates a 6-number code.
	 * @return bool|string
	 */
	public function generate()
	{
		$totp = new Mfa\TotpAlgorithm();
		$totp->setInterval($this->checkInterval);
		$totp->setSecret($this->code->getOtpSecret());

		$timecode = $totp->timecode(time());
		$shortCode = $totp->generateOTP($timecode);

		return $shortCode;
	}

	/**
	 * Verifies the 6-number code.
	 * @param string $code
	 * @return Main\Result
	 */
	public function verify($code)
	{
		$result = new Main\Result();

		$attempts = (int)$this->code->getAttempts();

		if($attempts >= 3)
		{
			$result->addError(new Main\Error("Retry count exceeded.", "ERR_RETRY_COUNT"));
			return $result;
		}

		$totp = new Main\Security\Mfa\TotpAlgorithm();
		$totp->setInterval($this->checkInterval);
		$totp->setSecret($this->code->getOtpSecret());

		$otpResult = false;
		try
		{
			list($otpResult, ) = $totp->verify($code);
		}
		catch(Main\ArgumentException $e)
		{
		}

		if($otpResult)
		{
			$this->code->setDateSent(null);
			$this->code->setDateResent(null);
		}
		else
		{
			$result->addError(new Main\Error("Incorrect code.", "ERR_CONFIRM_CODE"));

			$this->code->setAttempts($attempts + 1);
		}

		$this->code->save();

		return $result;
	}

	/**
	 * Checks if previous dispatch time is outside the interval.
	 * @return Main\Result
	 */
	public function checkDateSent()
	{
		$result = new Main\Result();

		$resultData = [
			"checkInterval" => $this->checkInterval*2,
			"resendInterval" => $this->resendInterval,
		];

		//alowed only once in a interval
		if($this->code->getDateResent())
		{
			$currentDateTime = new Main\Type\DateTime();
			$interval = $currentDateTime->getTimestamp() - $this->code->getDateResent()->getTimestamp();

			if($interval < $this->resendInterval)
			{
				$resultData["secondsLeft"] = $this->resendInterval - $interval;
				$resultData["secondsPassed"] = $interval;
				$result->addError(new Main\Error("Timeout not expired yet."));
			}
		}

		$result->setData($resultData);

		return $result;
	}

	/**
	 * Saves last sent date.
	 * @return bool
	 */
	public function saveDateSent()
	{
		$currentDateTime = new Main\Type\DateTime();

		if($this->code->getDateSent())
		{
			if(($currentDateTime->getTimestamp() - $this->code->getDateSent()->getTimestamp()) > $this->checkInterval*2)
			{
				//reset attempts only for the new code (when time passes)
				$this->code->setAttempts(0);
				$this->code->setDateSent(null);
			}
		}

		if(!$this->code->getDateSent())
		{
			//first time only
			$this->code->setDateSent($currentDateTime);
		}
		$this->code->setDateResent($currentDateTime);

		$this->code->save();

		return true;
	}

	/**
	 * @return Main\EO_User
	 */
	public function getUser()
	{
		return $this->code->fillUser();
	}

	/**
	 * @param int $userId
	 */
	public static function deleteByUser($userId)
	{
		UserAuthCodeTable::deleteByFilter(["=USER_ID" => $userId]);
	}

	protected function load()
	{
		$userId = $this->context->getUserId();
		$primaryKey = [
			"USER_ID" => $userId,
			"CODE_TYPE" => $this->type,
		];

		$code = UserAuthCodeTable::getById($primaryKey)->fetchObject();

		if(!$code)
		{
			//first time for the user, should create a record
			$code = UserAuthCodeTable::createObject();
			$code->setUserId($userId);
			$code->setCodeType($this->type);

			$result = $code->save();
			if(!$result->isSuccess())
			{
				return false;
			}
		}

		$this->code = $code;

		return true;
	}
}
