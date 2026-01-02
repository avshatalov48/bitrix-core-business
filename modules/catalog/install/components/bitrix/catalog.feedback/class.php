<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Service\GeoIp;
use Bitrix\UI\Form\UrlProvider;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Error;
use Bitrix\UI\Form\FormsProvider;

class CatalogFeedbackComponent extends CBitrixComponent implements Controllerable, Errorable
{
	use ErrorableImplementation;

	private const FEEDBACK_TYPE_FEEDBACK = 'feedback';
	private const FEEDBACK_TYPE_INTEGRATION_REQUEST = 'integration_request';

	public function configureActions(): array
	{
		return [];
	}

	public function onPrepareComponentParams($arParams): array
	{
		if (empty($arParams['FEEDBACK_TYPE']))
		{
			$arParams['FEEDBACK_TYPE'] = self::FEEDBACK_TYPE_INTEGRATION_REQUEST;
		}

		return parent::onPrepareComponentParams($arParams);
	}

	/**
	 * @deprecated
	 * @see \CatalogFeedbackComponent::getFormParamsAction
	 * @return void
	 */
	public function executeComponent(): void
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
			$this->arResult['domain'] = $this->getPartnerPortalUrl();
			$this->arResult['presets'] = [
				'url' => defined('BX24_HOST_NAME') ? BX24_HOST_NAME : $_SERVER['SERVER_NAME'],
				'tarif' => $this->getLicenseType(),
				'c_email' => CurrentUser::get()->getEmail(),
				'city' => implode(' / ', $this->getUserGeoData()),
				'partner_id' => Option::get('bitrix24', 'partner_id', 0),
				'sender_page' => $this->arParams['SENDER_PAGE'] ?? '',
			];
		}

		$this->includeComponentTemplate();
	}

	public function getFormParamsAction(): ?array
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->addErrorMessage('Module "catalog" is not installed.');

			return null;
		}

		if (!Loader::includeModule('bitrix24'))
		{
			$this->addErrorMessage('Module "bitrix24" is not installed.');

			return null;
		}

		return [
			'id' => 'B24CatalogFeedback',
			'forms' => FormsProvider::getForms(),
			'type' => 'slider_inline',
			'fields' => [
				'values' => [
					'CONTACT_EMAIL' => CurrentUser::get()->getEmail(),
				],
			],
			'portalUri' => $this->getPartnerPortalUrl(),
			'presets' => [
				'city' => implode(' / ', $this->getUserGeoData()),
				'source' => 'catalog',
			],
		];
	}

	protected function addErrorMessage(string $message): void
	{
		$this->errorCollection->setError(new Error($message));
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
			'city' => $cityName,
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
	 * @return string|null
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
	 * @return string|null
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
		return true;
	}

	public function renderIntegrationRequestButton(): void
	{
		if($this->isEnabled() && $this->isIntegrationRequestPossible() && Loader::includeModule('ui'))
		{
			Extension::load(['catalog.document-card']);
			echo '<button class="ui-btn ui-btn-light-border ui-btn-themes" onclick="BX.Catalog.DocumentCard.Slider.openIntegrationRequestForm(); return false;">'.Loc::getMessage('CATALOG_FEEDBACK_INTEGRATION_REQUEST_TITLE').'</button>';
		}
	}

	protected function getPartnerPortalUrl(): string
	{
		Loader::includeModule('ui');

		return (new UrlProvider())->getPartnerPortalUrl();
	}
}
