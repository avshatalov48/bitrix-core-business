<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Im;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sender\Message;
use Bitrix\Sender\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class MessageIm
 * @package Bitrix\Sender\Integration\Im
 */
class MessageIm implements Message\iBase, Message\iMailable
{
	const CODE = self::CODE_IM;

	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

	/**
	 * MessageSms constructor.
	 */
	public function __construct()
	{
		$this->configuration = new Message\Configuration();
	}

	/**
	 * Get name.
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_IM_MESSAGE_NAME');
	}

	public function getCode()
	{
		return static::CODE;
	}

	public function getSupportedTransports()
	{
		return array(TransportIm::CODE);
	}

	protected function setConfigurationOptions()
	{
		$this->configuration->setArrayOptions(array(
			array(
				'type' => 'text',
				'code' => 'MESSAGE_TEXT',
				'name' => Loc::getMessage('SENDER_INTEGRATION_IM_MESSAGE_CONFIG_MESSAGE_TEXT'),
				'required' => true,
			),
		));
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
		if (!$this->configuration->hasOptions())
		{
			$this->setConfigurationOptions();
		}

		$configuration = $this->configuration;
		$this->configuration->setView(
			function () use ($configuration)
			{
				ob_start();
				$GLOBALS['APPLICATION']->IncludeComponent(
					'bitrix:sender.im.message',
					'',
					array(
						'INPUT_NAME' => '%INPUT_NAME_MESSAGE_TEXT%',
						'VALUE' => $configuration->get('MESSAGE_TEXT'),
					)
				);

				return ob_get_clean();
			}
		);

		Entity\Message::create()
			->setCode($this->getCode())
			->loadConfiguration($id, $this->configuration);

		return $this->configuration;
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
			->saveConfiguration($this->configuration);
	}

	/**
	 * Remove configuration.
	 *
	 * @param integer $id ID.
	 * @return bool
	 */
	public function removeConfiguration($id)
	{
		$result = Entity\Message::removeById($id);
		return $result->isSuccess();
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