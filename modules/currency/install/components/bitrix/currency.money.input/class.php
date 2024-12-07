<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @global CMain $APPLICATION
 */

use Bitrix\Currency;
use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\Extension;

class CCurrencyMoneyInputComponent extends \CBitrixComponent
{
	protected array $currencyList = [];

	/**
	 * Load language file.
	 */
	public function onIncludeComponentLang(): void
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	public function onPrepareComponentParams($params)
	{
		$params['CONTROL_ID'] = trim((string)($params['CONTROL_ID'] ?? ''));
		if ($params['CONTROL_ID'] === '')
		{
			$params['CONTROL_ID'] = 'bxme_' . Random::getString(5);
		}
		$params['FIELD_NAME'] = trim((string)($params['FIELD_NAME'] ?? ''));
		if ($params['FIELD_NAME'] === '')
		{
			$params['FIELD_NAME'] = 'money_' . Random::getString(5);
		}
		$params['FIELD_NAME_CURRENCY'] = trim((string)($params['FIELD_NAME_CURRENCY'] ?? ''));

		$params['VALUE'] = trim((string)($params['VALUE'] ?? ''));

		$params['EXTENDED_CURRENCY_SELECTOR'] =
			($params['EXTENDED_CURRENCY_SELECTOR'] ?? null) === 'Y'
				? 'Y'
				: 'N'
		;

		return $params;
	}

	/**
	 * Check Required Modules
	 *
	 * @return bool
	 */
	protected function checkModules(): bool
	{
		return Loader::includeModule('currency');
	}

	protected function prepareData(): void
	{
		$this->currencyList = Currency\Helpers\Editor::getListCurrency();
	}

	protected function formatResult(): void
	{
		$this->arResult['CURRENCY_LIST'] = [];

		$defaultCurrency = '';
		foreach ($this->currencyList as $currency => $currencyInfo)
		{
			if ($defaultCurrency === '' || $currencyInfo['BASE'] === 'Y')
			{
				$defaultCurrency = $currency;
			}
			$this->arResult['CURRENCY_LIST'][$currency] = $currencyInfo['NAME'];
		}

		$value = '';
		if ($this->arParams['VALUE'] !== '')
		{
			list($value, $currency) = MoneyType::unFormatFromDb($this->arParams['VALUE']);

			$value = $this->formatNumber($value, $currency);

			if ($currency !== '' && !isset($this->arResult['CURRENCY_LIST'][$currency]))
			{
				$this->arResult['CURRENCY_LIST'][$currency] = $currency;
			}
		}
		else
		{
			$currency = $defaultCurrency;
		}
		$this->arResult['VALUE_NUMBER'] = $value;
		$this->arResult['VALUE_CURRENCY'] = $currency;
	}

	protected function formatNumber($currentValue, $currentCurrency): string
	{
		if ($currentValue !== '')
		{
			$currentValue = \CCurrencyLang::formatEditValue(
				$currentValue,
				\CCurrencyLang::GetFormatDescription($currentCurrency)
			);
		}

		return $currentValue;
	}

	protected function initCore(): void
	{
		$extensions = [
			'core_money_editor',
		];
		if ($this->arParams['EXTENDED_CURRENCY_SELECTOR'] === 'Y')
		{
			$extensions[] = 'ui';
		}
		Extension::load($extensions);
	}

	public function executeComponent()
	{
		if ($this->checkModules())
		{
			$this->prepareData();
			$this->formatResult();
			$this->initCore();

			$this->includeComponentTemplate();
		}
		else
		{
			ShowError(Loc::getMessage('CMI_CURRENCY_MODULE_NOT_INSTALLED'));
		}
	}
}
