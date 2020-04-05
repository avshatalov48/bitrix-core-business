<?php

namespace Bitrix\MessageService\Sender;

use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\Main\Result;
use Bitrix\Main\Context;
use Bitrix\Main\Config\Option;

abstract class BaseConfigurable extends Base
{
	protected $options;

	public function isConfigurable()
	{
		return true;
	}

	/**
	 * Check demo status.
	 * @return bool
	 */
	public function isDemo()
	{
		return ($this->getOption('is_demo') === true);
	}

	/**
	 * Check registration state.
	 * @return bool
	 */
	abstract public function isRegistered();

	/**
	 * Check is registration confirmed.
	 * @return bool
	 */
	public function isConfirmed()
	{
		return $this->isRegistered();
	}

	/**
	 * Get default From.
	 * @return null|string
	 */
	abstract public function getDefaultFrom();

	/**
	 * Set default From.
	 * @param string $from From.
	 * @return $this
	 */
	abstract public function setDefaultFrom($from);

	/**
	 * Check can use state of sender.
	 * @return bool
	 */
	public function canUse()
	{
		return ($this->isRegistered() && $this->isConfirmed());
	}

	/**
	 * @param array $fields
	 * @return Result
	 */
	abstract public function register(array $fields);

	/**
	 * @return array
	 */
	abstract public function getOwnerInfo();

	/**
	 * @param array $fields
	 * @return Result
	 */
	public function confirmRegistration(array $fields)
	{
		return new Result();
	}

	/**
	 * @return Result
	 */
	public function sendConfirmationCode()
	{
		return new Result();
	}

	/**
	 * @return string
	 */
	public function getManageUrl()
	{
		if (defined('ADMIN_SECTION') && ADMIN_SECTION === true)
		{
			return 'messageservice_sender_sms.php?sender_id='.$this->getId();
		}

		return '/crm/configs/sms/?sender='.$this->getId(); //TODO: replace public path
	}

	/**
	 * @return string
	 */
	abstract public function getExternalManageUrl();

	/**
	 * @param array $messageFields
	 * @return MessageStatus Message status result.
	 * @internal param string $messageId Message Id.
	 */
	abstract public function getMessageStatus(array $messageFields);

	/**
	 * Enable demo mode.
	 * @return $this
	 */
	public function enableDemo()
	{
		$this->setOption('is_demo', true);
		return $this;
	}

	/**
	 * Disable demo mode.
	 * @return $this
	 */
	public function disableDemo()
	{
		$this->setOption('is_demo', false);
		return $this;
	}

	/**
	 * Sync remote state (load From list etc.)
	 * @return $this
	 */
	public function sync()
	{
		return $this;
	}

	protected function getCallbackUrl()
	{
		$id = $this->getId();
		return $this->getHostUrl().'/bitrix/tools/messageservice/callback_'.$id.'.php';
	}

	/**
	 * Gets host url with port and scheme.
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @see \Bitrix\Disk\UrlManager::getHostUrl
	 */
	private function getHostUrl()
	{
		$protocol = (\CMain::isHTTPS() ? 'https' : 'http');
		if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME)
		{
			$host = SITE_SERVER_NAME;
		}
		else
		{
			$host =
				Option::get('main', 'server_name', Context::getCurrent()->getServer()->getHttpHost())?:
					Context::getCurrent()->getServer()->getHttpHost()
			;
		}

		$port = Context::getCurrent()->getServer()->getServerPort();
		if($port <> 80 && $port <> 443 && $port > 0 && strpos($host, ':') === false)
		{
			$host .= ':'.$port;
		}
		elseif($protocol == 'http' && $port == 80)
		{
			$host = str_replace(':80', '', $host);
		}
		elseif($protocol == 'https' && $port == 443)
		{
			$host = str_replace(':443', '', $host);
		}

		return $protocol . '://' . $host;
	}

	/**
	 * @param array $options
	 * @return $this
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected function setOptions(array $options)
	{
		$this->options = $options;
		$providerId = $this->getId();
		$providerType = strtolower($this->getType());
		Option::set('messageservice','sender.'.$providerType.'.'.$providerId, serialize($options));
		return $this;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected function getOptions()
	{
		if ($this->options === null)
		{
			$providerId = $this->getId();
			$providerType = strtolower($this->getType());
			$optionsString = Option::get('messageservice', 'sender.'.$providerType.'.'.$providerId);
			if (CheckSerializedData($optionsString))
			{
				$this->options = unserialize($optionsString);
			}

			if (!is_array($this->options))
			{
				$this->options = array();
			}
		}
		return $this->options;
	}

	/**
	 * @param $optionName
	 * @param $optionValue
	 * @return $this
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @internal param array $options
	 */
	protected function setOption($optionName, $optionValue)
	{
		$options = $this->getOptions();
		if (!isset($options[$optionName]) || $options[$optionName] !== $optionValue)
		{
			$options[$optionName] = $optionValue;
			$this->setOptions($options);
		}
		return $this;
	}

	/**
	 * @param $optionName
	 * @param mixed $defaultValue
	 * @return mixed|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected function getOption($optionName, $defaultValue = null)
	{
		$options = $this->getOptions();
		return isset($options[$optionName]) ? $options[$optionName] : $defaultValue;
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function clearOptions()
	{
		$this->options = array();
		$providerId = $this->getId();
		$providerType = strtolower($this->getType());
		Option::delete('messageservice', array('name' => 'sender.'.$providerType.'.'.$providerId));
		return true;
	}
}