<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\MessageService\Sms;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sender\Message;
use Bitrix\Sender\Entity;
use Bitrix\Main\Config\Option;
use Bitrix\Sender\Transport\TimeLimiter;

Loc::loadMessages(__FILE__);

/**
 * Class MessageSms
 * @package Bitrix\Sender\Integration\MessageService\Sms
 */
class MessageSms implements Message\iBase, Message\iMailable
{
	const CODE = self::CODE_SMS;

	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

	/** @var integer $configurationId Configuration ID. */
	protected $configurationId;

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
		return Loc::getMessage('SENDER_INTEGRATION_SMS_MESSAGE_NAME');
	}

	public function getCode()
	{
		return static::CODE;
	}

	public function getSupportedTransports()
	{
		return array(TransportSms::CODE);
	}

	protected function setConfigurationOptions()
	{
		if ($this->configuration->hasOptions())
		{
			return;
		}

		$this->configuration->setArrayOptions(array(
			array(
				'type' => 'string',
				'code' => 'SENDER',
				'name' => Loc::getMessage('SENDER_INTEGRATION_SMS_MESSAGE_CONFIG_SENDER'),
				'required' => true,
				'show_in_list' => true,
				'readonly_view' => function($value)
				{
					return Service::getFormattedOutputNumber($value);
				},
			),
			array(
				'type' => 'text',
				'code' => 'MESSAGE_TEXT',
				'name' => Loc::getMessage('SENDER_INTEGRATION_SMS_MESSAGE_CONFIG_MESSAGE_TEXT'),
				'required' => true,
			),
		));

		TimeLimiter::prepareMessageConfiguration($this->configuration);
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
		$this->setConfigurationOptions();
		Entity\Message::create()
			->setCode($this->getCode())
			->loadConfiguration($id, $this->configuration);

		/** @var \CAllMain {$GLOBALS['APPLICATION']} */
		$senderOption = $this->configuration->getOption('SENDER');
		if ($senderOption)
		{
			$senderOption->setView(
				function () use ($senderOption)
				{
					ob_start();
					$GLOBALS['APPLICATION']->includeComponent(
						"bitrix:sender.sms.sender",
						".default",
						array(
							"INPUT_NAME" => "%INPUT_NAME%",
							"SENDER" => $senderOption->getValue()
						)
					);

					return ob_get_clean();
				}
			);
		}

		$textOption = $this->configuration->getOption('MESSAGE_TEXT');
		if ($textOption)
		{
			$textOption->setView(
				function ()
				{
					ob_start();
					$GLOBALS['APPLICATION']->includeComponent(
						"bitrix:sender.sms.text.editor",
						".default",
						array(
							"INPUT_NAME" => "%INPUT_NAME%",
							"VALUE" => "%INPUT_VALUE%",
						)
					);

					return ob_get_clean();
				}
			);
		}
		TimeLimiter::prepareMessageConfigurationView($this->configuration);

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

	/**
	 * Get sms sender.
	 *
	 * @return string
	 */
	public function getSmsSender()
	{
		return $this->configuration->getOption('SENDER')->getValue();
	}
}