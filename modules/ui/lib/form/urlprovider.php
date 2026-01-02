<?php

namespace Bitrix\UI\Form;

use Bitrix\Main\Application;

class UrlProvider
{
	public function getPartnerPortalUrl(): string
	{
		$domain = $this->resolvePartnerDomain();
		return "https://{$domain}";
	}

	private function resolvePartnerDomain(): string
	{
		$region = Application::getInstance()->getLicense()->getRegion();

		if ($this->isCisRegion($region))
		{
			return match ($region)
			{
				'kz' => 'teamkz.bitrix24.kz',
				'by' => 'belarus.bitrix24.by',
				'uz' => 'team.bitrix24.uz',
				default => 'bitrix24.team',
			};
		}

		// For all non-CIS countries
		return 'global.bitrix24.com';
	}

	private function isCisRegion(string $region): bool
	{
		return in_array($region, ['ru', 'kz', 'by', 'uz'], true);
	}
}
