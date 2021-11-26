<?php


namespace Bitrix\Sender\Transport;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\Extension;
use Bitrix\Sender\Entity\Letter;
use Bitrix\Sender\Message;

Loc::loadMessages(__FILE__);
class TimeLimiter implements iLimiter
{
	public const DEFAULT_SENDING_START = '09:00';
	public const DEFAULT_SENDING_END = '18:00';
	/**
	 * @var Message\iBase
	 */
	private $letter;
	private $parameters;

	public function withLetter($letter)
	{
		$this->letter = $letter;
		return $this;
	}

	/**
	 * Create instance.
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * @inheritDoc
	 */
	public function getLimit()
	{
		return 1;
	}

	/**
	 * @inheritDoc
	 */
	public function getCurrent()
	{
		if (!$this->letter)
		{
			return 0;
		}

		$sendingStart = $this->letter->getConfiguration()->get('SENDING_START');
		$sendingEnd = $this->letter->getConfiguration()->get('SENDING_END');
		$sendingTimeEnabled = $this->letter->getConfiguration()->get('SENDING_TIME');

		if (!$sendingEnd || !$sendingStart || $sendingTimeEnabled !== 'Y')
		{
			return 0;
		}
		$checkTime =  strtotime($sendingStart);
		$sendingStart = strtotime($sendingStart);
		$sendingEnd = strtotime($sendingEnd);
		$currentTime = strtotime((new DateTime())->format("H:i:s"));

		$sendingStart = $sendingStart > $sendingEnd ? $sendingEnd : $sendingStart;
		$sendingEnd = $checkTime > $sendingEnd ? $checkTime : $sendingEnd;

		$this->setParameter('sendingStart', $sendingStart);
		$this->setParameter('sendingEnd', $sendingEnd);
		$this->setParameter('currentTime', $currentTime);

		if ($currentTime > $sendingStart && $currentTime < $sendingEnd)
		{
			return 0;
		}

		return 1;
	}

	/**
	 * @inheritDoc
	 */
	public function getUnitName()
	{
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getUnit()
	{
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getCaption()
	{
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getParameter($name)
	{
		return $this->parameters[$name] ?? null;
	}

	/**
	 * @inheritDoc
	 */
	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function isHidden()
	{
		return true;
	}

	/**
	 * add options SENDING_START and SENDING_END to the Message Configuration
	 * @param $configuration
	 */
	public static function prepareMessageConfiguration($configuration)
	{
		$configuration->addOption(new Message\ConfigurationOption([
			'type' => Message\ConfigurationOption::TYPE_CHECKBOX,
			'code' => 'SENDING_TIME',
			'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
			'name' => Loc::getMessage('SENDER_INTEGRATION_MESSAGE_CONFIG_SENDING_TIME'),
			'show_in_list' => false,
			'required' => false,
			'value' => Option::get('sender', 'sending_time')
		]));

		$configuration->addOption(new Message\ConfigurationOption([
			'type' => Message\ConfigurationOption::TYPE_STRING,
			'code' => 'SENDING_START',
			'name' => Loc::getMessage('SENDER_INTEGRATION_MESSAGE_CONFIG_SENDING_START'),
			'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
			'show_in_list' => false,
			'required' => false,
			'value' => Option::get('sender', 'sending_start', TimeLimiter::DEFAULT_SENDING_START)
		]));

		$configuration->addOption(new Message\ConfigurationOption([
			'type' => Message\ConfigurationOption::TYPE_STRING,
			'code' => 'SENDING_END',
			'name' => Loc::getMessage('SENDER_INTEGRATION_MESSAGE_CONFIG_SENDING_END'),
			'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
			'show_in_list' => false,
			'required' => false,
			'value' => Option::get('sender', 'sending_end', TimeLimiter::DEFAULT_SENDING_END)
		]));
	}

	/**
	 * add options view SENDING_START and SENDING_END to the Message Configuration
	 * @param $configuration
	 */
	public static function prepareMessageConfigurationView($configuration)
	{

		$sendingStart = $configuration->getOption('SENDING_START');
		$sendingEnd = $configuration->getOption('SENDING_END');
		$checkbox = $configuration->getOption('SENDING_TIME');

		$view = function($input, $checkbox)
		{
			$prefix = 'CONFIGURATION_';
			$inputCode = htmlspecialcharsbx($prefix.$input->getCode());
			$checkboxCode = htmlspecialcharsbx($prefix.$checkbox->getCode());
			ob_start();
			Extension::load("sender.secret_block");
			$inputHtml = "<select 
				id=\"$inputCode\"
				name=\"$inputCode\"
				value='".$input->getValue()."'
				class=\"bx-sender-form-control bx-sender-message-editor-field-select\">";
			for ($hour = 0; $hour < 24; $hour++)
			{
				foreach ([0, 30] as $minute)
				{
					$time = strtotime(sprintf("%02d:%02d", $hour, $minute));
					$formatted = (new \DateTime())
						->setTimestamp($time)
						->format(Context::getCurrent()
							->getCulture()
							->getShortTimeFormat()
						);

					$inputHtml .= "<option value='{$formatted}'";
					$inputHtml .= $time === strtotime($input->getValue()) ? "selected" : "";
					$inputHtml .= ">{$formatted}</option>";
				}
			}

			$inputHtml .= '</select>';

			echo $inputHtml;
			$params = \Bitrix\Main\Web\Json::encode(
				[
					'elementId' => $inputCode,
					'conditionElementId' => $checkboxCode
				]
			);

			echo "<script>new BX.Sender.SecretBlock(".$params.")</script>";

			return ob_get_clean();
		};

		$sendingStart->setView($view($sendingStart, $checkbox));
		$sendingEnd->setView($view($sendingEnd, $checkbox));
	}
}