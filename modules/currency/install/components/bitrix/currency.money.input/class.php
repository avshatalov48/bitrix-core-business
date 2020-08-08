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

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class CCurrencyMoneyInputComponent extends \CBitrixComponent
{
	protected $currencyList = array();

	/**
	 * Load language file.
	 */
	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	public function onPrepareComponentParams($params)
	{
		$params['CONTROL_ID'] = !empty($params['CONTROL_ID']) ? trim($params['CONTROL_ID']) : 'bxme_'.(\Bitrix\Main\Security\Random::getString(5));
		$params['FIELD_NAME'] = !empty($params['FIELD_NAME']) ? trim($params['FIELD_NAME']) : 'money_'.(\Bitrix\Main\Security\Random::getString(5));
		$params['FIELD_NAME_CURRENCY'] = !empty($params['FIELD_NAME_CURRENCY']) ? trim($params['FIELD_NAME']) : '';
		$params['VALUE'] = $params['VALUE'] <> '' ? trim($params['VALUE']) : '';

		$params['EXTENDED_CURRENCY_SELECTOR'] = $params['EXTENDED_CURRENCY_SELECTOR'] === 'Y' ? 'Y' : 'N';

		return $params;
	}

	/**
	 * Check Required Modules
	 *
	 * @throws Exception
	 */
	protected function checkModules()
	{
		if(!Loader::includeModule('currency'))
		{
			throw new SystemException(Loc::getMessage('CMI_CURRENCY_MODULE_NOT_INSTALLED'));
		}
	}

	protected function prepareData()
	{
		$this->currencyList = \Bitrix\Currency\Helpers\Editor::getListCurrency();
	}

	protected function formatResult()
	{
		$this->arResult['CURRENCY_LIST'] = array();

		$defaultCurrency = '';
		foreach($this->currencyList as $currency => $currencyInfo)
		{
			if($defaultCurrency === '' || $currencyInfo['BASE'] == 'Y')
			{
				$defaultCurrency = $currency;
			}

			$this->arResult['CURRENCY_LIST'][$currency] = $currencyInfo['NAME'];
		}

		$this->arResult['VALUE_NUMBER'] = '';
		$this->arResult['VALUE_CURRENCY'] = '';

		if($this->arParams['VALUE'] <> '')
		{
			list($this->arResult['VALUE_NUMBER'], $this->arResult['VALUE_CURRENCY']) = explode('|', $this->arParams['VALUE']);

			$this->arResult['VALUE_NUMBER'] = $this->formatNumber($this->arResult['VALUE_NUMBER'], $this->arResult['VALUE_CURRENCY']);

			if ($this->arResult['VALUE_CURRENCY'] !== '' && !isset($this->arResult['CURRENCY_LIST'][$this->arResult['VALUE_CURRENCY']]))
				$this->arResult['CURRENCY_LIST'][$this->arResult['VALUE_CURRENCY']] = $this->arResult['VALUE_CURRENCY'];
		}
		else
		{
			$this->arResult['VALUE_CURRENCY'] = $defaultCurrency;
		}
	}

	protected function formatNumber($currentValue, $currentCurrency)
	{
		if($currentValue !== '')
		{
			$format = \CCurrencyLang::GetFormatDescription($currentCurrency);
			//TODO: in the future - remove this hack
			if ($format['THOUSANDS_VARIANT'] == \CCurrencyLang::SEP_NBSPACE)
			{
				$format['THOUSANDS_VARIANT'] = \CCurrencyLang::SEP_SPACE;
				$separators = \CCurrencyLang::GetSeparators();
				$format['THOUSANDS_SEP'] = $separators[\CCurrencyLang::SEP_SPACE];
				unset($separators);
			}
			$currentValue = \CCurrencyLang::formatValue($currentValue, $format, false);
			unset($format);
		}

		return $currentValue;
	}

	protected function initCore()
	{
		if($this->arParams['EXTENDED_CURRENCY_SELECTOR'] === 'Y')
		{
			\CJSCore::Init(array('core_money_editor', 'ui'));
		}
		else
		{
			\CJSCore::Init(array('core_money_editor'));
		}
	}

	public function executeComponent()
	{
		try
		{
			$this->checkModules();
			$this->prepareData();
			$this->formatResult();
			$this->initCore();

			$this->includeComponentTemplate();
		}
		catch(SystemException $e)
		{
			ShowError($e->getMessage());
		}
	}
}