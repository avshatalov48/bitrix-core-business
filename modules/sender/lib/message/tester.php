<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Message;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Config\Option;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;

use Bitrix\Sender\Entity;
use Bitrix\Sender\Posting;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Message;
use Bitrix\Sender\Security;
use Bitrix\Sender\Integration;

Loc::loadMessages(__FILE__);

class Tester
{
	const MAX_LAST_CODES = 6;
	const MAX_SEND_CODES = 15;

	/** @var Adapter $message Message. */
	protected $message;

	/** @var string $userOptionLastCodesName User option last codes name. */
	protected static $userOptionLastCodesName = 'last_codes';


	/**
	 * Checker constructor.
	 *
	 * @param Adapter $message Message.
	 */
	public function __construct(Adapter $message)
	{
		return $this->message = $message;
	}

	/**
	 * Is support.
	 *
	 * @return bool
	 */
	public function isSupport()
	{
		$isSupport = in_array(
			$this->message->getCode(),
			array(
				Adapter::CODE_MAIL,
				Adapter::CODE_SMS,
				Adapter::CODE_CALL,
				Adapter::CODE_AUDIO_CALL,
				//Message::CODE_WEB_HOOK,
			)
		);

		if ($isSupport)
		{
			$isSupport = $this->getRecipientType() !== null;
		}

		return $isSupport;
	}

	/**
	 * Get recipient type.
	 *
	 * @return int|null
	 */
	public function getRecipientType()
	{
		static $type = false;
		if ($type === false)
		{
			$types = $this->message->getSupportedRecipientTypes();

			$type = current($types);
			$type = $type ?: null;
		}

		return $type;
	}

	/**
	 * Get default code.
	 *
	 * @return null|string
	 */
	protected function getDefaultCode()
	{
		$code = null;
		switch ($this->getRecipientType())
		{
			case Recipient\Type::EMAIL:
				if (!is_object($GLOBALS['USER']))
				{
					return null;
				}

				$code = $GLOBALS['USER']->getEmail();
				break;
			case Recipient\Type::PHONE:
				if (!is_object($GLOBALS['USER']))
				{
					return null;
				}

				$u = UserTable::getRowById($GLOBALS['USER']->getID());
				$code = $u['PERSONAL_MOBILE'] ?: $u['WORK_PHONE'] ?: $u['PERSONAL_PHONE'] ?: null;
				break;
		}

		return Recipient\Normalizer::normalize($code, $this->getRecipientType());
	}


	/**
	 * @return string
	 */
	protected function getUserOptionLastCodesName()
	{
		return self::$userOptionLastCodesName . '_' . Recipient\Type::getCode($this->getRecipientType());
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected function getEmailToMeList()
	{
		$addressToList = [];
		$email = Option::get('sender', 'address_send_to_me');
		if(!empty($email))
		{
			$addressToList = explode(',', $email);
			$addressToList = array_unique($addressToList);
			\TrimArr($addressToList, true);
		}

		return $addressToList;
	}

	/**
	 * Get last codes.
	 *
	 * @return array
	 */
	public function getLastCodes()
	{
		$codes = \CUserOptions::getOption('sender', $this->getUserOptionLastCodesName(), array());
		$codes = is_array($codes) ? $codes : array();
		$codes = $this->prepareCodes($codes);
		$code = $this->getDefaultCode();
		if ($code && !in_array($code, $codes))
		{
			$codes = $this->cutCodes($codes, true);
			$codes[] = $code;
		}

		$codes = $this->prepareCodes(
			array_merge($codes, $this->getEmailToMeList()),
			false
		);

		return $codes;
	}

	/**
	 * Set last codes.
	 *
	 * @param array $list Codes.
	 * @return void
	 */
	protected function setLastCodes(array $list)
	{
		\CUserOptions::setOption(
			'sender',
			$this->getUserOptionLastCodesName(),
			$this->prepareCodes($list)
		);
	}

	/**
	 * Add last code.
	 *
	 * @param string $code Code.
	 * @return bool
	 */
	protected function addLastCode($code)
	{
		$code = Recipient\Normalizer::normalize((string) $code, $this->getRecipientType());
		if (!$code)
		{
			return false;
		}

		$this->setLastCodes(array_merge(array($code), $this->getLastCodes()));

		return true;
	}

	/**
	 * Prepare codes.
	 *
	 * @param array $codes Codes.
	 * @param bool $isRemoveLast Is remove last item.
	 * @return array
	 */
	protected function cutCodes(array $codes, $isRemoveLast = false)
	{
		$length = (int) Option::get('sender', 'max_last_codes', 0);
		$length = $length > 0 ? $length : self::MAX_LAST_CODES;
		if ($isRemoveLast)
		{
			$length -= 1;
		}
		return array_slice($codes, 0, $length);
	}

	/**
	 * Prepare codes.
	 *
	 * @param array $codes Codes.
	 * @param bool $doCut Do cut.
	 * @return array
	 */
	protected function prepareCodes(array $codes, $doCut = true)
	{
		$result = array();
		foreach ($codes as $code)
		{
			$code = Recipient\Normalizer::normalize((string) $code, $this->getRecipientType());
			if (!$code)
			{
				continue;
			}

			$result[] = $code;
		}


		$result = array_unique($result);
		if ($doCut)
		{
			$result = $this->cutCodes($result);
		}

		return $result;
	}

	/**
	 * Send test message to recipients.
	 *
	 * @param array $codes Recipient codes.
	 * @param array $parameters Parameters.
	 * @return Result
	 */
	public function send(array $codes, array $parameters)
	{
		$result = new Result();
		if (!$this->isSupport())
		{
			$result->addError(new Error("Testing not supported."));
			return $result;
		}

		// agreement accept check
		if(!Security\User::current()->isAgreementAccepted())
		{
			$result->addError(new Error(Security\Agreement::getErrorText(), 'NEED_ACCEPT_AGREEMENT'));
			return $result;
		}

		$campaignId = isset($parameters['CAMPAIGN_ID']) ? $parameters['CAMPAIGN_ID'] : Entity\Campaign::getDefaultId(SITE_ID);
		$name = isset($parameters['NAME']) ? $parameters['NAME'] : null;
		$name = $name ?: $GLOBALS['USER']->getFirstName();
		$userId = isset($parameters['USER_ID']) ? $parameters['USER_ID'] : null;
		$userId = $userId ?: $GLOBALS['USER']->getID();
		$fields = isset($parameters['FIELDS']) ? $parameters['FIELDS'] : array();

		$this->message->getTransport()->start();

		$count = 0;
		foreach ($codes as $code)
		{
			if (self::MAX_SEND_CODES && $count++ >= self::MAX_SEND_CODES)
			{
				$result->addError(new Error(Loc::getMessage('SENDER_MESSAGE_TESTER_ERROR_MAX_COUNT', ['%count%' => self::MAX_SEND_CODES])));
				return $result;
			}

			if ($this->message->getTransport()->isLimitsExceeded($this->message))
			{
				$result->addError(new Error(Loc::getMessage('SENDER_MESSAGE_TESTER_ERROR_LIMIT_EXCEEDED', array('%name%' => $code))));
				return $result;
			}

			if (Integration\Bitrix24\Service::isCloud())
			{
				$testerDailyLimit = Integration\Bitrix24\Limitation\TesterDailyLimit::instance();
				if ($testerDailyLimit->getCurrent() >= $testerDailyLimit->getLimit())
				{
					$result->addError(new Error(Loc::getMessage('SENDER_MESSAGE_TESTER_ERROR_LIMIT_EXCEEDED', array('%name%' => $code))));
					return $result;
				}
			}

			$type = Recipient\Type::detect($code);
			if ($type)
			{
				$code = Recipient\Normalizer::normalize($code, $type);
			}
			if (!$type || !$code)
			{
				$result->addError(new Error(Loc::getMessage('SENDER_MESSAGE_TESTER_ERROR_WRONG_RECIPIENT', array('%name%' => $code))));
				continue;
			}

			$recipient = array(
				'ID' => 0,
				'CAMPAIGN_ID' => $campaignId,
				'CONTACT_CODE' => $code,
				'CONTACT_TYPE' => $type,
				'NAME' => $name,
				'USER_ID' => $userId,
				'FIELDS' => $fields,
			);

			Posting\Sender::applyRecipientToMessage($this->message, $recipient, true);
			try
			{
				$sendResult = $this->message->send();
				if (!$sendResult && $result->isSuccess())
				{
					$to = $this->message->getRecipientCode();
					$result->addError(new Error(Loc::getMessage('SENDER_MESSAGE_TESTER_ERROR_SENT', array('%name%' => $to))));
				}

				if ($sendResult)
				{
					$this->addLastCode($code);
					if (Integration\Bitrix24\Service::isCloud() && $this->message->getCode() === Message\iBase::CODE_MAIL)
					{
						Integration\Bitrix24\Limitation\DailyLimit::increment();
						Integration\Bitrix24\Limitation\TesterDailyLimit::increment();
					}
				}
			}
			catch(SystemException $e)
			{
				$result->addError(new Error($e->getMessage()));
				break;
			}
		}
		$this->message->getTransport()->end();

		return $result;
	}
}