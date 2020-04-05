<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Sender\WebHook;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

use Bitrix\Sender\Message;
use Bitrix\Sender\Transport;
use Bitrix\Sender\Recipient;

Loc::loadMessages(__FILE__);

/**
 * Class TransportWebHook
 * @package Bitrix\Sender\Integration\Sender\WebHook
 */
class TransportWebHook implements Transport\iBase, Transport\iLimitation
{
	const CODE = self::CODE_WEB_HOOK;

	const MAX_BUFFER_SIZE = 200;

	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

	/** @var Transport\CountLimiter $limiter Limiter. */
	protected $limiter;

	/** @var HttpClient $httpClient Http client. */
	protected $httpClient = array();

	/** @var array $buffer Buffer. */
	protected $buffer = array(
		'uri' => null,
		'list' => array()
	);

	/**
	 * TransportWebHook constructor.
	 */
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
		return Loc::getMessage('SENDER_INTEGRATION_WEBHOOK_TRANSPORT_NAME');
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
		return array(Recipient\Type::EMAIL, Recipient\Type::PHONE);
	}

	/**
	 * Load configuration.
	 *
	 * @return Message\Configuration
	 */
	public function loadConfiguration()
	{
		return $this->configuration;
	}

	/**
	 * Save configuration.
	 *
	 * @param Message\Configuration $configuration Configuration.
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
			'waitResponse' => true,
			'socketTimeout' => 5,
		);
		$this->httpClient = new HttpClient($clientOptions);
		$this->httpClient->setTimeout(5);

		$this->resetBuffer();
	}

	/**
	 * Send.
	 *
	 * @param Message\Adapter $message Message.
	 *
	 * @return bool
	 */
	public function send(Message\Adapter $message)
	{
		$this->buffer['uri'] = $message->getConfiguration()->get('URI');
		$this->buffer['list'][$message->getRecipientType()][] = $message->getTo();

		$count = 0;
		$types = $this->getSupportedRecipientTypes();
		foreach ($types as $type)
		{
			if (!isset($this->buffer['list'][$type]))
			{
				continue;
			}

			$count += count($this->buffer['list'][$type]);
		}

		if ($count >= self::MAX_BUFFER_SIZE)
		{
			$this->flushBuffer();
		}

		return true;
	}

	/**
	 * End.
	 */
	public function end()
	{
		$this->flushBuffer();
	}

	protected function resetBuffer()
	{
		$this->buffer = array(
			'uri' => null,
			'list' => array()
		);

		$types = $this->getSupportedRecipientTypes();
		foreach ($types as $type)
		{
			$this->buffer['list'][$type] = array();
		}
	}

	protected function flushBuffer()
	{
		if (!$this->buffer['uri'])
		{
			return;
		}

		$count = count($this->buffer['list']);
		if ($count === 0)
		{
			return;
		}

		$this->httpClient->post($this->buffer['uri'], array(
			'list' => Json::encode($this->buffer['list']),
		));

		$this->getCountLimiter()->inc($count);
		$this->resetBuffer();
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
			$this->limiter = Transport\CountLimiter::create()
				->withName('web_hook')
				->withLimit(5000)
				->withUnit("1 " . Transport\iLimiter::DAYS)
				->withUnitName(Loc::getMessage('SENDER_INTEGRATION_WEBHOOK_TRANSPORT_LIMIT_PER_DAY'));
		}

		return $this->limiter;
	}
}