<?php

namespace Bitrix\MessageService\Sender;

use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\Main\Result;
use Bitrix\Main\Context;
use Bitrix\Main\Config\Option;
use Bitrix\MessageService\Providers;

abstract class BaseConfigurable extends Base
{
	protected $options;

	protected Providers\DemoManager $demoManager;
	protected Providers\Registrar $registrar;
	protected Providers\OptionManager $optionManager;
	protected Providers\TemplateManager $templateManager;

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
	 * Set default From.
	 * @param string $from From.
	 * @return $this
	 */
	public function setDefaultFrom($from)
	{
		return $this;
	}

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
	public function getManageUrl(): string
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
		if($port <> 80 && $port <> 443 && $port > 0 && mb_strpos($host, ':') === false)
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
		$providerType = mb_strtolower($this->getType());
		Option::set('messageservice','sender.'.$providerType.'.'.$providerId, serialize($options));
		return $this;
	}

	/**
	 * @return array
	 */
	protected function getOptions(): array
	{
		$this->optionManager ??= new Providers\Base\Option($this->getType(), $this->getId());

		return $this->optionManager->getOptions();
	}

	/**
	 * @param $optionName
	 * @param $optionValue
	 * @return $this
	 * @internal param array $options
	 */
	protected function setOption($optionName, $optionValue): BaseConfigurable
	{
		$this->optionManager ??= new Providers\Base\Option($this->getType(), $this->getId());
		$this->optionManager->setOption($optionName, $optionValue);

		return $this;
	}

	/**
	 * @param $optionName
	 * @param mixed $defaultValue
	 * @return mixed|null
	 */
	protected function getOption($optionName, $defaultValue = null)
	{
		$this->optionManager ??= new Providers\Base\Option($this->getType(), $this->getId());

		return $this->optionManager->getOption($optionName, $defaultValue);
	}

	/**
	 * @return bool
	 */
	public function clearOptions(): bool
	{
		$this->optionManager ??= new Providers\Base\Option($this->getType(), $this->getId());
		$this->optionManager->clearOptions();

		return true;
	}

	public function getConfigComponentTemplatePageName(): string
	{
		return static::getId();
	}

	/**
	 * Can message be created from template only
	 *
	 * @return bool
	 */
	public function isTemplatesBased(): bool
	{
		return false;
	}

	/**
	 *
	 * List of available templates for templates-based senders
	 * Should return array of templates like this:
	 *
	 * [
	 * 		['ID' => '1', 'TITLE' => 'Template 1', 'PREVIEW' => 'Message created from template 1'],
	 * 		['ID' => '2', 'TITLE' => 'Template 2', 'PREVIEW' => 'Message created from template 2'],
	 * ]
	 *
	 * @param array|null $context Context for context-dependant templates
	 * @return array
	 */
	public function getTemplatesList(array $context = null): array
	{
		return [];
	}

	/**
	 * Prepare template for save in message headers
	 */
	public function prepareTemplate($templateData)
	{
		return $templateData;
	}
}
