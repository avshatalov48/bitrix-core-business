<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Sender\WebHook;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sender\Message;
use Bitrix\Sender\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class MessageWebHook
 * @package Bitrix\Sender\Integration\Sender\WebHook
 */
class MessageWebHook implements Message\iBase
{
	const CODE = self::CODE_WEB_HOOK;

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_WEBHOOK_MESSAGE_NAME');
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return static::CODE;
	}

	/**
	 * Get supported transports.
	 *
	 * @return array
	 */
	public function getSupportedTransports()
	{
		return array(TransportWebHook::CODE);
	}

	/**
	 * Load configuration.
	 *
	 * @param integer|null $id ID.
	 *
	 * @return Message\Configuration
	 */
	public function loadConfiguration($id = null)
	{
		$configuration = new Message\Configuration();
		$configuration->setArrayOptions(array(
			array(
				'type' => 'string',
				'code' => 'URI',
				'name' => Loc::getMessage('SENDER_INTEGRATION_WEBHOOK_MESSAGE_CONFIG_URI'),
				'required' => true,
			),
		));

		return Entity\Message::create()
			->setCode($this->getCode())
			->loadConfiguration($id, $configuration);
	}

	/**
	 * Save configuration.
	 *
	 * @param Message\Configuration $configuration Configuration.
	 *
	 * @return Result
	 */
	public function saveConfiguration(Message\Configuration $configuration)
	{
		return Entity\Message::create()
			->setCode($this->getCode())
			->saveConfiguration($configuration);
	}

	/**
	 * Copy configuration.
	 *
	 * @param integer|string|null $id ID.
	 * @return Result|null
	 */
	public function copyConfiguration($id)
	{
		return Entity\Message::create()
			->setCode($this->getCode())
			->copyConfiguration($id);
	}
}