<?php

namespace Bitrix\UI\FeaturePromoter;

use Bitrix\Main\Result;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Helpdesk;

class Popup implements FeaturePromoterProvider
{
	private const HELPDESK_PATH = '/widget2/restrictive_popup.php';

	public function __construct(private string $code, private string $currentUrl = '')
	{
	}

	public function getRendererParameters(): array
	{
		$resultLoadingConfigurationFromHelpdesk = $this->loadConfigurationFromHelpdesk();

		if ($resultLoadingConfigurationFromHelpdesk->isSuccess())
		{
			$configuration = $resultLoadingConfigurationFromHelpdesk->getData()['popup'] ?? [];
			$validator = new PopupConfigurationValidator($configuration);

			return $validator->isValidConfiguration()
				? $configuration
				: $this->getDefaultConfiguration();
		}

		return $this->getDefaultConfiguration();
	}

	private function loadConfigurationFromHelpdesk(): Result
	{
		$helpdeskRequest = new Helpdesk\Request(self::HELPDESK_PATH, [
			'url' => $this->currentUrl,
			'code' => $this->code,
		]);

		return $helpdeskRequest->send();
	}

	private function getDefaultConfiguration(): array
	{
		return [
			'header' => [
				'top' => [
					'title' => Loc::getMessage('UI_INFOHELPER_PROVIDER_POPUP_DEFAULT_TOP_TITLE'),
				],
				'info' => [
					'title' => Loc::getMessage('UI_INFOHELPER_PROVIDER_POPUP_DEFAULT_DEESCRIPTION'),
					'roundContent' => '--rocket',
					'moreLabel' => Loc::getMessage('UI_INFOHELPER_PROVIDER_POPUP_MORE_BUTTON'),
					'code' => 'limit_why_pay_tariff',
				],
				'button' => [
					'label' => Loc::getMessage('UI_INFOHELPER_PROVIDER_POPUP_EXTRA_BUTTON'),
					'url' => '/settings/license_all.php',
				],
			],
		];
	}
}
