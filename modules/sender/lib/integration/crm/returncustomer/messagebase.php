<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\ReturnCustomer;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sender\Message;
use Bitrix\Sender\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class MessageBase
 * @package Bitrix\Sender\Integration\Crm\ReturnCustomer
 */
class MessageBase implements Message\iBase, Message\iReturnCustomer
{
	const CODE = self::CODE_UNDEFINED;
	const CODE_RC_LEAD = 'rc_lead';
	const CODE_RC_DEAL = 'rc_deal';

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
		return Loc::getMessage('SENDER_INTEGRATION_CRM_RC_MESSAGE_NAME');
	}

	public function getCode()
	{
		return static::CODE;
	}

	public function getSupportedTransports()
	{
		return array(static::CODE);
	}

	protected function setConfigurationOptions()
	{
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
		$assignOption = $this->configuration->getOption('ASSIGNED_BY');
		if ($assignOption)
		{
			$assignOption->setView(
				function () use ($assignOption)
				{
					$userList = $assignOption->getValue();
					if ($userList)
					{
						$userList = explode(',', $assignOption->getValue());
					}

					ob_start();
					$GLOBALS['APPLICATION']->includeComponent(
						"bitrix:sender.ui.user.selector",
						".default",
						[
							"ID" => "sender-crm-rc-message",
							"INPUT_NAME" => "%INPUT_NAME%",
							"LIST" => $userList,
						]
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