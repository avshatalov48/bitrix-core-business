<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use \Bitrix\Main\Service\GeoIp;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadLanguageFile(__FILE__);

class CatalogFeedbackComponent extends CBitrixComponent
{
	private const FEEDBACK_TYPE_FEEDBACK = 'feedback';
	private const FEEDBACK_TYPE_INTEGRATION_REQUEST = 'integration_request';

	public function onPrepareComponentParams($arParams)
	{
		if (empty($arParams['FEEDBACK_TYPE']))
		{
			$arParams['FEEDBACK_TYPE'] = self::FEEDBACK_TYPE_INTEGRATION_REQUEST;
		}

		return parent::onPrepareComponentParams($arParams);
	}

	public function executeComponent()
	{
		if ($this->arParams['FEEDBACK_TYPE'] === self::FEEDBACK_TYPE_INTEGRATION_REQUEST)
		{
			$this->arResult = $this->getIntegrationRequestFormInfo($this->getPortalZone());
		}
		elseif ($this->arParams['FEEDBACK_TYPE'] === self::FEEDBACK_TYPE_FEEDBACK)
		{
			$this->arResult = $this->getFeedbackFormInfo($this->getPortalZone());
		}

		$this->arResult['type'] = 'slider_inline';
		$this->arResult['fields']['values']['CONTACT_EMAIL'] = CurrentUser::get()->getEmail();
		if ($this->arParams['FEEDBACK_TYPE'] === self::FEEDBACK_TYPE_INTEGRATION_REQUEST)
		{
			$this->arResult['domain'] = 'https://bitrix24.team';
			$this->arResult['presets'] = [
				'url' => defined('BX24_HOST_NAME') ? BX24_HOST_NAME : $_SERVER['SERVER_NAME'],
				'tarif' => $this->getLicenseType(),
				'c_email' => CurrentUser::get()->getEmail(),
				'city' => implode(' / ', $this->getUserGeoData()),
				'partner_id' => Option::get('bitrix24', 'partner_id', 0),
				'sender_page' => $this->arParams['SENDER_PAGE'] ?? '',
			];
		}
		else
		{
			$this->arResult['domain'] = 'https://product-feedback.bitrix24.com';
			$this->arResult['presets'] = [
				'from_domain' => defined('BX24_HOST_NAME') ? BX24_HOST_NAME : Option::get('main', 'server_name', ''),
				'b24_plan' => $this->getLicenseType(),
				'b24_zone' => $this->getPortalZone(),
				'c_name' => CurrentUser::get()->getFullName(),
				'user_status' => CurrentUser::get()->isAdmin() ? 'yes' : 'no',
			];
		}

		$this->includeComponentTemplate();
	}

	private function getUserGeoData(): array
	{
		$countryName = GeoIp\Manager::getCountryName('', 'ru');
		if (!$countryName)
		{
			$countryName = GeoIp\Manager::getCountryName();
		}

		$cityName = GeoIp\Manager::getCityName('', 'ru');
		if (!$cityName)
		{
			$cityName = GeoIp\Manager::getCityName();
		}

		return [
			'country' => $countryName,
			'city' => $cityName
		];
	}

	/**
	 * @param string | null $region
	 * @return array
	 */
	private function getIntegrationRequestFormInfo(?string $region): array
	{
		if (LANGUAGE_ID === 'ua')
		{
			return ['id' => 1293, 'lang' => 'ua', 'sec' => 'vnb6hi'];
		}

		switch ($region)
		{
			case 'ru':
				return ['id' => 1291, 'lang' => 'ru', 'sec' => 'a9byq4'];
			case 'by':
				return ['id' => 1297, 'lang' => 'ru', 'sec' => 'b9rrf5'];
			case 'kz':
				return ['id' => 1298, 'lang' => 'ru', 'sec' => '6xe72g'];
			default:
				return ['id' => 1291, 'lang' => 'ru', 'sec' => 'a9byq4'];
		}
	}

	/**
	 * @param string | null $region
	 * @return array
	 */
	private function getFeedbackFormInfo(?string $region): array
	{
		switch ($region)
		{
			case 'ru':
			case 'by':
			case 'kz':
				return ['id' => 384, 'lang' => 'ru', 'sec' => '0pskpd', 'zones' => ['ru', 'by', 'kz']];
			case 'en':
			case 'ua':
				return ['id' => 392, 'lang' => 'en', 'sec' => 'siqjqa', 'zones' => ['en', 'ua']];
			case 'es':
				return ['id' => 388, 'lang' => 'es', 'sec' => '53t2bu', 'zones' => ['es']];
			case 'de':
				return ['id' => 390, 'lang' => 'de', 'sec' => 'mhglfc', 'zones' => ['de']];
			case 'com.br':
				return ['id' => 386, 'lang' => 'com.br', 'sec' => 't6tdpy', 'zones' => ['com.br']];
			default:
				return ['id' => 392, 'lang' => 'en', 'sec' => 'siqjqa', 'zones' => ['en', 'ua']];
		}
	}

	/**
	 * @return string
	 */
	private function getPortalZone(): ?string
	{
		if ($this->isEnabled())
		{
			return \CBitrix24::getPortalZone();
		}

		return null;
	}

	/**
	 * @return string
	 */
	private function getLicenseType(): ?string
	{
		if($this->isEnabled())
		{
			return \CBitrix24::getLicenseType();
		}

		return null;
	}

	private function isEnabled(): bool
	{
		return Loader::includeModule('bitrix24');
	}

	/**
	 * @return bool
	 */
	public function isIntegrationRequestPossible(): bool
	{
		$isPortalValidForIntegration = in_array(
			$this->getPortalZone(),
			['ru', 'ua', 'by', 'kz']
		);
		$doesFormHavePortalLanguage = in_array(LANGUAGE_ID, ['ru', 'ua']);

		return $isPortalValidForIntegration && $doesFormHavePortalLanguage;
	}

	public function renderIntegrationRequestButton(): void
	{
		if($this->isEnabled() && $this->isIntegrationRequestPossible() && Loader::includeModule('ui'))
		{
			Extension::load(['catalog.document-card']);
			echo '<button class="ui-btn ui-btn-light-border ui-btn-themes" onclick="BX.Catalog.DocumentCard.Slider.openIntegrationRequestForm(); return false;">'.Loc::getMessage('CATALOG_FEEDBACK_INTEGRATION_REQUEST_TITLE').'</button>';
		}
	}
}