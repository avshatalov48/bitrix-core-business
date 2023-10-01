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
use Bitrix\Main\UI\Extension;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Message;

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

		$assignOption = $this->configuration->getOption('ASSIGNED_BY');
		if ($assignOption)
		{
			$assignOption->setView(
				function () use ($assignOption)
				{
					$userList = $assignOption->getValue();
					$userList = $userList ? explode(',', $userList) : [];

					ob_start();
					$GLOBALS['APPLICATION']->includeComponent(
						"bitrix:main.user.selector",
						".default",
						[
							"ID" => "sender-crm-rc-message",
							"INPUT_NAME" => "%INPUT_NAME%[]",
							"LIST" => $userList,
							'API_VERSION' => '3',
							"SELECTOR_OPTIONS" => array(
								'context' => 'SENDER_USER',
								'allowAddSocNetGroup' => 'N',
								'departmentSelectDisable' => 'Y'
							)
						]
					);

					return ob_get_clean();
				}
			);
		}
		$this->createDaysAgoView();

		return $this->configuration;
	}

	protected function createDaysAgoView()
	{
		$dealDaysAgoOption = $this->configuration->getOption('DEAL_DAYS_AGO');
		$formPreviousOption = $this->configuration->getOption('FROM_PREVIOUS');

		if($dealDaysAgoOption && $formPreviousOption)
		{
			$dealDaysAgoOption->setView(
				function() use ($dealDaysAgoOption, $formPreviousOption)
				{
					$prefix = 'CONFIGURATION_';
					$daysAgoCode = htmlspecialcharsbx($prefix.$dealDaysAgoOption->getCode());
					$fromPreviousCode = htmlspecialcharsbx($prefix.$formPreviousOption->getCode());
					ob_start();
					Extension::load("sender.rc_editor");

					echo "<input type='number' step='1' id='" . $daysAgoCode . "' 
					name='" . $daysAgoCode . "'
					class='bx-sender-form-control' value='" . (int)$dealDaysAgoOption->getValue() . "' 
					min='" . htmlspecialcharsbx($dealDaysAgoOption->getMinValue()) . "'
					max='" . htmlspecialcharsbx($dealDaysAgoOption->getMaxValue()) . "'
					/>";

					$params = \Bitrix\Main\Web\Json::encode(
						[
							'elementId' => $daysAgoCode,
							'conditionElementId' => $fromPreviousCode
						]
					);

					echo "<script>new BX.Sender.RcEditor(".$params.")</script>";

					return ob_get_clean();
				}
			);
		}
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