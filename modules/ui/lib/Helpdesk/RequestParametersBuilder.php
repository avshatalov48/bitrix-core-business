<?php

namespace Bitrix\UI\Helpdesk;

use Bitrix\ImBot\Bot\Network;
use Bitrix\ImBot\Bot\Partner24;
use Bitrix\ImBot\Bot\Support24;
use Bitrix\ImBot\Bot\SupportBox;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\Encoding;

class RequestParametersBuilder
{
	private CurrentUser $currentUser;
	/**
	 * @var array<string, int|string>
	 */
	private array $parameters = [];
	private bool $isCloud;

	public function __construct()
	{
		$this->currentUser = CurrentUser::get();
		$this->isCloud = Loader::includeModule('bitrix24');
	}

	public function build(): array
	{
		$this->buildPortalInformation();
		$this->buildUserCharacteristics();
		$this->buildSupportConfiguration();
		$this->buildKeyConfiguration();
		$this->buildExternalParameters();

		return $this->parameters;
	}

	private function buildSupportConfiguration(): void
	{
		if (!Loader::includeModule('imbot'))
		{
			return;
		}

		$this->parameters += [
			'support_partner_code' => Partner24::getBotCode(),
			'support_partner_name' => Encoding::convertEncoding(Partner24::getPartnerName(), SITE_CHARSET, 'utf-8'),
		];
		$supportBotId = 0;

		if (Support24::getSupportLevel() === Network::SUPPORT_LEVEL_PAID && Support24::isEnabled())
		{
			$supportBotId = Support24::getBotId();
		}
		elseif (SupportBox::isEnabled())
		{
			$supportBotId = SupportBox::getBotId();
		}

		$this->parameters['support_bot'] = $supportBotId;
	}

	private function buildExternalParameters(): void
	{
		$method = '\\' . __METHOD__;
		$event =  (new Event('ui', $method, $this->parameters));
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if (($eventParameters = $eventResult->getParameters()) && is_array($eventParameters))
			{
				$this->parameters = array_merge($this->parameters, $eventParameters);
			}
		}
	}

	private function buildKeyConfiguration(): void
	{
		if ($this->isCloud)
		{
			$this->parameters['key'] = \CBitrix24::requestSign($this->getHostName() . $this->currentUser->getId());
		}
		else
		{
			$this->parameters['head'] = md5('BITRIX' . Application::getInstance()->getLicense()->getKey() . 'LICENCE');
			$this->parameters['key'] = md5($this->getHostName() . $this->currentUser->getId() . $this->parameters['head']);
		}
	}

	private function buildUserCharacteristics(): void
	{
		$userId = $this->currentUser->getId();
		$this->parameters += [
			'is_admin' => ($this->isCloud && \CBitrix24::isPortalAdmin($userId))
			|| (!$this->isCloud && $this->currentUser->isAdmin()) ? 1 : 0,
			'is_integrator' => (int)($this->isCloud && \CBitrix24::isIntegrator($userId)),
			'user_id' => $userId,
			'user_email' => $this->currentUser->getEmail(),
			'user_name' => Encoding::convertEncoding($this->currentUser->getFirstName(), SITE_CHARSET, 'utf-8'),
			'user_last_name' => Encoding::convertEncoding($this->currentUser->getLastName(), SITE_CHARSET, 'utf-8'),
		];

		if (Loader::includeModule('intranet'))
		{
			$this->parameters['user_date_register'] = \Bitrix\Intranet\CurrentUser::get()->getDateRegister()?->getTimestamp();
		}
	}

	private function buildPortalInformation(): void
	{
		$this->parameters += [
			'tariff' => Option::get('main', '~controller_group_name', ''),
			'is_cloud' => $this->isCloud ? '1' : '0',
			'host' => $this->getHostName(),
			'languageId' => LANGUAGE_ID,
		];

		if ($this->isCloud)
		{
			$this->parameters['portal_date_register'] = Option::get('main', '~controller_date_create', '');
		}
	}

	private function getHostName(): ?string
	{
		if ($this->isCloud && defined('BX24_HOST_NAME'))
		{
			return BX24_HOST_NAME;
		}

		return Context::getCurrent()?->getRequest()->getHttpHost();
	}
}