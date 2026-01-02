<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Copilot;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Metrika;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Uri;
use Bitrix\UI\Util;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class SiteCopilotComponent extends LandingBaseComponent
{
	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->init();

		// Analytic
		$metrika = new Metrika\Metrika(
			Metrika\Categories::SiteGeneration,
			Metrika\Events::open,
			Metrika\Tools::ai,
		);
		if ($this->request('st_section'))
		{
			$metrika->setSection(Metrika\Sections::tryfrom((string)$this->request('st_section')));
		}

		$isAvailable = Copilot\Manager::isAvailable();
		$isEnabled = Copilot\Manager::isFeatureEnabled();
		$isActive = Copilot\Manager::isActive();
		if (!$isAvailable || !$isEnabled || !$isActive)
		{
			$url = new Uri($this->arParams['PAGE_URL_SITES']);

			if ($isAvailable)
			{
				$sliderCode = Copilot\Manager::getUnactiveSliderCode();
				if (!$isEnabled)
				{
					$sliderCode = Copilot\Manager::getLimitSliderCode();
				}

				$url->addParams([
					'feature_promoter' => $sliderCode,
				]);
			}

			$metrika->setStatus(Metrika\Statuses::ErrorTurnedOff)->send();

			\localRedirect($url->getUri());
		}
		$metrika->send();

		if (Loader::includeModule('ai'))
		{
			// force chatbot registration
			Copilot\Connector\Chat\Chat::getCreateSiteChatBot();
		}

		if (Loader::includeModule('ui'))
		{
			$this->arResult['HELPER_FRAME_OPEN_URL'] = $this->getHelperFrameOpenUrl();
		}

		parent::executeComponent();
	}

	/**
	 * Returns the URL for the helper frame.
	 *
	 * @return string
	 */
	protected function getHelperFrameOpenUrl(): string
	{
		$baseUrl = Util::getHelpdeskUrl(true) . '/widget2/';
		$params = [
			'url' => urlencode(
				(Manager::isHttps() ? 'https://' : 'http://')
				. Manager::getHttpHost()
				. Manager::getApplication()->getCurPageParam()
			),
			'user_id' => Manager::getUserId(),
			'is_cloud' => ModuleManager::isModuleInstalled('bitrix24') ? '1' : '0',
			'action' => 'open',
		];

		return $baseUrl . '?' . http_build_query($params);
	}
}
