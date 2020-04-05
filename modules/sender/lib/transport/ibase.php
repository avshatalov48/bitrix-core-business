<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Transport;

use Bitrix\Sender\Message;

/**
 * Interface iBase
 * @package Bitrix\Sender\Transport
 */
interface iBase
{
	const CODE_MAIL = 'mail';
	const CODE_WEB_HOOK = 'web_hook';
	const CODE_SMS = 'sms';
	const CODE_IM = 'im';
	const CODE_CALL = 'call';
	const CODE_UNDEFINED = '';

	const EVENT_NAME = 'onSenderTransportList';

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode();

	/**
	 * Get supported recipient types.
	 *
	 * @return array
	 */
	public function getSupportedRecipientTypes();

	/**
	 * Load configuration.
	 *
	 * @return Message\Configuration
	 */
	public function loadConfiguration();

	/**
	 * Save configuration.
	 *
	 * @param Message\Configuration $configuration Configuration.
	 */
	public function saveConfiguration(Message\Configuration $configuration);

	/**
	 * Start.
	 */
	public function start();

	/**
	 * Send message.
	 *
	 * @param Message\Adapter $message Message.
	 *
	 * @return bool
	 */
	public function send(Message\Adapter $message);

	/**
	 * End.
	 */
	public function end();
}