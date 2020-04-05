<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\VoxImplant;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;

use Bitrix\Sender\Message;
use Bitrix\Sender\Transport;
use Bitrix\Sender\Recipient;

Loc::loadMessages(__FILE__);

/**
 * Class TransportCall
 * @package Bitrix\Sender\Integration\VoxImplant
 */
class TransportCall implements Transport\iBase, Transport\iDuration, Transport\iLimitation
{
	const CODE = self::CODE_CALL;

	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

	/** @var Transport\CountLimiter $limiter Limiter. */
	protected $limiter;

	/** @var HttpClient $httpClient Http client. */
	protected $httpClient = array();

	public function __construct()
	{
		$this->configuration = new Message\Configuration();
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_CALL_TRANSPORT_NAME');
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
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

	/**
	 * Get configuration.
	 *
	 * @return string
	 */
	public function loadConfiguration()
	{
		return $this->configuration;
	}

	/**
	 * Save configuration.
	 *
	 * @param Message\Configuration $configuration
	 */
	public function saveConfiguration(Message\Configuration $configuration)
	{
		$this->configuration = $configuration;
	}

	/**
	 * Start.
	 */
	public function start()
	{
		$clientOptions = array(
			'waitResponse' => false,
			'socketTimeout' => 5,
		);
		$this->httpClient = new HttpClient($clientOptions);
		$this->httpClient->setTimeout(1);
	}

	/**
	 * Send.
	 *
	 * @param Message\Adapter $message
	 *
	 * @return bool
	 */
	public function send(Message\Adapter $message)
	{
		$outputNumber = $message->getConfiguration()->get('OUTPUT_NUMBER');
		$number = $message->getTo();
		$text = $message->getConfiguration()->get('MESSAGE_TEXT');
		$text = $message->replaceFields($text);
		$voiceLanguage = $message->getConfiguration()->get('VOICE_LANGUAGE');
		$voiceSpeed = $message->getConfiguration()->get('VOICE_SPEED');
		$voiceVolume = $message->getConfiguration()->get('VOICE_VOLUME');

		$callId = Service::send(
			$outputNumber,
			$number,
			$text,
			$voiceLanguage,
			$voiceSpeed,
			$voiceVolume
		);

		if ($callId && $message->getRecipientId())
		{
			CallLogTable::add(array(
				'CALL_ID' => $callId,
				'RECIPIENT_ID' => $message->getRecipientId()
			));
		}

		return !!$callId;
	}

	/**
	 * End.
	 */
	public function end()
	{

	}

	/**
	 * Get send duration in seconds.
	 * Calc: length(message text based) + magic(connection time) / limit(because calls is parallel).
	 *
	 * @param Message\Adapter|null $message Message.
	 *
	 * @return float
	 */
	public function getDuration(Message\Adapter $message = null)
	{
		$messageText = $message->getConfiguration()->get('MESSAGE_TEXT');
		$voiceSpeed = $message->getConfiguration()->get('VOICE_SPEED');
		$length = SpeechRate::create()
			->withSpeed($voiceSpeed)
			->withText($messageText)
			->getDuration();

		$length = $length ?: 20;
		$magic = 5;
		$limit = $this->getCountLimiter()->getLimit();

		return round(($length + $magic) / $limit);
	}

	/**
	 * Get limiters.
	 *
	 * @param Message\iBase $message Message.
	 * @return Transport\iLimiter[]
	 */
	public function getLimiters(Message\iBase $message = null)
	{
		return array(
			$this->getCountLimiter()
		);
	}

	protected function getCountLimiter()
	{
		if ($this->limiter === null)
		{
			$this->limiter = new Limiter();
		}

		return $this->limiter;
	}
}