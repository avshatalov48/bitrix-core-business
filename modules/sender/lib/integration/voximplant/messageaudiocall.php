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
use Bitrix\Main\UI\FileInputUtility;
use Bitrix\Sender\Message;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Transport\TimeLimiter;

Loc::loadMessages(__FILE__);

/**
 * Class MessageCall
 * @package Bitrix\Sender\Integration\VoxImplant
 */
class MessageAudioCall implements Message\iBase, Message\iMailable, Message\iAudible
{
	const CODE = self::CODE_AUDIO_CALL;

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
		return Loc::getMessage('SENDER_INTEGRATION_AUDIOCALL_MESSAGE_NAME');
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
		return array(TransportAudioCall::CODE);
	}

	/**
	 * Set configuration options
	 *
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
				'name' => Loc::getMessage('SENDER_INTEGRATION_AUDIOCALL_MESSAGE_CONFIG_OUTPUT_NUMBER'),
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
				'readonly_view' => function($value)
				{
					return Service::getFormattedOutputNumber($value);
				},
				'required' => true,
				'show_in_list' => true,
			),
			array(
				'type' => 'audio',
				'code' => 'AUDIO_FILE',
				'name' => Loc::getMessage('SENDER_INTEGRATION_AUDIOCALL_MESSAGE_CONFIG_MESSAGE_FILE'),
				'required' => true,
				'params' => [
					'allowUpload' => 'F',
					'allowUploadExt' => 'mp3',
					'maxCount' => 1,
				]
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
	 * Check value of audio field and prepare it for DB
	 * @param string $optionCode Field code.
	 * @param string $newValue New field value.
	 * @return bool|string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function getAudioValue($optionCode, $newValue)
	{
		$valueIsCorrect = false;

		if($newValue <> '')
		{
			$audio = (new Audio())
				->withValue($newValue)
				->withMessageCode($this->getCode());

			if($audio->createdFromPreset())
			{
				if($audio->getFileUrl()) // preset $newValue is really exists
				{
					$valueIsCorrect = true;
				}
			}
			else
			{
				$oldValue = $this->configuration->getOption($optionCode);
				$oldAudio = (new Audio())
					->withJsonString($oldValue->getValue())
					->withMessageCode($this->getCode());

				if($oldAudio->getFileId() == $newValue) // file wasn't changed
				{
					$audio = $oldAudio;
					$valueIsCorrect = true;
				}
				else
				{
					if(
						$audio->getDuration() && // check if new file is really mp3
						FileInputUtility::instance()->checkFiles($optionCode, [$newValue]) // check if file was uploaded by current user
					)
					{
						$valueIsCorrect = true;
					}
				}
			}
		}

		if ($valueIsCorrect)
		{
			return $audio->getDbValue();
		}

		return false;
	}
}