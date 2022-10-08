<?php

namespace Bitrix\MessageService\Providers\Base;

use Bitrix\Main\Context;
use Bitrix\Main\Result;
use Bitrix\MessageService\Providers;

abstract class Registrar implements Providers\Registrar
{
	protected Providers\OptionManager $optionManager;
	protected string $providerId;

	public function __construct(string $providerId, Providers\OptionManager $optionManager)
	{
		$this->providerId = $providerId;
		$this->optionManager = $optionManager;
	}

	public function isConfirmed(): bool
	{
		return $this->isRegistered();
	}

	public function confirmRegistration(array $fields): Result
	{
		return new Result();
	}

	public function sendConfirmationCode(): Result
	{
		return new Result();
	}

	public function sync(): Registrar
	{
		return $this;
	}

	public function getCallbackUrl(): string
	{
		return $this->getHostUrl() . '/bitrix/tools/messageservice/callback_' . $this->providerId . '.php';
	}

	public function getHostUrl(): string
	{
		$protocol = (Context::getCurrent()->getRequest()->isHttps() ? 'https' : 'http');
		if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME)
		{
			$host = SITE_SERVER_NAME;
		}
		else
		{
			$host =
				\Bitrix\Main\Config\Option::get('main', 'server_name', Context::getCurrent()->getServer()->getHttpHost())
					?: Context::getCurrent()->getServer()->getHttpHost()
			;
		}

		$port = Context::getCurrent()->getServer()->getServerPort();
		if($port != 80 && $port != 443 && $port > 0 && mb_strpos($host, ':') === false)
		{
			$host .= ':'.$port;
		}
		elseif($protocol === 'http' && $port == 80)
		{
			$host = str_replace(':80', '', $host);
		}
		elseif($protocol === 'https' && $port == 443)
		{
			$host = str_replace(':443', '', $host);
		}

		return $protocol . '://' . $host;
	}


}