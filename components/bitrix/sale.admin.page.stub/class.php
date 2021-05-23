<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

/**
 * Class SaleAdminPageStub
 */
class SaleAdminPageStub extends \CBitrixComponent
{
	/** @var string */
	const CRM_WIZARD_SITE_ID = "~CRM_WIZARD_SITE_ID";

	/**
	 * @return array
	 */
	private function getMap()
	{
		return [
			"sale_pay_system.php" => [
				"page" => "/shop/settings/sale_pay_system/",
				"title" => Loc::getMessage("SAPS_SALE_PAY_SYSTEM"),
			],
			"sale_order.php" => [
				"page" => "/shop/orders/",
				"title" => Loc::getMessage("SAPS_SALE_ORDER"),
			],
			"sale_cashbox.php" => [
				"page" => "/shop/settings/menu_sale_cashbox/",
				"title" => Loc::getMessage("SAPS_SALE_CASHBOX"),
			],
			"sale_cashbox_list.php" => [
				"page" => "/shop/settings/sale_cashbox_list/",
				"title" => Loc::getMessage("SAPS_SALE_CASHBOX_LIST"),
			],
			"sale_cashbox_check.php" => [
				"page" => "/shop/settings/sale_cashbox_check",
				"title" => Loc::getMessage("SAPS_SALE_CASHBOX_CHECK"),
			],
			"sale_buyers.php" => [
				"page" => "/shop/settings/menu_sale_buyers/",
				"title" => Loc::getMessage("SAPS_SALE_BUYERS"),
			],
			"sale_basket.php" => [
				"page" => "/shop/settings/sale_basket/",
				"title" => Loc::getMessage("SAPS_SALE_BASKET"),
			],
			"sale_account_admin.php" => [
				"page" => "/shop/settings/sale_account_admin/",
				"title" => Loc::getMessage("SAPS_SALE_ACCOUNT_ADMIN"),
			],
			"sale_transact_admin.php" => [
				"page" => "/shop/settings/sale_transact_admin/",
				"title" => Loc::getMessage("SAPS_SALE_TRANSACT_ADMIN"),
			],
			"cat_subscription_list.php" => [
				"page" => "/shop/settings/cat_subscription_list/",
				"title" => Loc::getMessage("SAPS_CAT_SUBSCRIPTION_LIST"),
			],
			"cat_store_list.php" => [
				"page" => "/shop/settings/cat_store_list/",
				"title" => Loc::getMessage("SAPS_CAT_STORE_LIST"),
			],
			"cat_store_document_list.php" => [
				"page" => "/shop/settings/cat_store_document_list/",
				"title" => Loc::getMessage("SAPS_CAT_STORE_DOCUMENT_LIST"),
			],
			"cat_contractor_list.php" => [
				"page" => "/shop/settings/cat_contractor_list/",
				"title" => Loc::getMessage("SAPS_CAT_CONTRACTOR_LIST"),
			],
			"sale_discount.php" => [
				"page" => "/shop/settings/sale_discount/",
				"title" => Loc::getMessage("SAPS_SALE_DISCOUNT"),
			],
			"sale_discount_coupons.php" => [
				"page" => "/shop/settings/sale_discount_coupons/",
				"title" => Loc::getMessage("SAPS_SALE_DISCOUNT_COUPONS"),
			],
			"sale_delivery_service_list.php" => [
				"page" => "/shop/settings/sale_delivery_service_list/",
				"title" => Loc::getMessage("SAPS_SALE_DELIVERY_SERVICE_LIST"),
			],
			"sale_tax.php" => [
				"page" => "/shop/settings/menu_sale_taxes/",
				"title" => Loc::getMessage("SAPS_SALE_TAX"),
			],
			"sale_tax_rate.php" => [
				"page" => "/shop/settings/sale_tax_rate/",
				"title" => Loc::getMessage("SAPS_SALE_TAX_RATE"),
			],
			"sale_tax_exempt.php" => [
				"page" => "/shop/settings/sale_tax_exempt/",
				"title" => Loc::getMessage("SAPS_SALE_TAX_EXEMPT"),
			],
			"cat_vat_admin.php" => [
				"page" => "/shop/settings/cat_vat_admin/",
				"title" => Loc::getMessage("SAPS_CAT_VAT_ADMIN"),
			],
			"sale_person_type.php" => [
				"page" => "/shop/settings/sale_person_type/",
				"title" => Loc::getMessage("SAPS_SALE_PERSON_TYPE"),
			],
			"sale_location_node_list.php" => [
				"page" => "/shop/settings/menu_sale_locations/",
				"title" => Loc::getMessage("SAPS_SALE_LOCATION_NODE_LIST"),
			],
			"sale_location_group_list.php" => [
				"page" => "/shop/settings/sale_location_group_list/",
				"title" => Loc::getMessage("SAPS_SALE_LOCATION_GROUP_LIST"),
			],
			"sale_location_type_list.php" => [
				"page" => "/shop/settings/sale_location_type_list/",
				"title" => Loc::getMessage("SAPS_SALE_LOCATION_TYPE_LIST")
			],
			"cat_group_admin.php" => [
				"page" => "/shop/settings/cat_group_admin/",
				"title" => Loc::getMessage("SAPS_CAT_GROUP_ADMIN"),
			],
			"cat_round_list.php" => [
				"page" => "/shop/settings/cat_round_list/",
				"title" => Loc::getMessage("SAPS_CAT_ROUND_LIST"),
			],
			"cat_extra.php" => [
				"page" => "/shop/settings/cat_extra/",
				"title" => Loc::getMessage("SAPS_CAT_EXTRA"),
			],
			"cat_measure_list.php" => [
				"page" => "/shop/settings/cat_measure_list/",
				"title" => Loc::getMessage("SAPS_CAT_MEASURE_LIST"),
			],
		];
	}

	/**
	 * @throws Main\SystemException
	 */
	private function initCurrentPageMap()
	{
		$uri = new Main\Web\Uri(Main\Application::getInstance()->getContext()->getRequest()->getRequestUri());
		foreach ($this->getMap() as $adminPage => $crmPage)
		{
			if (mb_strpos($uri->getUri(), $adminPage) !== false)
			{
				$this->arResult["admin_page"] = $adminPage;
				$this->arResult["crm"] = $crmPage;
				break;
			}
		}
	}

	/**
	 * @param $link
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private static function getLink($link)
	{
		$serverName = self::getServerName();
		if ($serverName)
		{
			return (Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")."://".$serverName.$link;
		}

		return "";
	}

	/**
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private static function getServerName()
	{
		$crmSiteId = Main\Config\Option::get("sale", self::CRM_WIZARD_SITE_ID, null);
		if ($crmSiteId)
		{
			$site = Main\SiteTable::getList([
				"select" => ["SERVER_NAME"],
				"filter" => ["LID" => $crmSiteId]
			])->fetch();

			if ($site && isset($site["SERVER_NAME"]) && !empty($site["SERVER_NAME"]))
			{
				return $site["SERVER_NAME"];
			}
		}
		else
		{
			$site = Main\SiteTable::getList([
				"select" => ["SERVER_NAME"],
				"filter" => ["=DEF" => "Y"]
			])->fetch();

			if ($site && isset($site["SERVER_NAME"]) && !empty($site["SERVER_NAME"]))
			{
				return $site["SERVER_NAME"];
			}
			elseif (Main\Config\Option::get("main", "server_name"))
			{
				return Main\Config\Option::get("main", "server_name");
			}
		}

		return "";
	}


	/**
	 * @return mixed|void
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function executeComponent()
	{
		global $APPLICATION, $USER;

		$this->initCurrentPageMap();
		$this->arResult["crm_link"] = self::getLink($this->arResult["crm"]["page"]);
		$this->arResult["current_page"] = $APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID.'&admin_panel=Y';

		$request = Main\Context::getCurrent()->getRequest();
		if ($request->get('admin_panel') === 'Y')
		{
			Option::set('sale', \Bitrix\Sale\Update\CrmEntityCreatorStepper::PREFIX_OPTION_ADMIN_PANEL_IS_ENABLED.$USER->GetID(), 'Y');
			LocalRedirect($APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID);
		}

		$this->includeComponentTemplate();
	}
}
