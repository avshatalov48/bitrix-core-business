<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\MessageService\Sms;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Transport;
use Bitrix\Sender\Message;
use Bitrix\Sender\Recipient;

Loc::loadMessages(__FILE__);

/**
 * Class TransportSms
 * @package Bitrix\Sender\Integration\MessageService\Sms
 */
class TransportSms implements Transport\iBase, Transport\iLimitation
{
	const CODE = self::CODE_SMS;

	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

	/** @var Transport\CountLimiter[] $limiters Limiters. */
	protected $limiters;

	/** @var HttpClient $httpClient Http client. */
	protected $httpClient = array();

	public function __construct()
	{
		$this->configuration = new Message\Configuration();
	}

	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_SMS_TRANSPORT_NAME');
	}

	public function getCode()
	{
		return self::CODE;
	}

	/**
	 * Get supported recipient types.
	 *
	 * @return integer[]
	 */
	public function getSupportedRecipientTypes()
	{
		return array(Recipient\Type::PHONE);
	}

	public function loadConfiguration()
	{
		return $this->configuration;
	}

	public function saveConfiguration(Message\Configuration $configuration)
	{
		$this->configuration = $configuration;
	}

	public function start()
	{
		$clientOptions = array(
			'waitResponse' => false,
			'socketTimeout' => 5,
		);
		$this->httpClient = new HttpClient($clientOptions);
		$this->httpClient->setTimeout(5);
	}

	public function send(Message\Adapter $message)
	{
		$sender = $message->getConfiguration()->get('SENDER');
		list($senderId, $from) = explode(':', $sender);
		$authorId = $message->getConfiguration()->get('LETTER_CREATED_BY_ID');
		$text = $message->getConfiguration()->get('MESSAGE_TEXT');
		$text = $message->replaceFields($text);
		$to = $message->getTo();

		return Service::send($senderId, $from, $to, $text, $authorId);
	}

	public function end()
	{

	}

	/**
	 * Get limiters.
	 *
	 * @param Message\iBase $message Message.
	 * @return Transport\iLimiter[]
	 */
	public function getLimiters(Message\iBase $message = null)
	{
		if (!empty($this->limiters))
		{
			return $this->limiters;
		}

		/** @var MessageSms $message */
		$smsSender = null;
		if ($message)
		{
			if ($message instanceof Message\Adapter)
			{
				$smsSender = $message->getConfiguration()->getOption('SENDER')->getValue();
			}
			else
			{
				$smsSender = $message->getSmsSender();
			}
		}

		$this->limiters = [];
		$limitList = Service::getDailyLimits();
		$senderNames = Service::getSenderNames();
		foreach ($limitList as $limitSender => $limitData)
		{
			if ($smsSender && $smsSender !== $limitSender)
			{
				continue;
			}
			if (empty($limitData['limit']))
			{
				continue;
			}

			$this->limiters[] = Transport\CountLimiter::create()
				->withName('sms_per_day_' . $limitSender)
				->withCaption($senderNames[$limitSender])
				->withLimit($limitData['limit'])
				->withCurrent(
					function () use ($limitSender)
					{
						$limitList = Service::getDailyLimits();
						if (!isset($limitList[$limitSender]))
						{
							return 0;
						}

						if (!isset($limitList[$limitSender]['current']))
						{
							return 0;
						}

						return $limitList[$limitSender]['current'];
					}

				)
				->withUnit("1 " . Transport\iLimiter::DAYS)
				->setParameter('setupUri', Service::getLimitsUrl());
		}

		return $this->limiters;
	}
}