<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\VoxImplant;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sender\Message;
use Bitrix\Sender\Entity;

use Bitrix\Voximplant\Tts;

Loc::loadMessages(__FILE__);

/**
 * Class MessageCall
 * @package Bitrix\Sender\Integration\VoxImplant
 */
class MessageCall implements Message\iBase, Message\iMailable
{
	const CODE = self::CODE_CALL;

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
		return Loc::getMessage('SENDER_INTEGRATION_CALL_MESSAGE_NAME');
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
		return array(TransportCall::CODE);
	}

	/**
	 * Set configuration options
	 * @return void
	 */
	protected function setConfigurationOptions()
	{
		if ($this->configuration->hasOptions())
		{
			return;
		}

		$this->configuration->setArrayOptions(array(
			array(
				'type' => 'list',
				'code' => 'OUTPUT_NUMBER',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CALL_MESSAGE_CONFIG_OUTPUT_NUMBER'),
				'items' => \CVoxImplantConfig::GetPortalNumbers(false),
				'view' => function ()
				{
					/** @var \CAllMain {$GLOBALS['APPLICATION']} */
					ob_start();
					$GLOBALS['APPLICATION']->includeComponent(
						"bitrix:sender.call.number", "",
						array(
							"INPUT_NAME" => "%INPUT_NAME%",
							"VALUE" => "%INPUT_VALUE%",
							"MESSAGE_TYPE" => $this->getCode()
						)
					);
					return ob_get_clean();
				},
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
				'name' => Loc::getMessage('SENDER_INTEGRATION_CALL_MESSAGE_CONFIG_MESSAGE_TEXT'),
				'required' => true,
			),
			array(
				'type' => 'list',
				'code' => 'VOICE_LANGUAGE',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CALL_MESSAGE_CONFIG_VOICE_LANGUAGE'),
				'items' => Tts\Language::getList(),
				'required' => true,
				'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
			),
			array(
				'type' => 'list',
				'code' => 'VOICE_SPEED',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CALL_MESSAGE_CONFIG_VOICE_SPEED'),
				'items' => Tts\Speed::getList(),
				'required' => true,
				'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
			),
			array(
				'type' => 'list',
				'code' => 'VOICE_VOLUME',
				'name' => Loc::getMessage('SENDER_INTEGRATION_CALL_MESSAGE_CONFIG_VOICE_VOLUME'),
				'items' => Tts\Volume::getList(),
				'required' => true,
				'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
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
		$this->setConfigurationOptions();
		Entity\Message::create()
			->setCode($this->getCode())
			->loadConfiguration($id, $this->configuration);

		$defaultValues = array(
			'VOICE_VOLUME' => Tts\Volume::getDefault(),
			'VOICE_SPEED' => Tts\Speed::getDefault(),
			'VOICE_LANGUAGE' => Tts\Language::getDefaultVoice(LANGUAGE_ID),
		);
		foreach ($defaultValues as $key => $value)
		{
			$option = $this->configuration->getOption($key);
			if (!$option || $option->hasValue())
			{
				continue;
			}

			$option->setValue($value);
		}

		$textOption = $this->configuration->getOption('MESSAGE_TEXT');
		if ($textOption)
		{
			$speedOption = $this->configuration->getOption('VOICE_SPEED');
			$textOption->setView(
				function () use ($speedOption)
				{
					/** @var \CAllMain {$GLOBALS['APPLICATION']} */
					ob_start();
					$GLOBALS['APPLICATION']->includeComponent(
						"bitrix:sender.call.text.editor",
						".default",
						array(
							"INPUT_NAME" => "%INPUT_NAME%",
							"VALUE" => "%INPUT_VALUE%",
							"SPEED_INPUT_NAME" => $speedOption
								?
								"%INPUT_NAME_" . $speedOption->getCode() . "%"
								:
								''
						)
					);

					return ob_get_clean();
				}
			);
		}

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