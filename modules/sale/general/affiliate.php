<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Session\Session;
use Bitrix\Main\Web\Cookie;

IncludeModuleLangFile(__FILE__);

$GLOBALS["SALE_AFFILIATE"] = Array();
$GLOBALS["SALE_AFFILIATE_TIER_TMP_CACHE"] = Array();
$GLOBALS["SALE_PRODUCT_SECTION_CACHE"] = Array();
$GLOBALS["BASE_LANG_CURRENCIES"] = array();
$GLOBALS["SALE_CONVERT_CURRENCY_CACHE"] = array();

class CAllSaleAffiliate
{
	private const VALUE_STORAGE_ID = 'SALE_AFFILIATE';
	/**
	 * Session object.
	 *
	 * If session is not accessible, returns null.
	 *
	 * @return Session|null
	 */
	private static function getSession(): ?Session
	{
		/** @var Session $session */
		$session = Application::getInstance()->getSession();
		if (!$session->isAccessible())
		{
			return null;
		}

		return $session;
	}

	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		/** @global CDatabase $DB */
		global $DB;

		if ((is_set($arFields, "SITE_ID") || $ACTION=="ADD") && $arFields["SITE_ID"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("ACGA1_NO_SITE"), "EMPTY_SITE_ID");
			return false;
		}
		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && intval($arFields["USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("ACGA1_NO_USER"), "EMPTY_USER_ID");
			return false;
		}
		if (is_set($arFields, "USER_ID"))
		{
			$dbUser = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbUser->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["USER_ID"], GetMessage("SKGU_NO_USER")), "ERROR_NO_USER_ID");
				return false;
			}
		}
		if ((is_set($arFields, "PLAN_ID") || $ACTION=="ADD") && intval($arFields["PLAN_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("ACGA1_NO_PLAN"), "EMPTY_PLAN_ID");
			return false;
		}

		$ID = intval($ID);
		$arAffiliate = false;
		if ($ACTION != "ADD")
		{
			if ($ID <= 0)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("ACGA1_ERROR_FUNC"), "FUNCTION_ERROR");
				return false;
			}
			else
			{
				$arAffiliate = CSaleAffiliate::GetByID($ID);
				if (!$arAffiliate)
				{
					$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("ACGA1_NO_AFFILIATE")), "NO_AFFILIATE");
					return false;
				}
			}
		}

		if (is_set($arFields, "AFFILIATE_ID") && intval($arFields["AFFILIATE_ID"]) <= 0)
			$arFields["AFFILIATE_ID"] = false;

		if ((is_set($arFields, "ACTIVE") || $ACTION=="ADD") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";

		if ((is_set($arFields, "FIX_PLAN") || $ACTION=="ADD") && $arFields["FIX_PLAN"] != "Y")
			$arFields["FIX_PLAN"] = "N";

		if ((is_set($arFields, "DATE_CREATE") || $ACTION=="ADD") && (!$GLOBALS["DB"]->IsDate($arFields["DATE_CREATE"], false, LANG, "FULL")))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("ACGA1_BAD_DATE"), "ERROR_DATE_CREATE");
			return false;
		}

		if (is_set($arFields, "PAID_SUM"))
		{
			$arFields["PAID_SUM"] = str_replace(",", ".", $arFields["PAID_SUM"]);
			$arFields["PAID_SUM"] = DoubleVal($arFields["PAID_SUM"]);
		}

		if (is_set($arFields, "APPROVED_SUM"))
		{
			$arFields["APPROVED_SUM"] = str_replace(",", ".", $arFields["APPROVED_SUM"]);
			$arFields["APPROVED_SUM"] = DoubleVal($arFields["APPROVED_SUM"]);
		}

		if (is_set($arFields, "PENDING_SUM"))
		{
			$arFields["PENDING_SUM"] = str_replace(",", ".", $arFields["PENDING_SUM"]);
			$arFields["PENDING_SUM"] = DoubleVal($arFields["PENDING_SUM"]);
		}

		if (is_set($arFields, "ITEMS_NUMBER"))
			$arFields["ITEMS_NUMBER"] = intval($arFields["ITEMS_NUMBER"]);

		if (is_set($arFields, "ITEMS_SUM"))
		{
			$arFields["ITEMS_SUM"] = str_replace(",", ".", $arFields["ITEMS_SUM"]);
			$arFields["ITEMS_SUM"] = DoubleVal($arFields["ITEMS_SUM"]);
		}

		unset($arFields['TIMESTAMP_X']);
		$arFields['~TIMESTAMP_X'] = $DB->GetNowFunction();

		return True;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);

		$db_events = GetModuleEvents("sale", "OnBeforeAffiliateDelete");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID))===false)
				return false;

		if ($ID <= 0)
			return False;

		if(!(CSaleAffiliateTransact::OnAffiliateDelete($ID)))
			return false;

		unset($GLOBALS["SALE_AFFILIATE"]["SALE_AFFILIATE_CACHE_".$ID]);

		$bResult = $DB->Query("DELETE FROM b_sale_affiliate WHERE ID = ".$ID." ", true);

		$events = GetModuleEvents("sale", "OnAfterAffiliateDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID, $bResult));

		return $bResult;
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return false;

		if (isset($GLOBALS["SALE_AFFILIATE"]["SALE_AFFILIATE_CACHE_".$ID]) && is_array($GLOBALS["SALE_AFFILIATE"]["SALE_AFFILIATE_CACHE_".$ID]))
		{
			return $GLOBALS["SALE_AFFILIATE"]["SALE_AFFILIATE_CACHE_".$ID];
		}
		else
		{
			$strSql =
				"SELECT A.ID, A.SITE_ID, A.USER_ID, A.AFFILIATE_ID, A.PLAN_ID, A.ACTIVE, A.PAID_SUM, ".
				"	A.APPROVED_SUM, A.PENDING_SUM, A.ITEMS_NUMBER, A.ITEMS_SUM, A.AFF_SITE, A.AFF_DESCRIPTION, A.FIX_PLAN, ".
				"	".$DB->DateToCharFunction("A.TIMESTAMP_X", "FULL")." as TIMESTAMP_X, ".
				"	".$DB->DateToCharFunction("A.DATE_CREATE", "FULL")." as DATE_CREATE, ".
				"	".$DB->DateToCharFunction("A.LAST_CALCULATE", "FULL")." as LAST_CALCULATE ".
				"FROM b_sale_affiliate A ".
				"WHERE A.ID = ".$ID." ";

			$db_res = $DB->Query($strSql);
			if ($res = $db_res->Fetch())
			{
				$GLOBALS["SALE_AFFILIATE"]["SALE_AFFILIATE_CACHE_".$ID] = $res;
				return $GLOBALS["SALE_AFFILIATE"]["SALE_AFFILIATE_CACHE_".$ID];
			}
		}

		return false;
	}

	public static function GetAffiliate($affiliateID = 0)
	{
		$request = Context::getCurrent()->getRequest();
		$session = static::getSession();

		$affiliateID = (int)$affiliateID;

		if ($affiliateID <= 0)
		{
			$affiliateParam = Option::get('sale', 'affiliate_param_name', 'partner');
			if ($affiliateParam !== '')
			{
				$rawValue = $request->getQuery($affiliateParam);
				if (!is_array($rawValue))
				{
					$affiliateID = (int)$rawValue;
				}
				unset($rawValue);
			}
		}

		if ($affiliateID <= 0 && $session !== null)
		{
			if ($session->has(self::VALUE_STORAGE_ID))
			{
				$affiliateID = (int)$session->get(self::VALUE_STORAGE_ID);
			}
		}

		if ($affiliateID <= 0)
		{
			$affiliateID = (int)$request->getCookie(self::VALUE_STORAGE_ID);
		}

		if ($affiliateID > 0)
		{
			if ($session !== null)
			{
				$session->set(self::VALUE_STORAGE_ID, $affiliateID);
			}

			$cookieTime = (int)Option::get('sale', 'affiliate_life_time');
			$cookieTime =
				$cookieTime > 0
					? time() + $cookieTime * 86400
					: null
			;
			$secureCookie = Option::get('sale', 'use_secure_cookies') === 'Y' && $request->isHttps();

			$cookie = new Cookie(self::VALUE_STORAGE_ID, (string)$affiliateID, $cookieTime);
			$cookie
				->setSecure($secureCookie)
				->setHttpOnly(false)
				->setSpread(Cookie::SPREAD_DOMAIN | Cookie::SPREAD_SITES)
			;

			$response = Context::getCurrent()->getResponse();

			$response->addCookie($cookie);

			unset($response);
			unset($cookie);

		}
		unset($session);
		unset($request);

		return $affiliateID;
	}

	public static function Calculate($dateFrom = false, $dateTo = false, $datePlanFrom = false, $datePlanTo = false)
	{
		global $DB;

		$arFilter = array(
			"ACTIVE" => "Y",
			"ORDER_ALLOW_DELIVERY" => "Y"
		);
		if (!$dateFrom || $dateFrom == '')
		{
			if (!$dateTo || $dateTo == '')
				$dateTo = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()+CTimeZone::GetOffset());

			$arFilter[">=ORDER_DATE_ALLOW_DELIVERY"] = $dateFrom;
			$arFilter["<ORDER_DATE_ALLOW_DELIVERY"] = $dateTo;
		}
		else
		{
			$dateTo = false;
		}

		if (!$datePlanFrom || $datePlanFrom == '')
			$datePlanFrom = $dateFrom;

		if (!$datePlanTo || $datePlanTo == '')
			$datePlanTo = $dateTo;

		$dbAffiliates = CSaleAffiliate::GetList(
			array(),
			$arFilter,
			array(
				"ID",
				"SITE_ID",
				"USER_ID",
				"AFFILIATE_ID",
				"PLAN_ID",
				"ACTIVE",
				"TIMESTAMP_X",
				"DATE_CREATE",
				"PAID_SUM",
				"APPROVED_SUM",
				"PENDING_SUM",
				"ITEMS_NUMBER",
				"ITEMS_SUM",
				"FIX_PLAN",
				"MAX" => "ORDER_ID"
			)
		);
		while ($arAffiliates = $dbAffiliates->Fetch())
			CSaleAffiliate::CalculateAffiliate($arAffiliates, $dateFrom, $dateTo, $datePlanFrom, $datePlanTo);

	}

	public static function CheckAffiliateFunc($affiliate)
	{
		if (is_array($affiliate))
		{
			$arAffiliate = $affiliate;
			$affiliateID = intval($arAffiliate["ID"]);

			if ($affiliateID <= 0)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("ACGA1_ERROR_FUNC"), "FUNCTION_ERROR");
				return false;
			}
		}
		else
		{
			$affiliateID = intval($affiliate);
			if ($affiliateID <= 0)
				return False;

			$dbAffiliate = CSaleAffiliate::GetList(
				array(),
				array("ID" => $affiliateID, "ACTIVE" => "Y", "PLAN_ACTIVE" => "Y"),
				false,
				false,
				array("ID", "SITE_ID", "USER_ID", "AFFILIATE_ID", "PLAN_ID", "ACTIVE", "TIMESTAMP_X", "DATE_CREATE", "PAID_SUM", "APPROVED_SUM", "PENDING_SUM", "ITEMS_NUMBER", "ITEMS_SUM", "LAST_CALCULATE", "FIX_PLAN", "PLAN_BASE_RATE", "PLAN_BASE_RATE_TYPE", "PLAN_BASE_RATE_CURRENCY")
			);
			$arAffiliate = $dbAffiliate->Fetch();
			if (!$arAffiliate)
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $affiliateID, GetMessage("ACGA1_NO_AFFILIATE")), "NO_AFFILIATE");
				return false;
			}
		}

		return $arAffiliate;
	}

	public static function SetAffiliatePlan($affiliate, $dateFrom = false, $dateTo = false)
	{
		global $DB;

		$arAffiliate = CSaleAffiliate::CheckAffiliateFunc($affiliate);
		if (!$arAffiliate)
			return False;

		// If not fixed plan
		$affiliateID = intval($arAffiliate["ID"]);

		// If fixed plan
		if ($arAffiliate["FIX_PLAN"] == "Y")
		{
			$dbAffiliatePlan = CSaleAffiliatePlan::GetList(
				array(),
				array(
					"ID" => $arAffiliate["PLAN_ID"],
					"ACTIVE" => "Y",
					"SITE_ID" => $arAffiliate["SITE_ID"]
				),
				false,
				false,
				array("ID", "SITE_ID", "NAME", "TIMESTAMP_X", "ACTIVE", "BASE_RATE", "BASE_RATE_TYPE", "BASE_RATE_CURRENCY", "MIN_PAY", "MIN_PLAN_VALUE")
			);
			$arAffiliatePlan = $dbAffiliatePlan->Fetch();
			if (!$arAffiliatePlan)
			{
				$arFields = array(
					"ACTIVE" => "N"
				);
				$res = CSaleAffiliate::Update($affiliateID, $arFields);
				if ($res)
					$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $affiliateID, GetMessage("ACGA1_NO_PLAN_DEACT")), "NO_PLAN");

				return false;
			}

			return $arAffiliatePlan;
		}

		if (!$dateFrom || $dateFrom == '')
		{
			if ($arAffiliate["LAST_CALCULATE"] <> '')
				$dateFrom = $arAffiliate["LAST_CALCULATE"];
			else
				$dateFrom = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), mktime(0, 0, 0, 1, 1, 1990));
		}
		if (!$dateTo || $dateTo == '')
			$dateTo = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()+CTimeZone::GetOffset());

		$affiliatePlanType = COption::GetOptionString("sale", "affiliate_plan_type", "N");

		$itemsValue = 0;

		if ($affiliatePlanType == "N")
		{
			$dbOrders = \Bitrix\Sale\Internals\OrderTable::getList(
				array(
					'filter' => array(
						"=ALLOW_DELIVERY" => "Y",
						">=DATE_ALLOW_DELIVERY" => $dateFrom,
						"<DATE_ALLOW_DELIVERY" => $dateTo,
						"=AFFILIATE_ID" => $affiliateID,
						"=LID" => $arAffiliate["SITE_ID"],
					),
					'runtime' => array(
						new \Bitrix\Main\Entity\ExpressionField('BASKET_QUANTITY', 'SUM(%s)', array('BASKET.QUANTITY'))
					),
					'select' => array('BASKET_QUANTITY')
				)
			);
			if ($arOrder = $dbOrders->fetch())
				$itemsValue = $arOrder["BASKET_QUANTITY"];
		}
		else
		{
			$dbOrders = \Bitrix\Sale\Internals\OrderTable::getList(
				array(
					'filter' => array(
						"=ALLOW_DELIVERY" => "Y",
						">=DATE_ALLOW_DELIVERY" => $dateFrom,
						"<DATE_ALLOW_DELIVERY" => $dateTo,
						"=AFFILIATE_ID" => $affiliateID,
						"=LID" => $arAffiliate["SITE_ID"],
					),
					'runtime' => array(
						new \Bitrix\Main\Entity\ExpressionField('ORDER_SUM_PRICE', 'SUM(%s)', array('PRICE'))
					),
					'select' => array('ORDER_SUM_PRICE')
				)
			);
			if ($arOrder = $dbOrders->fetch())
				$price = $arOrder["ORDER_SUM_PRICE"];

			$dbOrders = \Bitrix\Sale\Internals\OrderTable::getList(
				array(
					'filter' => array(
						"=ALLOW_DELIVERY" => "Y",
						">=DATE_ALLOW_DELIVERY" => $dateFrom,
						"<DATE_ALLOW_DELIVERY" => $dateTo,
						"=AFFILIATE_ID" => $affiliateID,
						"=LID" => $arAffiliate["SITE_ID"],
					),
					'runtime' => array(
						new \Bitrix\Main\Entity\ExpressionField('ORDER_PRICE_DELIVERY', 'SUM(%s)', array('PRICE_DELIVERY'))
					),
					'select' => array('ORDER_PRICE_DELIVERY')
				)
			);
			if ($arOrder = $dbOrders->fetch())
				$priceDelivery = $arOrder["ORDER_PRICE_DELIVERY"];

			$dbOrders = \Bitrix\Sale\Internals\OrderTable::getList(
				array(
					'filter' => array(
						"=ALLOW_DELIVERY" => "Y",
						">=DATE_ALLOW_DELIVERY" => $dateFrom,
						"<DATE_ALLOW_DELIVERY" => $dateTo,
						"=AFFILIATE_ID" => $affiliateID,
						"=LID" => $arAffiliate["SITE_ID"],
					),
					'runtime' => array(
						new \Bitrix\Main\Entity\ExpressionField('ORDER_TAX_VALUE', 'SUM(%s)', array('TAX_VALUE'))
					),
					'select' => array('ORDER_TAX_VALUE')
				)
			);
			if ($arOrder = $dbOrders->fetch())
				$priceTax = $arOrder["ORDER_TAX_VALUE"];

			$itemsValue = $price - $priceDelivery - $priceTax;
		}

		if (DoubleVal($itemsValue) > 0)
		{
			$dbAffiliatePlan = CSaleAffiliatePlan::GetList(
				array("MIN_PLAN_VALUE" => "DESC"),
				array(
					"+<=MIN_PLAN_VALUE" => $itemsValue,
					"ACTIVE" => "Y",
					"SITE_ID" => $arAffiliate["SITE_ID"]
				),
				false,
				false,
				array("ID", "SITE_ID", "NAME", "TIMESTAMP_X", "ACTIVE", "BASE_RATE", "BASE_RATE_TYPE", "BASE_RATE_CURRENCY", "MIN_PAY", "MIN_PLAN_VALUE")
			);
			if ($arAffiliatePlan = $dbAffiliatePlan->Fetch())
			{
				if ($arAffiliate["FIX_PLAN"] != "Y")
				{
					$arFields = array(
						"PLAN_ID" => $arAffiliatePlan["ID"]
					);
					$res = CSaleAffiliate::Update($affiliateID, $arFields);
					if (!$res)
						return false;
				}
			}
			else
			{
				$arFields = array(
					"ACTIVE" => "N"
				);
				$res = CSaleAffiliate::Update($affiliateID, $arFields);
				if ($res)
					$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $affiliateID, GetMessage("ACGA1_NO_PLAN_DEACT")), "NO_PLAN");

				return false;
			}

			return $arAffiliatePlan;
		}
		else
			return true;
	}

	public static function CalculateAffiliate($affiliate, $dateFrom = false, $dateTo = false, $datePlanFrom = false, $datePlanTo = false)
	{
		global $DB;

		$disableCalculate = false;

		// Prepare function params - affiliate
		$arAffiliate = CSaleAffiliate::CheckAffiliateFunc($affiliate);
		if (!$arAffiliate)
			return False;

		$db_events = GetModuleEvents("sale", "OnBeforeAffiliateCalculate");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, Array(&$arAffiliate, &$dateFrom, &$dateTo, &$datePlanFrom, &$datePlanTo, &$disableCalculate)) === false)
			{
				return false;
			}
		}

		$affiliateID = intval($arAffiliate["ID"]);
		if ($disableCalculate === true)
		{
			return True;
		}

		if (!$dateFrom || $dateFrom == '')
		{
			if ($arAffiliate["LAST_CALCULATE"] <> '')
				$dateFrom = $arAffiliate["LAST_CALCULATE"];
			else
				$dateFrom = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), mktime(0, 0, 0, 1, 1, 1990));
		}
		if (!$dateTo || $dateTo == '')
			$dateTo = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()+CTimeZone::GetOffset());

		// Get affiliate plan
		$arAffiliatePlan = CSaleAffiliate::SetAffiliatePlan($arAffiliate, $datePlanFrom, $datePlanTo);

		if (!$arAffiliatePlan)
			return False;
		if ($arAffiliatePlan && !is_array($arAffiliatePlan))
			return true;

		// Get affiliate plan params
		$arPlanSections = array();
		$dbPlanSection = CSaleAffiliatePlanSection::GetList(
			array(),
			array("PLAN_ID" => $arAffiliate["PLAN_ID"]),
			false,
			false,
			array("ID", "MODULE_ID", "SECTION_ID", "RATE", "RATE_TYPE", "RATE_CURRENCY")
		);
		while ($arPlanSection = $dbPlanSection->Fetch())
		{
			$arPlanSections[$arPlanSection["MODULE_ID"].$arPlanSection["SECTION_ID"]] = $arPlanSection;
		}

		// Get affiliate parents
		$arAffiliateParents = array();

		$affiliateParent = intval($arAffiliate["AFFILIATE_ID"]);
		$count = 0;
		while (($affiliateParent > 0) && ($count < 5))
		{
			$dbAffiliateParent = CSaleAffiliate::GetList(
				array(),
				array("ID" => $affiliateParent, "ACTIVE" => "Y"),
				false,
				false,
				array("ID", "AFFILIATE_ID")
			);
			if ($arAffiliateParent = $dbAffiliateParent->Fetch())
			{
				$count++;
				$arAffiliateParents[] = $affiliateParent;
				$affiliateParent = intval($arAffiliateParent["AFFILIATE_ID"]);
			}
			else
			{
				$affiliateParent = 0;
			}
		}

		// Get tier
		if (!array_key_exists("SALE_AFFILIATE_TIER_TMP_CACHE", $GLOBALS))
			$GLOBALS["SALE_AFFILIATE_TIER_TMP_CACHE"] = array();

		if (!array_key_exists($arAffiliate["SITE_ID"], $GLOBALS["SALE_AFFILIATE_TIER_TMP_CACHE"]))
		{
			$dbAffiliateTier = CSaleAffiliateTier::GetList(array(), array("SITE_ID" => $arAffiliate["SITE_ID"]), false, false, array("RATE1", "RATE2", "RATE3", "RATE4", "RATE5"));
			if ($arAffiliateTier = $dbAffiliateTier->Fetch())
				$GLOBALS["SALE_AFFILIATE_TIER_TMP_CACHE"][$arAffiliate["SITE_ID"]] = array(DoubleVal($arAffiliateTier["RATE1"]), DoubleVal($arAffiliateTier["RATE2"]), DoubleVal($arAffiliateTier["RATE3"]), DoubleVal($arAffiliateTier["RATE4"]), DoubleVal($arAffiliateTier["RATE5"]));
			else
				$GLOBALS["SALE_AFFILIATE_TIER_TMP_CACHE"][$arAffiliate["SITE_ID"]] = array(0, 0, 0, 0, 0);
		}

		// Orders cicle
		$affiliateSum = 0;
		$affiliateCurrency = CSaleLang::GetLangCurrency($arAffiliate["SITE_ID"]);

		$dbOrders = \Bitrix\Sale\Internals\OrderTable::getList(
			array(
				'filter' => array(
					"=ALLOW_DELIVERY" => 'Y',
					">=DATE_ALLOW_DELIVERY" => $dateFrom,
					"<DATE_ALLOW_DELIVERY" => $dateTo,
					"=AFFILIATE_ID" => $affiliateID,
					"=LID" => $arAffiliate["SITE_ID"],
					"=CANCELED" => 'N',
				),
				'select' => array(
					"ID",
					"LID",
					"PRICE_DELIVERY",
					"PRICE",
					"CURRENCY",
					"TAX_VALUE",
					"AFFILIATE_ID",
					"BASKET_QUANTITY" => 'BASKET.QUANTITY',
					"BASKET_PRODUCT_ID" => 'BASKET.PRODUCT_ID',
					"BASKET_MODULE" => 'BASKET.MODULE',
					"BASKET_PRICE" => 'BASKET.PRICE',
					"BASKET_CURRENCY" => 'BASKET.CURRENCY',
					"BASKET_DISCOUNT_PRICE" => 'BASKET.DISCOUNT_PRICE'
				),
				'order' => array('ID' => 'ASC')
			)
		);
		while ($arOrder = $dbOrders->fetch())
		{
			$arProductSections = array();

			if (!array_key_exists("SALE_PRODUCT_SECTION_CACHE", $GLOBALS))
				$GLOBALS["SALE_PRODUCT_SECTION_CACHE"] = array();

			if (array_key_exists($arOrder["BASKET_MODULE"].$arOrder["BASKET_PRODUCT_ID"], $GLOBALS["SALE_PRODUCT_SECTION_CACHE"]))
			{
				$arProductSections = $GLOBALS["SALE_PRODUCT_SECTION_CACHE"][$arOrder["BASKET_MODULE"].$arOrder["BASKET_PRODUCT_ID"]];
				unset($GLOBALS["SALE_PRODUCT_SECTION_CACHE"][$arOrder["BASKET_MODULE"].$arOrder["BASKET_PRODUCT_ID"]]);
				$GLOBALS["SALE_PRODUCT_SECTION_CACHE"] = $GLOBALS["SALE_PRODUCT_SECTION_CACHE"] + array($arOrder["BASKET_MODULE"].$arOrder["BASKET_PRODUCT_ID"] => $arProductSections);
			}
			else
			{
				if ($arOrder["BASKET_MODULE"] == "catalog")
				{
					CModule::IncludeModule("iblock");
					CModule::IncludeModule("catalog");

					$arSku = CCatalogSku::GetProductInfo($arOrder["BASKET_PRODUCT_ID"]);
					if ($arSku && count($arSku) > 0)
						$elementId = $arSku["ID"];
					else
						$elementId = $arOrder["BASKET_PRODUCT_ID"];

					$elementSectionIterator = Bitrix\Iblock\SectionElementTable::getList(array(
						'select' => array('IBLOCK_SECTION_ID'),
						'filter' => array('=IBLOCK_ELEMENT_ID' => $elementId, '=ADDITIONAL_PROPERTY_ID' => null),
					));
					$elementSectionList = [];
					while ($elementSection = $elementSectionIterator->fetch())
					{
						$arSectionsChains = \CIBlockSection::GetNavChain(0, $elementSection['IBLOCK_SECTION_ID'], array('ID'), true);
						foreach ($arSectionsChains as $arSectionsChain)
						{
							$elementSectionList[$arSectionsChain['ID']] = $arSectionsChain['ID'];
						}
					}
					unset($elementSectionIterator);

					if ($elementSectionList)
					{
						sort($elementSectionList);
						$sectionIterator = Bitrix\Iblock\SectionTable::getList(array(
							'select' => array('ID', 'LEFT_MARGIN'),
							'filter' => array('@ID' => $elementSectionList),
							'order' => array('LEFT_MARGIN' => 'DESC')
						));
						while($section = $sectionIterator->fetch())
						{
							$arProductSections[] = $section['ID'];
						}
						unset($sectionIterator);
					}
					unset($elementSectionList);
				}
				else
				{
					$events = GetModuleEvents("sale", "OnAffiliateGetSections");
					if ($arEvent = $events->Fetch())
						$arProductSections = ExecuteModuleEventEx($arEvent, Array($arOrder["BASKET_MODULE"], $arOrder["BASKET_PRODUCT_ID"]));
				}

				$GLOBALS["SALE_PRODUCT_SECTION_CACHE"] = $GLOBALS["SALE_PRODUCT_SECTION_CACHE"] + array($arOrder["BASKET_MODULE"].$arOrder["BASKET_PRODUCT_ID"] => $arProductSections);
				if (count($GLOBALS["SALE_PRODUCT_SECTION_CACHE"]) > 20)
					array_shift($GLOBALS["SALE_PRODUCT_SECTION_CACHE"]);
			}

			$realRate = $arAffiliatePlan["BASE_RATE"];
			$realRateType = $arAffiliatePlan["BASE_RATE_TYPE"];
			$realRateCurrency = $arAffiliatePlan["BASE_RATE_CURRENCY"];

			$coountArProd = count($arProductSections);
			for ($i = 0; $i < $coountArProd; $i++)
			{
				if (!empty($arPlanSections[$arOrder["BASKET_MODULE"].$arProductSections[$i]]))
				{
					$realRate = $arPlanSections[$arOrder["BASKET_MODULE"].$arProductSections[$i]]["RATE"];
					$realRateType = $arPlanSections[$arOrder["BASKET_MODULE"].$arProductSections[$i]]["RATE_TYPE"];
					$realRateCurrency = $arPlanSections[$arOrder["BASKET_MODULE"].$arProductSections[$i]]["RATE_CURRENCY"];
					break;
				}
			}

			if ($realRateType == "P")
			{
				if ($arOrder["CURRENCY"] != $affiliateCurrency)
				{
					if (!array_key_exists("SALE_CONVERT_CURRENCY_CACHE", $GLOBALS))
						$GLOBALS["SALE_CONVERT_CURRENCY_CACHE"] = array();

					if (!array_key_exists($arOrder["CURRENCY"]."-".$affiliateCurrency, $GLOBALS["SALE_CONVERT_CURRENCY_CACHE"]))
						$GLOBALS["SALE_CONVERT_CURRENCY_CACHE"][$arOrder["CURRENCY"]."-".$affiliateCurrency] = CCurrencyRates::GetConvertFactor($arOrder["CURRENCY"], $affiliateCurrency);

					$affiliateSum += \Bitrix\Sale\PriceMaths::roundPrecision((($arOrder["BASKET_PRICE"] * $arOrder["BASKET_QUANTITY"]) * $GLOBALS["SALE_CONVERT_CURRENCY_CACHE"][$arOrder["CURRENCY"]."-".$affiliateCurrency] * $realRate) / 100);
				}
				else
				{
					$affiliateSum += \Bitrix\Sale\PriceMaths::roundPrecision((($arOrder["BASKET_PRICE"] * $arOrder["BASKET_QUANTITY"]) * $realRate) / 100);
				}
			}
			else
			{
				if ($realRateCurrency != $affiliateCurrency)
				{
					if (!array_key_exists("SALE_CONVERT_CURRENCY_CACHE", $GLOBALS))
						$GLOBALS["SALE_CONVERT_CURRENCY_CACHE"] = array();

					if (!array_key_exists($realRateCurrency."-".$affiliateCurrency, $GLOBALS["SALE_CONVERT_CURRENCY_CACHE"]))
						$GLOBALS["SALE_CONVERT_CURRENCY_CACHE"][$realRateCurrency."-".$affiliateCurrency] = CCurrencyRates::GetConvertFactor($realRateCurrency, $affiliateCurrency);

					$affiliateSum += roundEx($realRate * $GLOBALS["SALE_CONVERT_CURRENCY_CACHE"][$realRateCurrency."-".$affiliateCurrency], SALE_VALUE_PRECISION);
				}
				else
				{
					$affiliateSum += roundEx($realRate, SALE_VALUE_PRECISION);
				}
			}
		}

		$arFields = array(
			"=PENDING_SUM" => "PENDING_SUM + ".$affiliateSum,
			"LAST_CALCULATE" => $dateTo
		);
		$res = CSaleAffiliate::Update($affiliateID, $arFields);
		if (!$res)
			return False;

		if ($affiliateSum > 0)
		{
			$cnt = min(count($arAffiliateParents), count($GLOBALS["SALE_AFFILIATE_TIER_TMP_CACHE"][$arAffiliate["SITE_ID"]]));
			for ($i = 0; $i < $cnt; $i++)
			{
				$affiliateSumTmp = roundEx($affiliateSum * $GLOBALS["SALE_AFFILIATE_TIER_TMP_CACHE"][$arAffiliate["SITE_ID"]][$i] / 100, SALE_VALUE_PRECISION);

				$arFields = array(
					"=PENDING_SUM" => "PENDING_SUM + ".$affiliateSumTmp
				);
				CSaleAffiliate::Update($arAffiliateParents[$i], $arFields);
			}
		}

		$events = GetModuleEvents("sale", "OnAfterAffiliateCalculate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($affiliateID));

		return True;
	}

	public static function PayAffiliate($affiliate, $payType, &$paySum)
	{
		global $DB;

		$arAffiliate = CSaleAffiliate::CheckAffiliateFunc($affiliate);
		if (!$arAffiliate)
			return False;

		$db_events = GetModuleEvents("sale", "OnBeforePayAffiliate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array(&$arAffiliate, &$payType))===false)
				return false;

		$arPayTypes = array("U", "P");
		if ($payType == '' || !in_array($payType, $arPayTypes))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("ACGA1_BAD_FUNC1"), "ERROR_FUNCTION_CALL");
			return False;
		}

		$arAffiliate["PENDING_SUM"] = str_replace(",", ".", $arAffiliate["PENDING_SUM"]);
		$arAffiliate["PENDING_SUM"] = DoubleVal($arAffiliate["PENDING_SUM"]);
		$paySum = $arAffiliate["PENDING_SUM"];

		if ($arAffiliate["PENDING_SUM"] > 0)
		{
			if (!array_key_exists("BASE_LANG_CURRENCIES", $GLOBALS))
				$GLOBALS["BASE_LANG_CURRENCIES"] = array();

			if (!array_key_exists($arAffiliate["SITE_ID"], $GLOBALS["BASE_LANG_CURRENCIES"]))
				$GLOBALS["BASE_LANG_CURRENCIES"][$arAffiliate["SITE_ID"]] = CSaleLang::GetLangCurrency($arAffiliate["SITE_ID"]);

			if ($payType == "U")
			{
				if (!CSaleUserAccount::UpdateAccount($arAffiliate["USER_ID"], $arAffiliate["PENDING_SUM"], $GLOBALS["BASE_LANG_CURRENCIES"][$arAffiliate["SITE_ID"]], "AFFILIATE"))
				{
					if ($ex = $GLOBALS["APPLICATION"]->GetException())
						$GLOBALS["APPLICATION"]->ThrowException($ex->GetString(), "ACCT_UPDATE_ERROR");
					else
						$GLOBALS["APPLICATION"]->ThrowException(GetMessage("ACGA1_ERROR_TRANSF_MONEY"), "ACCT_UPDATE_ERROR");

					return False;
				}
				//$arFields = array("PENDING_SUM" => 0);
			}
			//else
			//{
			//	$arFields = array("=PAID_SUM" => "PAID_SUM + PENDING_SUM", "PENDING_SUM" => 0);
			//}
			$arFields = array("=PAID_SUM" => "PAID_SUM + PENDING_SUM", "PENDING_SUM" => 0);

			if (!CSaleAffiliate::Update($arAffiliate["ID"], $arFields))
			{
				if ($ex = $GLOBALS["APPLICATION"]->GetException())
					$GLOBALS["APPLICATION"]->ThrowException($ex->GetString().(($payType == "U") ? GetMessage("ACGA1_TRANSF_MONEY") : ""), "AF_UPDATE_ERROR");
				else
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("ACGA1_ERROR_UPDATE_SUM").(($payType == "U") ? GetMessage("ACGA1_TRANSF_MONEY") : ""), "AF_UPDATE_ERROR");

				return False;
			}

			$arFields = array(
				"AFFILIATE_ID" => $arAffiliate["ID"],
				"TRANSACT_DATE" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID))),
				"AMOUNT" => $arAffiliate["PENDING_SUM"],
				"CURRENCY" => $GLOBALS["BASE_LANG_CURRENCIES"][$arAffiliate["SITE_ID"]],
				"DEBIT" => "Y",
				"DESCRIPTION" => "AFFILIATE_IN",
				"EMPLOYEE_ID" => ($GLOBALS["USER"]->IsAuthorized() ? $GLOBALS["USER"]->GetID() : False)
			);
			CSaleAffiliateTransact::Add($arFields);

			if ($payType == "U")
			{
				$arFields = array(
					"AFFILIATE_ID" => $arAffiliate["ID"],
					"TRANSACT_DATE" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID))),
					"AMOUNT" => $arAffiliate["PENDING_SUM"],
					"CURRENCY" => $GLOBALS["BASE_LANG_CURRENCIES"][$arAffiliate["SITE_ID"]],
					"DEBIT" => "N",
					"DESCRIPTION" => "AFFILIATE_ACCT",
					"EMPLOYEE_ID" => ($GLOBALS["USER"]->IsAuthorized() ? $GLOBALS["USER"]->GetID() : False)
				);
				CSaleAffiliateTransact::Add($arFields);
			}
		}

		$ID = $arAffiliate["ID"];
		$events = GetModuleEvents("sale", "OnAfterPayAffiliate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID));

		return True;
	}

	public static function ClearAffiliateSum($affiliate)
	{
		global $DB;

		$arAffiliate = CSaleAffiliate::CheckAffiliateFunc($affiliate);
		if (!$arAffiliate)
			return False;

		$arAffiliate["PAID_SUM"] = str_replace(",", ".", $arAffiliate["PAID_SUM"]);
		$arAffiliate["PAID_SUM"] = DoubleVal($arAffiliate["PAID_SUM"]);

		if ($arAffiliate["PAID_SUM"] > 0)
		{
			if (!array_key_exists("BASE_LANG_CURRENCIES", $GLOBALS))
				$GLOBALS["BASE_LANG_CURRENCIES"] = array();

			if (!array_key_exists($arAffiliate["SITE_ID"], $GLOBALS["BASE_LANG_CURRENCIES"]))
				$GLOBALS["BASE_LANG_CURRENCIES"][$arAffiliate["SITE_ID"]] = CSaleLang::GetLangCurrency($arAffiliate["SITE_ID"]);

			if (!CSaleAffiliate::Update($arAffiliate["ID"], array("PAID_SUM" => 0)))
			{
				if ($ex = $GLOBALS["APPLICATION"]->GetException())
					$GLOBALS["APPLICATION"]->ThrowException($ex->GetString(), "AF_UPDATE_ERROR");
				else
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("ACGA1_ERROR_UPDATE_SUM"), "AF_UPDATE_ERROR");

				return False;
			}

			$arFields = array(
				"AFFILIATE_ID" => $arAffiliate["ID"],
				"TRANSACT_DATE" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID))),
				"AMOUNT" => $arAffiliate["PAID_SUM"],
				"CURRENCY" => $GLOBALS["BASE_LANG_CURRENCIES"][$arAffiliate["SITE_ID"]],
				"DEBIT" => "N",
				"DESCRIPTION" => "AFFILIATE_CLEAR",
				"EMPLOYEE_ID" => ($GLOBALS["USER"]->IsAuthorized() ? $GLOBALS["USER"]->GetID() : False)
			);
			CSaleAffiliateTransact::Add($arFields);
		}

		return True;
	}

	public static function OnBeforeUserDelete($UserID)
	{
		global $DB;
		if (intval($UserID) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty user ID", "EMPTY_USER_ID");
			return false;
		}

		$dbAffiliate = CSaleAffiliate::GetList(array(), array("USER_ID" => $UserID), false, array("nTopCount" => 1), array("ID", "USER_ID"));
		if ($arAffiliate = $dbAffiliate->Fetch())
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#USER_ID#", $UserID, GetMessage("AF_ERROR_USER")), "ERROR_AFFILIATE");
			return False;
		}
		return true;
	}
}
