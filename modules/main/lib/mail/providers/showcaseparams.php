<?php

namespace Bitrix\Main\Mail\Providers;

use Bitrix\Mail;
use Bitrix\Main\Localization;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Configuration;
use Bitrix\Mail\Helper\Mailbox;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;

final class ShowcaseParams
{
	private const PATH_MAIL_CONFIG = '/mail/config/';
	private const MAILBOX_LIMIT_SLIDER_CODE = 'limit_contact_center_mail_box_number';

	private const RU_REGION_CODE = 'ru';
	private const IMAP_TYPE = 'imap';
	private const RU_PROVIDERS_LIST = ['yandex', 'mailru', 'office365', 'gmail'];
	private const WORLD_PROVIDER_LIST = ['gmail', 'icloud', 'office365', 'yahoo'];

	private bool $isSenderShowcase;
	private ?string $region;

	public function __construct(bool $isSenderShowcase)
	{
		$this->isSenderShowcase = $isSenderShowcase;
		$this->region = Application::getInstance()->getLicense()->getRegion();
	}

	public function getParams(): array
	{
		$isCloud = false;
		if (!Loader::includeModule('mail'))
		{
			return [
				'options' => [
					'isModuleMailInstalled' => false,
					'promotionProviders' => $this->getPromotionProviders(),
					'isSmtpAvailable' => $this->isSmtpAvailable(),
					'isCloud' => $isCloud,
				],
			];
		}

		$intranetToolSettings = $this->getIntranetToolSettings();
		extract($intranetToolSettings, EXTR_OVERWRITE);
		$canConnectNewMailbox = Mailbox\MailboxConnector::canConnectNewMailbox();
		$providers = $this->getProviders();

		return [
			'options' => [
				'isModuleMailInstalled' => true,
				'canConnectNewMailbox' => $canConnectNewMailbox,
				'mailboxLimitSliderCode' => self::MAILBOX_LIMIT_SLIDER_CODE,
				'isMailToolAvailable' => $isMailToolAvailable ?? false,
				'toolLimitSliderCode' => $toolLimitSliderCode ?? null,
				'isSmtpAvailable' => $this->isSmtpAvailable(),
				'isCloud' => $isCloud,
			],
			'providers' => $providers,
		];
	}

	private function getIntranetToolSettings(): array
	{
		if (class_exists('\Bitrix\Mail\Integration\Intranet\ToolShowSettings'))
		{
			$toolShowSettings = new Mail\Integration\Intranet\ToolShowSettings();

			return [
				'isMailToolAvailable' => $toolShowSettings->isMailAvailable(),
				'toolLimitSliderCode' => $toolShowSettings->getMailLimitSliderCode(),
			];
		}

		return [
			'isMailToolAvailable' => true,
			'toolLimitSliderCode' => null,
		];
	}

	private function getProviders(): array
	{
		if (!Loader::includeModule('intranet'))
		{
			return [];
		}

		$services = Mailbox::getServices();
		$providers = [];
		foreach ($services as $service)
		{
			if ($service['type'] !== self::IMAP_TYPE)
			{
				continue;
			}

			$uri = new Uri(self::PATH_MAIL_CONFIG . 'new');
			if ($this->isSenderShowcase)
			{
				$uri->addParams(['smtp' => 'Y']);
			}

			$providers[] = [
				'href' => $uri->addParams(['id' => $service['id']]),
				'icon' => $service['icon'],
				'name' => $service['name'],
			];
		}

		return $providers;
	}

	private function getPromotionProviders(): array
	{
		if (!$this->region)
		{
			return self::WORLD_PROVIDER_LIST;
		}

		return (Localization\Loc::getDefaultLang($this->region) === self::RU_REGION_CODE) ? self::RU_PROVIDERS_LIST : self::WORLD_PROVIDER_LIST;
	}

	private function isSmtpAvailable(): bool
	{
		$defaultMailConfiguration = Configuration::getValue('smtp');
		return Loader::includeModule('bitrix24')
			|| $defaultMailConfiguration['enabled']
		;
	}
}