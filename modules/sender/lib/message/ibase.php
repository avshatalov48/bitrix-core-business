<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Message;

/**
 * Interface iMessage
 * @package Bitrix\Sender\Message
 */
interface iBase
{
	const CODE_MAIL = 'mail';
	const CODE_WEB_HOOK = 'web_hook';
	const CODE_SMS = 'sms';
	const CODE_IM = 'im';
	const CODE_CALL = 'call';
	const CODE_UNDEFINED = '';
	const EVENT_NAME = 'onSenderMessageList';

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
	public function getSupportedTransports();

	/**
	 * Load configuration.
	 *
	 * @param integer|string|null $id ID.
	 * @return Configuration
	 */
	public function loadConfiguration($id = null);

	/**
	 * Save configuration.
	 *
	 * @param Configuration $configuration
	 * @return Result|null
	 */
	public function saveConfiguration(Configuration $configuration);

	/**
	 * Copy configuration.
	 *
	 * @param integer|string|null $id ID.
	 * @return Result|null
	 */
	public function copyConfiguration($id);
}