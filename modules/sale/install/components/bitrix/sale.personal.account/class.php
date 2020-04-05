<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
	Bitrix\Main\Type\Date,
	Bitrix\Main\Localization\Loc;

class PersonalAccountComponent extends CBitrixComponent
{
	/**
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function checkRequirements()
	{
		global $USER, $APPLICATION;

		if (!Loader::includeModule("sale"))
		{
			ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
			return false;
		}

		if (!CBXFeatures::IsFeatureEnabled('SaleAccounts'))
			return false;

		if (!$USER->IsAuthorized())
		{
			$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"), false, false, 'N', false);
			return false;
		}

		return true;
	}

	/**
	 * PersonalAccountComponent constructor.
	 *
	 * @param null $component
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);

		Loc::loadMessages(__FILE__);
	}

	/**
	 * Execute component
	 *
	 * @return void
	 */
	public function executeComponent()
	{
		global $USER, $APPLICATION;

		if (!$this->checkRequirements())
		{
			return;
		}

		if($this->arParams["SET_TITLE"] == 'Y')
		{
			$APPLICATION->SetTitle(GetMessage("SPA_TITLE"));
		}

		$resultTemplate= Array();

		$objDateTime = new Date();
		$this->arResult["DATE"] = $objDateTime->toString();

		$accountList = CSaleUserAccount::GetList(
			array("CURRENCY" => "ASC"),
			array("USER_ID" => (int)($USER->GetID())),
			false,
			false,
			array("ID", "CURRENT_BUDGET", "CURRENCY", "TIMESTAMP_X")
		);

		$currencyList = array();
		$currencyIterator = Bitrix\Currency\CurrencyTable::getList(array(
			'select' => array('CURRENCY', 'FULL_NAME' => 'CURRENT_LANG_FORMAT.FULL_NAME', 'SORT'),
			'order' => array('SORT' => 'ASC', 'CURRENCY' => 'ASC')
		));
		while ($currency = $currencyIterator->fetch())		
		{
			$currencyList[$currency['CURRENCY']] = (string)$currency['FULL_NAME'];
		}

		$baseCurrencyCode = Bitrix\Sale\Internals\SiteCurrencyTable::getSiteCurrency(SITE_ID);
		$this->arResult['BASE_CURRENCY'] =  array(
			"CODE" => $baseCurrencyCode,
			"TEXT" => $currencyList[$baseCurrencyCode]
		);
			
		while ($account = $accountList->Fetch())
		{
			$resultTemplate["CURRENCY"] = $account["CURRENCY"];
			$resultTemplate["ACCOUNT_LIST"] = $account;
			$resultTemplate["INFO"] = Loc::getMessage(
				"SPA_IN_CUR",
				array(
					"#CURRENCY#" => $resultTemplate["CURRENCY"],
					"#SUM#" => SaleFormatCurrency($account["CURRENT_BUDGET"], $account["CURRENCY"]),
				));
			$resultTemplate["CURRENCY_FULL_NAME"] = $currencyList[$account['CURRENCY']];
			$resultTemplate["SUM"] =  SaleFormatCurrency($account["CURRENT_BUDGET"], $account["CURRENCY"]);
			$this->arResult["ACCOUNT_LIST"][] = $resultTemplate;
		}
		if (empty($this->arResult["ACCOUNT_LIST"]))
		{
			$this->arResult["ACCOUNT_LIST"][] = array(
				'SUM' => SaleFormatCurrency(0, $this->arResult['BASE_CURRENCY']['CODE']),
				'CURRENCY' => $this->arResult['BASE_CURRENCY']['CODE'],
				'CURRENCY_FULL_NAME' => $this->arResult['BASE_CURRENCY']['TEXT']
			);
			$this->arResult["ERROR_MESSAGE"] = Loc::getMessage("SPA_NO_ACCOUNT");
		}

		$this->includeComponentTemplate();
	}
}
