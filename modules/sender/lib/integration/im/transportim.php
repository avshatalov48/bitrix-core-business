<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Im;

use Bitrix\Sender\Transport;
use Bitrix\Sender\Message;
use Bitrix\Sender\Recipient;

/**
 * Class TransportIm
 * @package Bitrix\Sender\Integration\Im
 */
class TransportIm implements Transport\iBase
{
	const CODE = self::CODE_IM;

	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

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
		return 'Im';
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
		return array(Recipient\Type::IM);
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

	public function saveConfiguration(Message\Configuration $configuration)
	{
		$this->configuration = $configuration;
	}

	public function start()
	{

	}

	public function send(Message\Adapter $message)
	{
		$to = $message->getTo();
		$text = $message->getConfiguration()->get('MESSAGE_TEXT');
		$text = $message->replaceFields($text);

		return Service::send($to, $text);
	}

	public function end()
	{

	}
}