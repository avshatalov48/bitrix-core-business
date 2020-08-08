<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

global $USER;
$intUserID = $USER->getId();

$arAvailableExports = array(
//		"excel" => "excel.php",
		"csv" => "csv.php",
		"commerceml" => "commerceml.php",
		"commerceml2" => "commerceml2.php",
	);

$strPath2Export = BX_PERSONAL_ROOT."/php_interface/include/sale_export/";
$strPath2Export1 = "/bitrix/modules/sale/export/";

CheckDirPath($_SERVER["DOCUMENT_ROOT"].$strPath2Export);
if ($handle = opendir($_SERVER["DOCUMENT_ROOT"].$strPath2Export))
{
	while (($file = readdir($handle)) !== false)
	{
		if ($file == "." || $file == "..")
			continue;
		if (is_file($_SERVER["DOCUMENT_ROOT"].$strPath2Export.$file) && mb_substr($file, mb_strlen($file) - 4) == ".php")
		{
			$export_name = mb_substr($file, 0, mb_strlen($file) - 4);
			$arAvailableExports[$export_name] = $file;
		}
	}
}
closedir($handle);


$errorMessage = "";

if (CModule::IncludeModule("sale"))
{
	$EXPORT_FORMAT = Trim($EXPORT_FORMAT);
	if ($EXPORT_FORMAT <> '')
	{
		if (array_key_exists($EXPORT_FORMAT, $arAvailableExports))
		{
			$exportFilePath = "";
			if (file_exists($_SERVER["DOCUMENT_ROOT"].$strPath2Export.$arAvailableExports[$EXPORT_FORMAT])
				&& is_file($_SERVER["DOCUMENT_ROOT"].$strPath2Export.$arAvailableExports[$EXPORT_FORMAT]))
				$exportFilePath = $_SERVER["DOCUMENT_ROOT"].$strPath2Export.$arAvailableExports[$EXPORT_FORMAT];
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"].$strPath2Export1.$arAvailableExports[$EXPORT_FORMAT])
				&& is_file($_SERVER["DOCUMENT_ROOT"].$strPath2Export1.$arAvailableExports[$EXPORT_FORMAT]))
				$exportFilePath = $_SERVER["DOCUMENT_ROOT"].$strPath2Export1.$arAvailableExports[$EXPORT_FORMAT];

			if ($exportFilePath <> '')
			{
				@set_time_limit(50000);

				$runtimeFields = array();

				$arAccessibleSites = array();
				$dbAccessibleSites = CSaleGroupAccessToSite::GetList(
						array(),
						array("GROUP_ID" => $GLOBALS["USER"]->GetUserGroupArray()),
						false,
						false,
						array("SITE_ID")
					);
				while ($arAccessibleSite = $dbAccessibleSites->Fetch())
				{
					if (!in_array($arAccessibleSite["SITE_ID"], $arAccessibleSites))
						$arAccessibleSites[] = $arAccessibleSite["SITE_ID"];
				}

				$filter_lang = Trim($filter_lang);
				if ($filter_lang <> '')
				{
					if (!in_array($filter_lang, $arAccessibleSites) && $saleModulePermissions < "W")
						$filter_lang = "";
				}

				$arFilter = array();

				$arOrderProps = array();
				$arOrderPropsCode = array();
				$dbProps = \Bitrix\Sale\Internals\OrderPropsTable::getList(array(
																			   'order' => array("PERSON_TYPE_ID" => "ASC", "SORT" => "ASC"),
																			   'select' => array("ID", "NAME", "PERSON_TYPE_NAME" => "PERSON_TYPE.NAME", "PERSON_TYPE_ID", "SORT", "IS_FILTERED", "TYPE", "CODE", "SETTINGS"),
																		   ));
				while ($arProps = $dbProps->fetch())
				{
					$key = "";

					if($arProps["CODE"] <> '')
					{
						$key = $arProps["CODE"];

						if(empty($arOrderPropsCode[$key]))
							$arOrderPropsCode[$key] = $arProps;
					}
					else
					{
						$key = $arProps["ID"];
						$arOrderProps[intval($key)] = $arProps;
					}

					if($key)
					{
						if($arProps["IS_FILTERED"] == "Y" && $arProps["TYPE"] != "MULTISELECT" && $arProps["TYPE"] != "FILE")
							$arFilterFields[] = "filter_prop_".$key;
					}
				}

				if (isset($OID) && is_array($OID) && count($OID) > 0)
					$arFilter["ID"] = $OID;
				elseif (isset($OID) && intval($OID) > 0)
					$arFilter["ID"] = intval($OID);


				if(intval($filter_id_from)>0) $arFilter[">=ID"] = intval($filter_id_from);
				if(intval($filter_id_to)>0) $arFilter["<=ID"] = intval($filter_id_to);
				if($filter_date_from <> '') $arFilter[">=DATE_INSERT"] = trim($filter_date_from);
				if($filter_date_to <> '')
				{
					if($arDate = ParseDateTime($filter_date_to, CSite::GetDateFormat("FULL", SITE_ID)))
					{
						if(mb_strlen($filter_date_to) < 11)
						{
							$arDate["HH"] = 23;
							$arDate["MI"] = 59;
							$arDate["SS"] = 59;
						}

						$filter_date_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
						$arFilter["<=DATE_INSERT"] = $filter_date_to;
					}
					else
					{
						$filter_date_to = "";
					}
				}

				if($filter_date_update_from <> '')
				{
					$arFilter[">=DATE_UPDATE"] = trim($filter_date_update_from);
				}
				elseif($set_filter!="Y" && $del_filter != "Y")
				{
					$filter_date_update_from_DAYS_TO_BACK = \Bitrix\Main\Config\Option::get("sale", "order_list_date", 30);
					$filter_date_update_from = GetTime(time()-86400 * \Bitrix\Main\Config\Option::get("sale", "order_list_date", 30));
					$arFilter[">=DATE_UPDATE"] = $filter_date_update_from;
				}

				if($filter_date_update_to <> '')
				{
					if($arDate = ParseDateTime($filter_date_update_to, CSite::GetDateFormat("FULL", SITE_ID)))
					{
						if(mb_strlen($filter_date_update_to) < 11)
						{
							$arDate["HH"] = 23;
							$arDate["MI"] = 59;
							$arDate["SS"] = 59;
						}

						$filter_date_update_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
						$arFilter["<=DATE_UPDATE"] = $filter_date_update_to;
					}
					else
					{
						$filter_date_update_to = "";
					}
				}

				if($filter_date_paid_from <> '') $arFilter[">=DATE_PAYED"] = trim($filter_date_paid_from);

				if($filter_date_paid_to <> '')
				{
					if($arDate = ParseDateTime($filter_date_paid_to, CSite::GetDateFormat("FULL", SITE_ID)))
					{
						if(mb_strlen($filter_date_paid_to) < 11)
						{
							$arDate["HH"] = 23;
							$arDate["MI"] = 59;
							$arDate["SS"] = 59;
						}

						$filter_date_paid_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
						$arFilter["<=DATE_PAYED"] = $filter_date_paid_to;
					}
					else
					{
						$filter_date_paid_to = "";
					}
				}

				if($filter_date_allow_delivery_from <> '') $arFilter[">=DATE_ALLOW_DELIVERY"] = trim($filter_date_allow_delivery_from);

				if($filter_date_allow_delivery_to <> '')
				{
					if($arDate = ParseDateTime($filter_date_allow_delivery_to, CSite::GetDateFormat("FULL", SITE_ID)))
					{
						if(mb_strlen($filter_date_allow_delivery_to) < 11)
						{
							$arDate["HH"] = 23;
							$arDate["MI"] = 59;
							$arDate["SS"] = 59;
						}

						$filter_date_allow_delivery_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
						$arFilter["<=DATE_ALLOW_DELIVERY"] = $filter_date_allow_delivery_to;
					}
					else
					{
						$filter_date_allow_delivery_to = "";
					}
				}

				if($filter_lang <> '' && $filter_lang!="NOT_REF") $arFilter["=LID"] = trim($filter_lang);
				if($filter_currency <> '') $arFilter["CURRENCY"] = trim($filter_currency);

				if(isset($filter_status) && !is_array($filter_status) && $filter_status <> '')
					$filter_status = array($filter_status);
				if(isset($filter_status) && is_array($filter_status) && count($filter_status) > 0)
				{
					$countFilter = count($filter_status);
					for ($i = 0; $i < $countFilter; $i++)
					{
						$filter_status[$i] = trim($filter_status[$i]);
						if($filter_status[$i] <> '')
							$arFilter["=STATUS_ID"][] = $filter_status[$i];
					}
				}
				if ($filter_by_recommendation <> '') $arFilter["=BY_RECOMMENDATION"] = trim($filter_by_recommendation);
				if($filter_date_status_from <> '') $arFilter[">=DATE_STATUS"] = trim($filter_date_status_from);
				if($filter_date_status_to <> '')
				{
					if($arDate = ParseDateTime($filter_date_status_to, CSite::GetDateFormat("FULL", SITE_ID)))
					{
						if(mb_strlen($filter_date_status_to) < 11)
						{
							$arDate["HH"] = 23;
							$arDate["MI"] = 59;
							$arDate["SS"] = 59;
						}

						$filter_date_status_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
						$arFilter["<=DATE_STATUS"] = $filter_date_status_to;
					}
					else
					{
						$filter_date_status_to = "";
					}
				}

				if($filter_payed <> '') $arFilter["=PAYED"] = trim($filter_payed);
				if($filter_canceled <> '') $arFilter["=CANCELED"] = trim($filter_canceled);
				if($filter_deducted <> '') $arFilter["=DEDUCTED"] = trim($filter_deducted);
				if($filter_allow_delivery <> '') $arFilter["=ALLOW_DELIVERY"] = trim($filter_allow_delivery);
				if($filter_marked <> '') $arFilter["=MARKED"] = trim($filter_marked);
				if($filter_buyer <> '') $arFilter["%BUYER"] = trim($filter_buyer);
				if($filter_user_login <> '') $arFilter["USER.LOGIN"] = trim($filter_user_login);
				if($filter_user_email <> '') $arFilter["USER.EMAIL"] = trim($filter_user_email);
				if(intval($filter_user_id)>0) $arFilter["=USER_ID"] = intval($filter_user_id);
				if(is_array($filter_group_id) && count($filter_group_id) > 0)
				{
					foreach($filter_group_id as $v)
					{
						if(intval($v) > 0)
							$arFilter["USER_GROUP.GROUP_ID"][] = $v;
					}
				}

				if(intval($filter_affiliate_id)>0) $arFilter["AFFILIATE_ID"] = intval($filter_affiliate_id);
				if($filter_discount_coupon <> '') $arFilter["=ORDER_COUPONS.COUPON"] = trim($filter_discount_coupon);
				if(floatval($filter_price_from)>0) $arFilter[">=PRICE"] = floatval($filter_price_from);
				if(floatval($filter_price_to)>0) $arFilter["<PRICE"] = floatval($filter_price_to);
				if($filter_xml_id <> '') $arFilter["%XML_ID"] = trim($filter_xml_id);
				if($filter_tracking_number <> '') $arFilter["%SHIPMENT.TRACKING_NUMBER"] = trim($filter_tracking_number);

				if(isset($filter_universal) && $filter_universal <> '')
					$arFilter["NAME_SEARCH"] = trim($filter_universal);
				if($filter_account_number <> '') $arFilter["ACCOUNT_NUMBER"] = trim($filter_account_number);

				if($filter_sum_paid <> '')
				{
					if($filter_sum_paid == "Y")
						$arFilter[">SUM_PAID"] = 0;
					else
						$arFilter["<=SUM_PAID"] = 0;
				}

				if(isset($filter_person_type) && is_array($filter_person_type) && count($filter_person_type) > 0)
				{
					$countFilterPerson = count($filter_person_type);
					for ($i = 0; $i < $countFilterPerson; $i++)
					{
						if(intval($filter_person_type[$i]) > 0)
							$arFilter["=PERSON_TYPE_ID"][] = $filter_person_type[$i];
					}
				}

				if(isset($filter_source) && $filter_source != 0)
				{
					if($filter_source == -1)
						$arFilter["=SOURCE.TRADING_PLATFORM_ID"] = "";
					else
						$arFilter["=SOURCE.TRADING_PLATFORM_ID"] = $filter_source;
				}

				if(!empty($filter_pay_system) && is_array($filter_pay_system))
				{
					$countFilterPay = count($filter_pay_system);
					$whereExpression = "";

					for ($i = 0; $i < $countFilterPay; $i++)
					{
						if(intval($filter_pay_system[$i]) <= 0)
							continue;

						if($whereExpression == "")
							$whereExpression .= "(";
						else
							$whereExpression .= " OR ";

						$whereExpression .= "PAY_SYSTEM_ID = ".intval($filter_pay_system[$i]);
					}

					if($whereExpression <> '')
					{
						$whereExpression .= ")";

						$runtimeFields["REQUIRED_PS_PRESENTED"] = array(
							'data_type' => 'boolean',
							'expression' => array(
								'CASE WHEN EXISTS (SELECT ID FROM b_sale_order_payment WHERE ORDER_ID = %s AND '.$whereExpression.') THEN 1 ELSE 0 END',
								'ID'
							)
						);

						$arFilter["=REQUIRED_PS_PRESENTED"] = 1;
					}
				}

				if(!empty($filter_tracking_number) && $filter_tracking_number <> '')
				{
					$runtimeFields["REQUIRED_PS_PRESENTED"] = array(
						'data_type' => 'boolean',
						'expression' => array(
							'CASE WHEN EXISTS (SELECT ID FROM b_sale_order_payment WHERE ORDER_ID = %s AND '.$whereExpression.') THEN 1 ELSE 0 END',
							'ID'
						)
					);
				}


				if(!empty($filter_delivery_service) && is_array($filter_delivery_service))
				{
					$countFilterDeliveryService = count($filter_delivery_service);
					$whereExpression = "";

					for ($i = 0; $i < $countFilterDeliveryService; $i++)
					{
						if(intval($filter_delivery_service[$i]) <= 0)
							continue;

						if($whereExpression == "")
							$whereExpression .= "(";
						else
							$whereExpression .= " OR ";

						$whereExpression .= "DELIVERY_ID = ".intval($filter_delivery_service[$i]);
					}

					if(strval($whereExpression) != "")
					{
						$whereExpression .= ")";

						$runtimeFields["REQUIRED_DLV_PRESENTED"] = array(
							'data_type' => 'boolean',
							'expression' => array(
								'CASE WHEN EXISTS (SELECT ID FROM b_sale_order_delivery WHERE ORDER_ID = %s AND SYSTEM="N" AND '.$whereExpression.') THEN 1 ELSE 0 END',
								'ID'
							)
						);

						$arFilter["=REQUIRED_DLV_PRESENTED"] = 1;
					}
				}

				if(!empty($filter_product_id) || !empty($filter_product_xml_id))
				{

					$whereExpression = "";
					if (intval($filter_product_id) > 0)
					{
						$whereExpression .= "(PRODUCT_ID = ".intval($filter_product_id);
					}

					if (strval(trim($filter_product_xml_id)) != "")
					{
						if($whereExpression == "")
							$whereExpression .= "(";
						else
							$whereExpression .= " AND ";

						/** @var \Bitrix\Main\DB\Connection $connection */
						$connection = \Bitrix\Main\Application::getConnection();
						/** @var \Bitrix\Main\DB\SqlHelper $sqlHelper */
						$sqlHelper = $connection->getSqlHelper();

						$whereExpression .= "PRODUCT_XML_ID = '".$sqlHelper->forSql($filter_product_xml_id)."'";
					}

					if(strval($whereExpression) != "")
					{
						$whereExpression .= ")";

						$runtimeFields["REQUIRED_PRODUCT_PRESENTED"] = array(
							'data_type' => 'boolean',
							'expression' => array(
								'CASE WHEN EXISTS (SELECT ID FROM b_sale_basket WHERE ORDER_ID = %s AND '.$whereExpression.') THEN 1 ELSE 0 END',
								'ID'
							)
						);

						$arFilter["=REQUIRED_PRODUCT_PRESENTED"] = 1;
					}
				}


				$filterOrderPropValue = array();
				$filterOrderProps = array();
				foreach ($arOrderProps as $key => $value)
				{
					if($value["IS_FILTERED"] == "Y" && $value["TYPE"] != "MULTIPLE")
					{
						$tmp = trim(${"filter_prop_".$key});
						if($tmp <> '')
						{
							if($value["TYPE"]=="STRING" && !preg_match("/^\d+$/", $tmp))
								$filterName = "%PROPERTY_VALUE_".$key;
							else
								$filterName = "PROPERTY_VALUE_".$key;

							$filterOrderProps[$filterName] = $tmp;
							$filterOrderPropValue[$key] = $tmp;
						}
					}
				}

				foreach ($arOrderPropsCode as $key => $value)
				{
					if($value["IS_FILTERED"] == "Y" && $value["TYPE"] != "MULTIPLE")
					{
						$tmp = trim(${"filter_prop_".$key});
						if($tmp <> '')
						{
							if($value["TYPE"]=="STRING" && !preg_match("/^\d+$/", $tmp))
								$filterName = "%PROPERTY_VAL_BY_CODE_".$key;
							else
								$filterName = "PROPERTY_VAL_BY_CODE_".$key;

							$filterOrderProps[$filterName] = $tmp;
							$filterOrderPropValue[$key] = $tmp;
						}
					}
				}

				if($saleModulePermissions < "W")
				{
					if($filter_lang == '' && count($arAccessibleSites) > 0)
						$arFilter["=LID"] = $arAccessibleSites;
				}

				$allowedStatusesView = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($intUserID, array('view'));

				if($saleModulePermissions < "W")
				{
					if(!$arFilter["=STATUS_ID"])
						$arFilter["=STATUS_ID"] = array();

					$intersected = array_intersect($arFilter["=STATUS_ID"], $allowedStatusesView);

					if(!empty($arFilter["=STATUS_ID"]))
					{
						if(empty($intersected))
						{
							$arFilter[]["=STATUS_ID"] = $arFilter["=STATUS_ID"];
							$arFilter[]["=STATUS_ID"] = $allowedStatusesView;
							unset($arFilter["=STATUS_ID"], $arFilter["=STATUS_ID"]);
						}
						else
						{
							$arFilter["=STATUS_ID"] = $intersected;
						}
					}
					else
					{
						$arFilter["=STATUS_ID"] = $allowedStatusesView;
					}

				}

				if(isset($arFilter["NAME_SEARCH"]) && $arFilter["NAME_SEARCH"] <> '')
				{
					$nameSearch = $arFilter["NAME_SEARCH"];
					unset($arFilter["NAME_SEARCH"]);

					$arFilter[] = array(
						"LOGIC" => "OR",
						"%USER.LOGIN" => $nameSearch,
						"%USER.NAME" => $nameSearch,
						"%USER.LAST_NAME" => $nameSearch,
						"%USER.SECOND_NAME" => $nameSearch,
						"%USER.EMAIL" => $nameSearch,
					);
				}

				$propIterator = 0;

				foreach ($arOrderPropsCode as $key => $value)
				{
					if($value["IS_FILTERED"] != "Y" || $value["TYPE"] == "MULTIPLE")
						continue;

					if(
						(isset($filterOrderProps["PROPERTY_VAL_BY_CODE_".$key]) && $filterOrderProps["PROPERTY_VAL_BY_CODE_".$key] <> '')
						|| (isset($filterOrderProps["%PROPERTY_VAL_BY_CODE_".$key]) && $filterOrderProps["%PROPERTY_VAL_BY_CODE_".$key] <> '')
					)
					{
						$propIterator++;

						$runtimeFields['PROP_'.$propIterator] = array(
							'data_type' => 'Bitrix\Sale\Internals\OrderPropsValueTable',
							'reference' => array(
								'ref.ORDER_ID' => 'this.ID',
							),
							'join_type' => 'inner'
						);

						$arFilter["=PROP_".$propIterator.".CODE"] = $key;

						if (isset($filterOrderProps["%PROPERTY_VAL_BY_CODE_".$key]))
						{
							$arFilter["%PROP_".$propIterator.".VALUE"] = $filterOrderPropValue[$key];
						}
						else
						{
							$arFilter["PROP_".$propIterator.".VALUE"] = $filterOrderPropValue[$key];
						}
					}
				}

				foreach ($arOrderProps as $key => $value)
				{
					$propIterator++;

					if($value["IS_FILTERED"] != "Y" || $value["TYPE"] == "MULTIPLE")
						continue;

					if(
						(isset($filterOrderProps["PROPERTY_VALUE_".$key]) && $filterOrderProps["PROPERTY_VALUE_".$key] <> '')
						|| (isset($filterOrderProps["%PROPERTY_VALUE_".$key]) && $filterOrderProps["%PROPERTY_VALUE_".$key] <> '')
					)
					{
						$runtimeFields['PROP_'.$propIterator] = array(
							'data_type' => 'Bitrix\Sale\Internals\OrderPropsValueTable',
							'reference' => array(
								'ref.ORDER_ID' => 'this.ID',
							),
							'join_type' => 'inner'
						);

						$arFilter["=PROP_".$propIterator.".ORDER_PROPS_ID"] = $key;

						if (isset($filterOrderProps["%PROPERTY_VALUE_".$key]))
						{
							$arFilter["%PROP_".$propIterator.".VALUE"] = $filterOrderPropValue[$key];
						}
						else
						{
							$arFilter["PROP_".$propIterator.".VALUE"] = $filterOrderPropValue[$key];
						}
					}
				}


				$shownFieldsList = COption::GetOptionString("sale", "order_list_fields", "ID,USER,PAY_SYSTEM,PRICE,STATUS,PAYED,PS_STATUS,CANCELED,BASKET");
				$arShownFieldsList = explode(",", $shownFieldsList);

				$arShownFieldsParams = array();
				$aliasFields = array();

				$arSelectFields = array("PAYED");
				$ind = -1;

				$aliasFieldsList = \Bitrix\Sale\Compatible\OrderCompatibility::getAliasFields();

				$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var \Bitrix\Sale\Order $orderClass */
				$orderClass = $registry->getOrderClassName();

				$alreadyUsedFields = $orderClass::getAllFields();

				foreach ($aliasFieldsList as $fieldName => $fieldAlias)
				{
					if (!in_array($fieldName, $alreadyUsedFields))
					{
						$aliasFields[$fieldName] = $fieldAlias;
					}
				}

				foreach ($GLOBALS["AVAILABLE_ORDER_FIELDS"] as $key => $value)
				{
					if (in_array($key, $arShownFieldsList))
					{
						$ind++;
						$arShownFieldsParams[$ind] = $value;
						$arShownFieldsParams[$ind]["KEY"] = $key;

						$arFields_tmp = array();
						if ($value["SELECT"] <> '')
							$arFields_tmp = explode(",", $value["SELECT"]);

						$arShownFieldsParams[$ind]["SHOW"] = $arFields_tmp;

						for ($i = 0, $countFields = count($arFields_tmp); $i < $countFields; $i++)
						{
							$fieldName = $arFields_tmp[$i];
							$findAlias = false;

							if (array_key_exists($fieldName, $aliasFields))
							{
								$findAlias = true;
							}

							if ($findAlias && !array_key_exists($fieldName, $arSelectFields))
							{
								$arSelectFields[$fieldName] = $aliasFields[$fieldName];
							}
							elseif (!$findAlias && !in_array($fieldName, $arSelectFields))
							{
								$arSelectFields[] = $fieldName;
							}
						}
					}
				}

				include($exportFilePath);
			}
			else
			{
				$errorMessage .= str_replace("#FILE#", $exportFilePath, GetMessage("SOE_NO_SCRIPT")).". ";
			}
		}
		else
		{
			$errorMessage .= str_replace("#EXPORT_FORMAT#", $EXPORT_FORMAT, GetMessage("SOE_WRONG_FORMAT")).". ";
		}
	}
	else
	{
		$errorMessage .= GetMessage("SOE_NO_FORMAT").". ";
	}
}
else
{
	$errorMessage .= GetMessage("SOE_NO_SALE").". ";
}

if ($errorMessage <> '')
{
	$APPLICATION->SetTitle(GetMessage("SOE_EXPORT_ERROR"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	CAdminMessage::ShowMessage($errorMessage);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_before.php");
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
?>