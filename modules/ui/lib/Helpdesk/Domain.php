<?php

namespace Bitrix\UI\Helpdesk;

use Bitrix\Main;

class Domain
{
	private const HELPDESK_DOMAIN = [
		'en' => 'https://helpdesk.bitrix24.com',
		'br' => 'https://helpdesk.bitrix24.com.br',
		'de' => 'https://helpdesk.bitrix24.de',
		'es' => 'https://helpdesk.bitrix24.es',
		'fr' => 'https://helpdesk.bitrix24.fr',
		'it' => 'https://helpdesk.bitrix24.it',
		'pl' => 'https://helpdesk.bitrix24.pl',
		'ru' => 'https://helpdesk.bitrix24.ru',
	];

	public function __construct(private bool $useLicenseRegion = false)
	{
	}

	public function get(): string
	{
		return $this->useLicenseRegion ? $this->getByLicense() : $this->getByInterfaceLanguage();
	}

	public function getList(): array
	{
		return array_values(static::HELPDESK_DOMAIN);
	}

	public function isLicenseRegionUsed(): bool
	{
		return $this->useLicenseRegion;
	}

	private function getByInterfaceLanguage(): string
	{
		return $this->getByLanguageCode(LANGUAGE_ID);
	}

	private function getByLicense(): string
	{
		$lang = Main\Application::getInstance()->getLicense()->getRegion();

		return $this->getByLanguageCode($lang);
	}

	private function getByLanguageCode(string $languageCode): string
	{
		return match ($languageCode)
		{
			'ru', 'by', 'kz' => static::HELPDESK_DOMAIN['ru'],
			'de' => static::HELPDESK_DOMAIN['de'],
			'br' => static::HELPDESK_DOMAIN['br'],
			'fr' => static::HELPDESK_DOMAIN['fr'],
			'la' => static::HELPDESK_DOMAIN['es'],
			'pl' => static::HELPDESK_DOMAIN['pl'],
			'it' => static::HELPDESK_DOMAIN['it'],
			default => static::HELPDESK_DOMAIN['en'],
		};
	}
}