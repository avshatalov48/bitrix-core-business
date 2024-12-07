<?php

$module_id = 'sale';
/** @global CMain $APPLICATION */
/** @global CUser $USER */

use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SiteTable;
use Bitrix\Sale;
use Bitrix\Sale\Cashbox;
use Bitrix\Sale\Registry;
use Bitrix\Sale\SalesZone;

$SALE_RIGHT = $APPLICATION->GetGroupRight('sale');
if ($SALE_RIGHT < 'R')
{
	return;
}
if (
	!Loader::includeModule('sale')
	|| !Loader::includeModule('currency')
)
{
	return;
}

$allowEditPhp = $USER->CanDoOperation('edit_php');
$defaultValues = Option::getDefaults('sale');
$showMeasurePathOption = Option::get('sale', 'measurement_path') !== $defaultValues['measurement_path'];

$request = Main\Context::getCurrent()->getRequest();

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/options.php');
IncludeModuleLangFile(__FILE__);

Main\Page\Asset::getInstance()->addJs('/bitrix/js/sale/options.js');
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/sale.css");

$lpEnabled = CSaleLocation::isLocationProEnabled();
$lMigrated = CSaleLocation::isLocationProMigrated();

$siteIdList = [];
$siteList = [];
$siteIterator = SiteTable::getList([
	'select' => [
		'LID',
		'NAME',
		'SORT',
	],
	'order' => [
		'SORT' => 'ASC',
	],
]);
while ($oneSite = $siteIterator->fetch())
{
	$siteList[] = [
		'ID' => $oneSite['LID'],
		'SAFE_ID' => htmlspecialcharsbx($oneSite['LID']),
		'NAME' => $oneSite['NAME'],
		'SAFE_NAME' => htmlspecialcharsbx($oneSite['NAME']),
	];
	$siteIdList[] = $oneSite['LID'];
}
unset($oneSite, $siteIterator);
$siteCount = count($siteList);

$bWasUpdated = false;

$currentAction = null;
if ($request->getPost('Update') === 'Y')
{
	if ($request->getPost('Save') !== null)
	{
		$currentAction = 'save';
	}
	elseif ($request->getPost('Apply') !== null)
	{
		$currentAction = 'apply';
	}
	elseif ($request->getPost('RestoreDefaults') !== null)
	{
		$currentAction = 'reset';
	}
}

$backUrl = (string)$request->get('back_url_settings');

if (
	$request->isPost()
	&& $currentAction === 'reset'
	&& $SALE_RIGHT === 'W'
	&& check_bitrix_sessid()
)
{
	$bWasUpdated = true;

	$valueList = [];
	$savedOptions = [
		'sale_locationpro_migrated',
		'sale_locationpro_enabled',
	];

	foreach ($savedOptions as $optionId)
	{
		$valueList[$optionId] = Option::get('sale', $optionId, '-');
	}
	Option::delete('sale');
	foreach ($valueList as $optionId => $value)
	{
		if ($value !== '-')
		{
			Option::set('sale', $optionId, $value);
		}
	}
	unset($savedOptions, $valueList);

	$z = CGroup::GetList(
		'id',
		'asc',
		[
			'ACTIVE' => 'Y',
			'ADMIN' => 'N',
		]
	);
	while ($zr = $z->Fetch())
	{
		$APPLICATION->DelGroupRight('sale', [$zr["ID"]]);
	}
	unset($z);
}

// region basis options
$optionMainList = [];
$optionMainList[] = [
	'ID' => 'order_email',
	'TITLE' => Loc::getMessage('SALE_EMAIL_ORDER'),
	'DEFAULT_VALUE' => 'order@' . $_SERVER['SERVER_NAME'],
	'TYPE' => 'text',
	'SETTINGS' => [
		'LENGTH' => 30,
	],
];
$optionMainList[] = [
	'ID' => 'delete_after',
	'TITLE' => Loc::getMessage('SALE_DELETE_AFTER'),
	'TYPE' => 'text',
	'SETTINGS' => [
		'LENGTH' => 10,
	],
];
$optionMainList[] = [
	'ID' => 'order_list_date',
	'TITLE' => Loc::getMessage('SALE_ORDER_LIST_DATE'),
	'TYPE' => 'text',
	'SETTINGS' => [
		'LENGTH' => 10,
	],
];
$optionMainList[] = [
	'ID' => 'MAX_LOCK_TIME',
	'TITLE' => Loc::getMessage('SALE_MAX_LOCK_TIME'),
	'TYPE' => 'text',
	'SETTINGS' => [
		'LENGTH' => 10,
	],
];
$optionMainList[] = [
	'ID' => 'GRAPH_WEIGHT',
	'TITLE' => Loc::getMessage('SALE_GRAPH_WEIGHT'),
	'TYPE' => 'text',
	'SETTINGS' => [
		'LENGTH' => 10,
	],
];
$optionMainList[] = [
	'ID' => 'GRAPH_HEIGHT',
	'TITLE' => Loc::getMessage('SALE_GRAPH_HEIGHT'),
	'TYPE' => 'text',
	'SETTINGS' => [
		'LENGTH' => 10,
	],
];
$optionMainList[] = [
	'ID' => 'path2user_ps_files',
	'TITLE' => Loc::getMessage('SALE_PATH2UPSF'),
	'TYPE' => 'path',
	'SETTINGS' => [
		'LENGTH' => 40,
	],
	'HINT' => Loc::getMessage('SALE_HINT_NEED_ADMIN_RIGHTS_FOR_CHANGE'),
	'VALIDATE' => [
		'TYPE' => 'dir',
	],
];
$optionMainList[] = [
	'ID' => 'lock_catalog',
	'TITLE' => Loc::getMessage('SMO_LOCK_CATALOG'),
	'TYPE' => 'checkbox',
];
if (CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
{
	$optionMainList[] = [
		'ID' => 'affiliate_param_name',
		'TITLE' => Loc::getMessage('SMOS_AFFILIATE_PARAM'),
		'TYPE' => 'text',
		'SETTINGS' => [
			'LENGTH' => 40,
		],
	];
	$optionMainList[] = [
		'ID' => 'affiliate_life_time',
		'TITLE' => Loc::getMessage('SMO_AFFILIATE_LIFE_TIME'),
		'TYPE' => 'text',
		'SETTINGS' => [
			'LENGTH' => 10,
		],
	];
}
$optionMainList[] = [
	'ID' => 'show_order_sum',
	'TITLE' => Loc::getMessage('SMO_SHOW_ORDER_SUM'),
	'TYPE' => 'checkbox',
];
$optionMainList[] = [
	'ID' => 'show_order_product_xml_id',
	'TITLE' => Loc::getMessage('SMO_SHOW_ORDER_PRODUCT_XML_ID'),
	'TYPE' => 'checkbox',
];
$optionMainList[] = [
	'ID' => 'show_paysystem_action_id',
	'TITLE' => Loc::getMessage('SMO_SHOW_PAYSYSTEM_ACTION_ID'),
	'TYPE' => 'checkbox',
];
if ($showMeasurePathOption)
{
	$optionMainList[] = [
		'ID' => 'measurement_path',
		'TITLE' => Loc::getMessage('SMO_MEASUREMENT_PATH'),
		'TYPE' => 'path',
		'SETTINGS' => [
			'LENGTH' => '40',
		],
		'HINT' => Loc::getMessage('SALE_HINT_NEED_ADMIN_RIGHTS_FOR_CHANGE'),
		'VALIDATE' => [
			'TYPE' => 'file',
		],
	];
}
$optionMainList[] = [
	'ID' => 'delivery_handles_custom_path',
	'TITLE' => Loc::getMessage('SMO_DELIVERY_HANDLERS_CUSTOM_PATH'),
	'TYPE' => 'path',
	'SETTINGS' => [
		'LENGTH' => 40,
	],
	'HINT' => Loc::getMessage('SALE_HINT_NEED_ADMIN_RIGHTS_FOR_CHANGE'),
	'VALIDATE' => [
		'TYPE' => 'dir',
	],
];
$optionMainList[] = [
	'ID' => 'use_secure_cookies',
	'TITLE' => Loc::getMessage('SMO_USE_SECURE_COOKIES'),
	'TYPE' => 'checkbox',
];
$optionMainList[] = [
	'ID' => 'encode_fuser_id',
	'TITLE' => Loc::getMessage('SMO_ENCODE_FUSER_ID'),
	'TYPE' => 'checkbox',
];
$optionMainList[] = [
	'ID' => 'save_anonymous_fuser_cookie',
	'TITLE' => Loc::getMessage('SALE_SAVE_ANONYMOUS_FUSER_COOKIE'),
	'TYPE' => 'checkbox',
	'HINT' => Loc::getMessage('SALE_HINT_SAVE_ANONYMOUS_FUSER_COOKIE'),
];
$optionMainList[] = [
	'ID' => 'COUNT_DISCOUNT_4_ALL_QUANTITY',
	'TITLE' => Loc::getMessage('SALE_OPT_COUNT_DISCOUNT_4_ALL_QUANTITY'),
	'TYPE' => 'checkbox',
];
$optionMainList[] = [
	'ID' => 'COUNT_DELIVERY_TAX',
	'TITLE' => Loc::getMessage('SALE_OPT_COUNT_DELIVERY_TAX'),
	'TYPE' => 'checkbox',
];
if (Option::get('sale', 'QUANTITY_FACTORIAL') === 'Y')
{
	$optionMainList[] = [
		'ID' => 'QUANTITY_FACTORIAL',
		'TITLE' => Loc::getMessage('SALE_OPT_QUANTITY_FACTORIAL'),
		'TYPE' => 'checkbox',
	];
}
$optionMainList[] = [
	'ID' => 'product_viewed_save',
	'TITLE' => Loc::getMessage('SALE_PRODUCT_VIEWED_SAVE'),
	'TYPE' => 'checkbox',
];
$optionMainList[] = [
	'ID' => 'viewed_capability',
	'TITLE' => Loc::getMessage('SALE_VIEWED_CAPABILITY'),
	'TYPE' => 'checkbox',
];
$optionMainList[] = [
	'ID' => 'viewed_time',
	'TITLE' => Loc::getMessage('SALE_VIEWED_TIME'),
	'TYPE' => 'text',
	'SETTINGS' => [
		'LENGTH' => 10,
	],
];
$optionMainList[] = [
	'ID' => 'viewed_count',
	'TITLE' => Loc::getMessage('SALE_VIEWED_COUNT'),
	'TYPE' => 'text',
	'SETTINGS' => [
		'LENGTH' => 10,
	],
];
$optionMainList[] = [
	'ID' => 'SALE_ADMIN_NEW_PRODUCT',
	'TITLE' => Loc::getMessage('SALE_ADMIN_NEW_PRODUCT'),
	'TYPE' => 'checkbox',
];
$optionMainList[] = [
	'ID' => 'use_ccards',
	'TITLE' => Loc::getMessage('SALE_ADMIN_USE_CARDS'),
	'TYPE' => 'checkbox',
];
$optionMainList[] = [
	'ID' => 'show_basket_props_in_order_list',
	'TITLE' => Loc::getMessage('SALE_SHOW_BASKET_PROPS_IN_ORDER_LIST'),
	'TYPE' => 'checkbox',
];
// endregion basis options

$arOrderFlags = [
	'P' => Loc::getMessage('SMO_PAYMENT_FLAG'),
	'C' => Loc::getMessage('SMO_CANCEL_FLAG'),
	'D' => Loc::getMessage('SMO_DELIVERY_FLAG'),
];
$numeratorForOrdersId = '';
$numeratorsOrderType = Main\Numerator\Numerator::getOneByType(Registry::REGISTRY_TYPE_ORDER);
if ($numeratorsOrderType)
{
	$numeratorForOrdersId = $numeratorsOrderType['id'];
}
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "sale_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit7", "TAB" => GetMessage("SALE_TAB_WEIGHT"), "ICON" => "sale_settings", "TITLE" => GetMessage("SALE_TAB_WEIGHT_TITLE")),
	array("DIV" => "edit5", "TAB" => GetMessage("SALE_TAB_ADDRESS"), "ICON" => "sale_settings", "TITLE" => GetMessage("SALE_TAB_ADDRESS_TITLE"))
);

if (CBXFeatures::IsFeatureEnabled('SaleCCards') && Option::get('sale', "use_ccards", "N") == "Y")
	$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("SALE_TAB_2"), "ICON" => "sale_settings", "TITLE" => GetMessage("SMO_CRYPT_TITLE"));

$aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage("SALE_TAB_3"), "ICON" => "sale_settings", "TITLE" => GetMessage("SALE_TAB_3_TITLE"));
$aTabs[] = array("DIV" => "edit4", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "sale_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS"));
$aTabs[] = array("DIV" => "edit8", "TAB" => GetMessage("SALE_TAB_AUTO"), "ICON" => "sale_settings", "TITLE" => GetMessage("SALE_TAB_AUTO_TITLE"));
$aTabs[] = array("DIV" => "edit9", "TAB" => GetMessage("SALE_TAB_ARCHIVE"), "ICON" => "sale_settings", "TITLE" => GetMessage("SALE_TAB_ARCHIVE_TITLE"));
$aTabs[] = array("DIV" => "edit10", "TAB" => GetMessage("SALE_TAB_ORDER_NUMERATOR_TEMPLATE"), "ICON" => "sale_settings", "TITLE" => GetMessage("SALE_TAB_ORDER_NUMERATOR_TEMPLATE_TITLE"));
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$strWarning = "";
function addNumeratorErrorToWarningString($_numeratorResult): string
{
	$numeratorWarningsString = '';
	foreach ($_numeratorResult->getErrors() as $error)
	{
		$numeratorWarningsString = $error->getMessage() . '<br>';
	}
	return $numeratorWarningsString;
}

/**
 * @param array $list
 * @param string|int $index
 * @return string
 */
function getSaleArrayOptionValue(array $list, $index): string
{
	$result = $list[$index] ?? '';
	if (is_array($result))
	{
		$result = '';
	}

	return trim($result);
}

function getSaleStringOptionFromRequest(HttpRequest $request, string $index): string
{
	$result = $request->getPost($index) ?? '';
	if (is_array($result))
	{
		$result = '';
	}

	return trim($result);
}

function getSaleBooleanOptionFromRequest(HttpRequest $request, string $index): ?string
{
	$result = $request->getPost($index);
	if ($result === 'Y' || $result === 'N')
	{
		return $result;
	}

	return null;
}

if (
	$request->isPost()
	&& ($currentAction === 'save' || $currentAction === 'apply')
	&& $SALE_RIGHT === 'W'
	&& check_bitrix_sessid()
)
{
	$bWasUpdated = true;

	$separateSettings = getSaleBooleanOptionFromRequest($request, 'WEIGHT_dif_settings');
	if ($separateSettings !== null)
	{
		$weightUnit = $request->getPost('weight_unit');
		$weightKoef = $request->getPost('weight_koef');
		if (!empty($weightUnit) && is_array($weightUnit) && !empty($weightKoef) && is_array($weightKoef))
		{
			Option::delete('sale', ['name' => 'weight_unit']);
			Option::delete('sale', ['name' => 'weight_koef']);

			if ($separateSettings === 'Y')
			{
				$weightUnit = $request->getPost('weight_unit');
				$weightKoef = $request->getPost('weight_koef');

				foreach ($siteIdList as $siteId)
				{
					Option::set(
						'sale',
						'weight_unit',
						getSaleArrayOptionValue($weightUnit, $siteId),
						$siteId
					);
					Option::set(
						'sale',
						'weight_koef',
						(float)getSaleArrayOptionValue($weightKoef, $siteId),
						$siteId
					);
				}
			}
			else
			{
				$siteId = getSaleStringOptionFromRequest($request, 'WEIGHT_current_site');
				Option::set(
					'sale',
					'weight_unit',
					getSaleArrayOptionValue($weightUnit, $siteId),
				);
				Option::set(
					'sale',
					'weight_koef',
					(float)getSaleArrayOptionValue($weightKoef, $siteId),
				);
			}

			Option::set('sale', 'WEIGHT_different_set', $separateSettings);
		}
		unset($weightKoef, $weightUnit);
	}

	$separateSettings = getSaleBooleanOptionFromRequest($request, 'ADDRESS_dif_settings');
	if ($separateSettings !== null)
	{
		$locationZip = $request->getPost('location_zip');
		$location = $request->getPost('location');
		if (!empty($locationZip) && is_array($locationZip) && !empty($location) && is_array($location))
		{
			Option::delete('sale', ['name' => 'location_zip']);
			Option::delete('sale', ['name' => 'location']);

			if ($separateSettings === 'Y')
			{
				foreach ($siteIdList as $siteId)
				{
					Option::set(
						'sale',
						'location_zip',
						getSaleArrayOptionValue($locationZip, $siteId),
						$siteId
					);
					Option::set(
						'sale',
						'location',
						getSaleArrayOptionValue($location, $siteId),
						$siteId
					);
				}
			}
			else
			{
				$siteId = getSaleStringOptionFromRequest($request, 'ADDRESS_current_site');
				Option::set(
					'sale',
					'location_zip',
					getSaleArrayOptionValue($locationZip, $siteId)
				);
				Option::set(
					'sale',
					'location',
					getSaleArrayOptionValue($location, $siteId)
				);
			}
			Option::set('sale', 'ADDRESS_different_set', $separateSettings);
		}
		unset($location, $locationZip);
	}

		if(!$lMigrated )
		{
			COption::RemoveOption('sale', "sales_zone_countries");
			COption::RemoveOption('sale', "sales_zone_regions");
			COption::RemoveOption('sale', "sales_zone_cities");
		}

		if(!$lpEnabled)
		{
			if (!empty($_REQUEST["ADDRESS_dif_settings"]))
			{
				for ($i = 0; $i < $siteCount; $i++)
				{
					if($lMigrated)
					{
						try
						{
							SalesZone::saveSelectedTypes(array(
								'COUNTRY' => $_REQUEST["sales_zone_countries"][$siteList[$i]["ID"]],
								'REGION' => $_REQUEST["sales_zone_regions"][$siteList[$i]["ID"]],
								'CITY' => $_REQUEST["sales_zone_cities"][$siteList[$i]["ID"]]
							), $siteList[$i]["ID"]);
						}
						catch(Exception $e)
						{
						}
					}
					else
					{
						COption::SetOptionString('sale', "sales_zone_countries", implode(":", $_REQUEST["sales_zone_countries"][$siteList[$i]["ID"]]), false, $siteList[$i]["ID"]);
						COption::SetOptionString('sale', "sales_zone_regions", implode(":",$_REQUEST["sales_zone_regions"][$siteList[$i]["ID"]]), false, $siteList[$i]["ID"]);
						COption::SetOptionString('sale', "sales_zone_cities", implode(":",$_REQUEST["sales_zone_cities"][$siteList[$i]["ID"]]), false, $siteList[$i]["ID"]);
					}
				}
			}
			else
			{
				$site_id = trim($_REQUEST["ADDRESS_current_site"]);

				if($lMigrated)
				{
					try
					{
						SalesZone::saveSelectedTypes(array(
							'COUNTRY' => $_REQUEST["sales_zone_countries"][$site_id],
							'REGION' => $_REQUEST["sales_zone_regions"][$site_id],
							'CITY' => $_REQUEST["sales_zone_cities"][$site_id]
						), $site_id);
					}
					catch(Exception $e)
					{
					}
				}
				else
				{
					COption::SetOptionString('sale', "sales_zone_countries", implode(":",$_REQUEST["sales_zone_countries"][$site_id]));
					COption::SetOptionString('sale', "sales_zone_regions", implode(":",$_REQUEST["sales_zone_regions"][$site_id]));
					COption::SetOptionString('sale', "sales_zone_cities", implode(":",$_REQUEST["sales_zone_cities"][$site_id]));
				}
			}
		}

	foreach ($optionMainList as $option)
	{
		$name = $option['ID'];
		switch ($option['TYPE'])
		{
			case 'checkbox':
				$value = getSaleBooleanOptionFromRequest($request, $name);
				if ($value !== null)
				{
					Option::set('sale', $name, $value);
				}
				break;
			case 'text':
				$value = getSaleStringOptionFromRequest($request, $name);
				Option::set('sale', $name, $value);
				break;
			case 'path':
				if ($allowEditPhp)
				{
					$value = getSaleStringOptionFromRequest($request, $name);
					try
					{
						$value = Main\IO\Path::normalize($value);
					}
					catch (Main\IO\InvalidPathException $e)
					{
						$value = $defaultValues[$name] ?? '';
					}
					if ($value !== '')
					{
						if (($option['VALIDATE']['TYPE'] ?? '') === 'dir')
						{
							if (mb_substr($value, - 1, 1) !== '/')
							{
								$value .= '/';
							}
						}
						Option::set('sale', $name, $value);
					}
				}
				break;
		}
	}

	$rsAgents = CAgent::GetList(
		[
			'ID' => 'DESC',
		],
		[
			'MODULE_ID' => 'sale',
			'NAME' => "\\Bitrix\\Sale\\Basket::deleteOldAgent(%",
		]
	);
	while ($arAgent = $rsAgents->Fetch())
	{
		CAgent::Delete($arAgent['ID']);
	}
	unset($arAgent, $rsAgents);

	$delete_after = (int)Option::get('sale', 'delete_after');
	if ($delete_after > 0)
	{
		CAgent::AddAgent(
			"\\Bitrix\\Sale\\Basket::deleteOldAgent(" . $delete_after . ");",
			"sale",
			"N",
			8 * 60 * 60,
			"",
			"Y"
		);
	}

	if (CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
	{
		Option::set(
			'sale',
			'affiliate_plan_type',
			getSaleStringOptionFromRequest($request, 'affiliate_plan_type')
		);
	}

	$amountValues = $request->getPost('amount_val');
	$amountCurrencies = $request->getPost('amount_currency');
	if (!empty($amountValues) && is_array($amountValues) && !empty($amountCurrencies) && is_array($amountCurrencies))
	{
		$amountList = [];
		foreach (array_keys($amountValues) as $amountIndex)
		{
			$amount = (float)getSaleArrayOptionValue($amountValues, $amountIndex);
			$currency = getSaleArrayOptionValue($amountCurrencies, $amountIndex);
			if ($amount > 0 && $currency !== '')
			{
				$amountList[] = [
					'AMOUNT' => $amount,
					'CURRENCY' => $currency,
				];
			}
		}
		if (!empty($amountList))
		{
			Option::set('sale', 'pay_amount', serialize($amountList));
		}
		unset($amountList);
	}
	unset($amountCurrencies, $amountValues);

	CAgent::RemoveAgent('CSaleOrder::RemindPayment();', 'sale');
	Option::delete('sale', ['name' => 'pay_reminder']);
	$rawPayReminder = $request->getPost('reminder');
	if (!empty($rawPayReminder) && is_array($rawPayReminder))
	{
		$payReminder = [];
		foreach ($siteIdList as $siteId)
		{
			if (!empty($rawPayReminder[$siteId]) && is_array($rawPayReminder[$siteId]))
			{
				$payReminder[$siteId] = [
					'use' => ($rawPayReminder[$siteId]['use'] ?? 'N') === 'Y' ? 'Y' : 'N',
					'after' => (int)($rawPayReminder[$siteId]['after'] ?? 0),
					'frequency' => (int)($rawPayReminder[$siteId]['frequency'] ?? 0),
					'period' => (int)($rawPayReminder[$siteId]['period'] ?? 0),
				];
			}
		}
		if (!empty($payReminder))
		{
			Option::set('sale', 'pay_reminder', serialize($payReminder));
			CAgent::AddAgent('CSaleOrder::RemindPayment();', 'sale', 'N', 86400, '', 'Y');
		}
		unset($payReminder);
	}
	unset($rawPayReminder);

	//subscribe product
	$rsAgents = CAgent::GetList(
		[
			'ID' => 'DESC',
		],
		[
			'MODULE_ID' => 'sale',
			'NAME' => 'CSaleBasket::ClearProductSubscribe(%',
		]
	);
	while ($arAgent = $rsAgents->Fetch())
	{
		CAgent::Delete($arAgent["ID"]);
	}
	unset($arAgent, $rsAgents);
	Option::delete('sale', ['name' => 'subscribe_prod']);
	$rawProductSubscribe = $request->getPost('subscribProd');
	if (!empty($rawProductSubscribe) && is_array($rawProductSubscribe))
	{
		$productSubscribe = [];
		foreach ($siteIdList as $siteId)
		{
			$useSubscribe = ($rawProductSubscribe[$siteId]['use'] ?? 'N') === 'Y' ? 'Y' : 'N';
			$period = (int)($rawProductSubscribe[$siteId]['del_after'] ?? 0);
			if ($period <= 0)
			{
				$period = 30;
			}
			$productSubscribe[$siteId] = [
				'use' => $useSubscribe,
				'del_after' => $period,
			];
			if ($useSubscribe === 'Y')
			{
				CAgent::AddAgent(
					"CSaleBasket::ClearProductSubscribe('" . $siteId . "');",
					'sale',
					'N',
					$period * 86400,
					'',
					'Y'
				);
			}
		}
		Option::set('sale', 'subscribe_prod', serialize($productSubscribe));
		unset($productSubscribe);
	}
	unset($rawProductSubscribe);

	//viewed product
	Option::delete('sale', ['name' => 'viewed_product']);
	$rawViewed = $request->getPost('viewed');
	if (!empty($rawViewed) && is_array($rawViewed))
	{
		$viewed = [];
		foreach ($siteIdList as $siteId)
		{
			$viewedTime = (int)($rawViewed[$siteId]['time'] ?? 0);
			if ($viewedTime <= 0)
			{
				$viewedTime = 90;
			}
			$viewedCount = (int)($rawViewed[$siteId]['count'] ?? 0);
			if ($viewedCount <= 0)
			{
				$viewedCount = 1000;
			}
			$viewed[$siteId] = [
				'time' => $viewedTime,
				'count' => $viewedCount,
			];
		}
		Option::set('sale', 'viewed_product', serialize($viewed));
		unset($viewed);
	}

	Option::set(
		'sale',
		'viewed_capability',
		($request->getPost('viewed_capability') ?? 'N') === 'Y' ? 'Y' : 'N'
	);

	$rsAgents = CAgent::GetList(
		[
			'ID' => 'DESC',
		],
		[
			'MODULE_ID' => 'sale',
			'NAME' => 'CSaleViewedProduct::ClearViewed();',
		]
	);
	$arAgent = $rsAgents->Fetch();
	unset($rsAgents);
	if (!$arAgent)
	{
		CAgent::AddAgent(
			'CSaleViewedProduct::ClearViewed();',
			'sale',
			'N',
			86400,
			'',
			'Y'
		);
	}

	Option::set('sale', 'default_currency', getSaleStringOptionFromRequest($request, 'CURRENCY_DEFAULT'));
	Option::set('sale', 'crypt_algorithm', getSaleStringOptionFromRequest($request, 'crypt_algorithm'));
	Option::set('sale', 'sale_data_file', getSaleStringOptionFromRequest($request, 'sale_data_file'));

	$sale_ps_success_path = getSaleStringOptionFromRequest($request, 'sale_ps_success_path');
	if ($sale_ps_success_path === '')
	{
		$sale_ps_success_path = '/';
	}
	Option::set('sale', 'sale_ps_success_path', $sale_ps_success_path);

	$sale_ps_fail_path = getSaleStringOptionFromRequest($request, 'sale_ps_fail_path');
	if ($sale_ps_fail_path === '')
	{
		$sale_ps_fail_path = '/';
	}
	Option::set('sale', 'sale_ps_fail_path', $sale_ps_fail_path);

	$sale_location_selector_appearance = getSaleStringOptionFromRequest($request, 'sale_location_selector_appearance');
	if ($sale_location_selector_appearance === '')
	{
		$sale_location_selector_appearance = 'steps';
	}
	Option::set('sale', 'sale_location_selector_appearance', $sale_location_selector_appearance);

	$optionList = [
		'status_on_paid' => 'PAID_STATUS',
		'status_on_half_paid' => 'HALF_PAID_STATUS',
		'status_on_allow_delivery' => 'ALLOW_DELIVERY_STATUS',
		'status_on_allow_delivery_one_of' => 'ALLOW_DELIVERY_ONE_OF_STATUS',
		'status_on_shipped_shipment' => 'SHIPMENT_SHIPPED_STATUS',
		'status_on_shipped_shipment_one_of' => 'SHIPMENT_SHIPPED_ONE_OF_STATUS',
		'shipment_status_on_allow_delivery' => 'SHIPMENT_ALLOW_DELIVERY_TO_SHIPMENT_STATUS',
		'shipment_status_on_shipped' => 'SHIPMENT_SHIPPED_TO_SHIPMENT_STATUS',
		'status_on_payed_2_allow_delivery' => 'PAYED_2_ALLOW_DELIVERY',
		'status_on_change_allow_delivery_after_paid' => 'CHANGE_ALLOW_DELIVERY_AFTER_PAID',
	];
	foreach ($optionList as $optionName => $requestKey)
	{
		Option::set('sale', $optionName, getSaleStringOptionFromRequest($request, $requestKey));
	}
	unset($optionName, $requestKey, $optionList);

	$alloDeductionOnDelivery = getSaleBooleanOptionFromRequest($request, 'ALLOW_DEDUCTION_ON_DELIVERY');
	if ($alloDeductionOnDelivery !== null)
	{
		Option::set('sale', 'allow_deduction_on_delivery', $alloDeductionOnDelivery);
	}
	unset($alloDeductionOnDelivery);

	$formatQuantity = getSaleStringOptionFromRequest($request, 'FORMAT_QUANTITY');
	if ($formatQuantity !== 'AUTO')
	{
		$formatQuantity = (int)$formatQuantity;
	}
	Option::set('sale', 'format_quantity', $formatQuantity);
	unset($formatQuantity);

	$valuePrecision = (int)getSaleStringOptionFromRequest($request, 'VALUE_PRECISION');
	if ($valuePrecision < 0)
	{
		$valuePrecision = 2;
	}
	Option::set('sale', 'value_precision', $valuePrecision);

	$oldExpirationProcessingEvents = Option::get('sale', 'expiration_processing_events');
	$newExpirationProcessingEvents = getSaleBooleanOptionFromRequest($request, 'EXPIRATION_PROCESSING_EVENTS');
	if ($newExpirationProcessingEvents !== null)
	{
		Option::set('sale', 'expiration_processing_events', $newExpirationProcessingEvents);
		if ($oldExpirationProcessingEvents !== $newExpirationProcessingEvents)
		{
			$eventManager = Main\EventManager::getInstance();
			if ($newExpirationProcessingEvents === 'Y')
			{
				Sale\Compatible\EventCompatibility::registerEvents();

				$eventManager->registerEventHandlerCompatible(
					'sale',
					'OnBeforeBasketAdd',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onBeforeBasketAdd'
				);
				$eventManager->registerEventHandlerCompatible(
					'sale',
					'OnBasketAdd',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onBasketAdd'
				);
				$eventManager->registerEventHandlerCompatible(
					'sale',
					'OnOrderAdd',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onOrderAdd'
				);
				$eventManager->registerEventHandlerCompatible(
					'sale',
					'OnSalePayOrder',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onSalePayOrder'
				);

				$eventManager->unRegisterEventHandler(
					'sale',
					'OnSaleBasketItemSaved',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onSaleBasketItemSaved'
				);
				$eventManager->unRegisterEventHandler(
					'sale',
					'OnSaleOrderSaved',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onSaleOrderSaved'
				);
				$eventManager->unRegisterEventHandler(
					'sale',
					'OnSaleOrderPaid',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onSaleOrderPaid'
				);
			}
			else
			{
				Sale\Compatible\EventCompatibility::unRegisterEvents();

				$eventManager->unRegisterEventHandler(
					'sale',
					'OnBeforeBasketAdd',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onBeforeBasketAdd'
				);
				$eventManager->unRegisterEventHandler(
					'sale',
					'OnBasketAdd',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onBasketAdd'
				);
				$eventManager->unRegisterEventHandler(
					'sale',
					'OnOrderAdd',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onOrderAdd'
				);
				$eventManager->unRegisterEventHandler(
					'sale',
					'OnSalePayOrder',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onSalePayOrder'
				);

				$eventManager->registerEventHandler(
					'sale',
					'OnSaleBasketItemSaved',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onSaleBasketItemSaved'
				);
				$eventManager->registerEventHandler(
					'sale',
					'OnSaleOrderSaved',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onSaleOrderSaved'
				);
				$eventManager->registerEventHandler(
					'sale',
					'OnSaleOrderPaid',
					'sale',
					'\Bitrix\Sale\Internals\ConversionHandlers',
					'onSaleOrderPaid'
				);
			}
		}
	}

	$optionList = [
		'order_history_log_level' => 'ORDER_HISTORY_LOG_LEVEL',
		'order_history_action_log_level' => 'ORDER_HISTORY_ACTION_LOG_LEVEL',
	];
	foreach ($optionList as $optionName => $requestKey)
	{
		$value = (int)getSaleStringOptionFromRequest($request, $requestKey);
		if ($value !== 1)
		{
			$value = 0;
		}
		Option::set('sale', $optionName, $value);
	}
	unset($value, $optionName, $requestKey, $optionList);

	$orderListFields = '';
	$rawOrderListields = $request->getPost('SELECTED_FIELDS');
	if (!is_array($rawOrderListields))
	{
		$rawOrderListields = [];
	}
	$rawOrderListields = array_filter($rawOrderListields);
	if (!empty($rawOrderListields))
	{
		$orderListFields = implode(',', $rawOrderListields);
	}
	if ($orderListFields === '')
	{
		$orderListFields = 'ID,USER,PAY_SYSTEM,PRICE,STATUS,PAYED,PS_STATUS,CANCELED,BASKET';
	}
	Option::set('sale', 'order_list_fields', $orderListFields);

	// account number generation - via numerator
	$hideNumeratorSettings = getSaleBooleanOptionFromRequest($request, 'hideNumeratorSettings');
	if ($hideNumeratorSettings !== null)
	{
		if ($hideNumeratorSettings === 'Y')
		{
			if ($numeratorForOrdersId)
			{
				Main\Numerator\Numerator::delete($numeratorForOrdersId);
			}
		}
		else
		{
			$postValues = $request->getPostList()->toArray();
			if ($numeratorForOrdersId)
			{
				$numeratorUpdateResult = Main\Numerator\Numerator::update($numeratorForOrdersId, $postValues);
				if (!$numeratorUpdateResult->isSuccess())
				{
					$strWarning .= addNumeratorErrorToWarningString($numeratorUpdateResult);
				}
			}
			else
			{
				$numeratorOrder = Main\Numerator\Numerator::create();
				$numeratorOrderValidationResult = $numeratorOrder->setConfig($postValues);
				if ($numeratorOrderValidationResult->isSuccess())
				{
					$numeratorOrderSaveResult = $numeratorOrder->save();
					if (!$numeratorOrderSaveResult->isSuccess())
					{
						$strWarning .= addNumeratorErrorToWarningString($numeratorOrderSaveResult);
					}
				}
				else
				{
					$strWarning .= addNumeratorErrorToWarningString($numeratorOrderValidationResult);
				}
			}
			unset($postValues);
		}
	}

	//subscribe product
	$rawDefaultDeductStore = $request->getPost('defaultDeductStore');
	if (!empty($rawDefaultDeductStore) && is_array($rawDefaultDeductStore))
	{
		Option::delete('sale', ['name' => 'deduct_store_id']);
		foreach ($siteIdList as $siteId)
		{
			if (($rawDefaultDeductStore[$siteId]['save'] ?? 'N') === 'Y')
			{
				$defaultStoreId = (int)($rawDefaultDeductStore[$siteId]['id'] ?? 0);
				if ($defaultStoreId > 0)
				{
					Option::set('sale', 'deduct_store_id', $defaultStoreId, $siteId);
				}
			}
		}
	}
	unset($rawDefaultDeductStore);

	//SAVE SHOP LIST SITE
	foreach ($siteIdList as $siteId)
	{
		Option::delete('sale', ['name' => 'SHOP_SITE_' . $siteId]);
	}
	$rawShopSites = $request->getPost('SHOP_SITE');
	if (!empty($rawShopSites) && is_array($rawShopSites))
	{
		foreach ($rawShopSites as $siteId)
		{
			if (!is_string($siteId))
			{
				continue;
			}
			if (!in_array($siteId, $siteIdList))
			{
				continue;
			}
			Option::set('sale', 'SHOP_SITE_' . $siteId, $siteId);
		}
	}
	unset($rawShopSites);

	$SALE_P2P_ALLOW_COLLECT_DATA = getSaleBooleanOptionFromRequest($request, 'SALE_P2P_ALLOW_COLLECT_DATA');
	if ($SALE_P2P_ALLOW_COLLECT_DATA !== null)
	{
		$p2p_del_exp_old = (int)Option::get('sale', 'p2p_del_exp');
		$agentData = CAgent::GetList(
			[
				'ID' => 'DESC',
			],
			[
				'MODULE_ID' => 'sale',
				'NAME' => "\\Bitrix\\Sale\\Product2ProductTable::addProductsByAgent(%",
			]
		);
		$agent = $agentData->Fetch();
		unset($agentData);

		if ($SALE_P2P_ALLOW_COLLECT_DATA === 'Y')
		{
			if (!$agent)
			{
				$limit = (int)Option::get('sale', 'p2p_limit_collecting_per_hit');
				CAgent::AddAgent(
					"Bitrix\\Sale\\Product2ProductTable::addProductsByAgent($limit);",
					'sale',
					'N',
					60,
					'',
					'Y'
				);
			}
		}
		else
		{
			$agentId = (int)($agent['ID'] ?? 0);
			if ($agentId > 0)
			{
				CAgent::Delete($agentId);
			}
			unset($agentId);
		}
		unset($agent);

		Option::set('sale', 'p2p_allow_collect_data', $SALE_P2P_ALLOW_COLLECT_DATA);
	}
	$SALE_P2P_STATUS_LIST = $request->getPost('SALE_P2P_STATUS_LIST');
	if (is_array($SALE_P2P_STATUS_LIST))
	{
		$SALE_P2P_STATUS_LIST = array_filter($SALE_P2P_STATUS_LIST);
		Option::set('sale', 'p2p_status_list', serialize($SALE_P2P_STATUS_LIST));
	}
	unset($SALE_P2P_STATUS_LIST);

	$p2p_del_period = (int)getSaleStringOptionFromRequest($request, 'p2p_del_period');
	if ($p2p_del_period <= 0)
	{
		$p2p_del_period = 10;
	}
	Option::set('sale', 'p2p_del_period', $p2p_del_period);

	$p2p_del_exp = (int)getSaleStringOptionFromRequest($request, 'p2p_del_exp');
	if ($p2p_del_exp <= 0)
	{
		$p2p_del_exp = 10;
	}
	Option::set('sale', 'p2p_del_exp', $p2p_del_exp);
	$rsAgents = CAgent::GetList(
		['ID'=>'DESC'],
		[
			'MODULE_ID' => 'sale',
			'NAME' => "\\Bitrix\\Sale\\Product2ProductTable::deleteOldProducts(%",
		]
	);
	while($arAgent = $rsAgents->Fetch())
	{
		CAgent::Delete($arAgent["ID"]);
	}
	unset($arAgent, $rsAgents);
	CAgent::AddAgent(
		"Bitrix\\Sale\\Product2ProductTable::deleteOldProducts(" . $p2p_del_exp . ");",
		'sale',
		'N',
		86400 * $p2p_del_period,
		'',
		'Y'
	);
	unset($p2p_del_exp, $p2p_del_period);

	$siteCurrencies = [];
	$iterator = Sale\Internals\SiteCurrencyTable::getList([
		'select' => [
			'LID',
		],
	]);
	while ($row = $iterator->fetch())
	{
		$siteCurrencies[$row['LID']] = $row['LID'];
	}
	unset($row, $iterator);
	foreach ($siteIdList as $siteId)
	{
		$valCurrency = getSaleStringOptionFromRequest($request, 'CURRENCY_' . $siteId);
		if (isset($siteCurrencies[$siteId]))
		{
			if ($valCurrency === '')
			{
				Sale\Internals\SiteCurrencyTable::delete($siteId);
			}
			else
			{
				Sale\Internals\SiteCurrencyTable::update(
					$siteId,
					[
						'CURRENCY' => $valCurrency,
					]
				);
			}
			unset($siteCurrencies[$siteId]);
		}
		else
		{
			Sale\Internals\SiteCurrencyTable::add([
				'LID' => $siteId,
				'CURRENCY' => $valCurrency,
			]);
		}

		CSaleGroupAccessToSite::DeleteBySite($siteId);
		$userGroupList = $request->getPost('SITE_USER_GROUPS_' . $siteId);
		if (!empty($userGroupList) && is_array($userGroupList))
		{
			Main\Type\Collection::normalizeArrayValuesByInt($userGroupList);
			foreach ($userGroupList as $groupId)
			{
				CSaleGroupAccessToSite::Add([
					'SITE_ID' => $siteId,
					'GROUP_ID' => $groupId,
				]);
			}
		}
		unset($userGroupList);
	}
/*	if (!empty($siteCurrencies))
	{
		foreach ($siteCurrencies as $siteId)
		{
			Sale\Internals\SiteCurrencyTable::delete($siteId);
		}
	} */
	unset($siteCurrencies);

	$productReserveCondition = getSaleStringOptionFromRequest($request, 'product_reserve_condition');
	if (in_array($productReserveCondition, Sale\Configuration::getReservationConditionList(false)))
	{
		Option::set('sale', 'product_reserve_condition', $productReserveCondition);
	}
	unset($productReserveCondition);

	$clearPeriod = (int)getSaleStringOptionFromRequest($request, 'product_reserve_clear_period');
	if ($clearPeriod >= 0)
	{
		Option::set('sale', 'product_reserve_clear_period', $clearPeriod);
	}
	unset($clearPeriod);

	$useSaleDiscountOnly = getSaleBooleanOptionFromRequest($request, 'use_sale_discount_only');
	if ($useSaleDiscountOnly !== null)
	{
		Option::set('sale', 'use_sale_discount_only', $useSaleDiscountOnly);
	}
	unset($useSaleDiscountOnly);

	$discountPercent = getSaleBooleanOptionFromRequest($request, 'get_discount_percent_from_base_price');
	if ($discountPercent !== null)
	{
		Option::set('sale', 'get_discount_percent_from_base_price', $discountPercent);
	}
	unset($discountPercent);

	if (Option::get('sale', 'use_sale_discount_only') === 'N')
	{
		$discountModeApply = (int)getSaleStringOptionFromRequest($request, 'discount_apply_mode');
		if (in_array($discountModeApply, Sale\Discount::getApplyModeList(false)))
		{
			Option::set('sale', 'discount_apply_mode', $discountModeApply);
		}
		unset($discountModeApply);
	}

	$optionList = [
		'regular_archive_active' => 'archive_regular_accept',
		'archive_blocked_order' => 'archive_blocked_order_accept',
	];
	foreach ($optionList as $optionName => $requestKey)
	{
		$value = getSaleBooleanOptionFromRequest($request, $requestKey);
		if ($value !== null)
		{
			Option::set('sale', $optionName, $value);
		}
	}
	unset($value, $optionName, $requestKey, $optionList);

	$enableRegularArchive = Option::get('sale', 'regular_archive_active') === 'Y';
	$archiveBlockedOrder = Option::get('sale', 'archive_blocked_order') === 'Y';

	$filter = [];
	$archivePeriod = (int)getSaleStringOptionFromRequest($request, 'archive_period');
	$filter['PERIOD'] = ($archivePeriod > 0 ? $archivePeriod : 365);
	unset($archivePeriod);

	if (!$archiveBlockedOrder)
	{
		$filter['LOCKED_BY'] = null;
		$filter['DATE_LOCK'] = null;
	}

	$rawStatusList = $request->getPost('archive_status_id');
	if (!empty($rawStatusList) && is_array($rawStatusList))
	{
		$allStatusNames = Sale\OrderStatus::getAllStatusesNames();
		$statusList = [];
		foreach ($rawStatusList as $statusId)
		{
			if (isset($allStatusNames[$statusId]))
			{
				$statusList[] = $statusId;
			}
		}
		if (!empty($statusList))
		{
			$filter['@STATUS_ID'] = $statusList;
		}
		unset($statusList, $allStatusNames);
	}
	unset($rawStatusList);

	$rawArchiveSiteList = $request->getPost('archive_site');
	if (!empty($rawArchiveSiteList) && is_array($rawArchiveSiteList))
	{
		$archiveSiteList = [];
		foreach ($rawArchiveSiteList as $siteId)
		{
			if (isset($siteList[$siteId]))
			{
				$archiveSiteList[] = $siteId;
			}
		}
		if (!empty($archiveSiteList))
		{
			$filter['@LID'] = $archiveSiteList;
		}
		unset($archiveSiteList);
	}
	unset($rawArchiveSiteList);

	$archiveFlagOptionList = [
		'=PAYED' => 'archive_payed',
		'=CANCELED' => 'archive_canceled',
		'=DEDUCTED' => 'archive_deducted',
	];
	foreach ($archiveFlagOptionList as $filterKey => $requestKey)
	{
		$value = getSaleBooleanOptionFromRequest($request, $requestKey);
		if ($value !== null)
		{
			$filter[$filterKey] = $value;
		}
	}
	unset($archiveFlagOptionList);

	$archiveLimit = (int)getSaleStringOptionFromRequest($request, 'archive_limit');
	if ($archiveLimit <= 0)
	{
		$archiveLimit = 10;
	}
	Option::set('sale', 'archive_limit', $archiveLimit);

	$archiveTimeLimit = (int)getSaleStringOptionFromRequest($request, 'archive_time_limit');
	if ($archiveTimeLimit <= 0)
	{
		$archiveTimeLimit = 5;
	}
	Option::set('sale', 'archive_time_limit', $archiveTimeLimit);

	$filter = serialize($filter);
	Option::set('sale', 'archive_params', $filter);

	$agentsList = CAgent::GetList(
		[
			'ID' => 'DESC',
		],
		[
			'MODULE_ID' => 'sale',
			'NAME' => "\\Bitrix\\Sale\\Archive\\Manager::archiveOnAgent(%",
		]
	);
	while ($agent = $agentsList->Fetch())
	{
		CAgent::Delete($agent["ID"]);
	}
	unset($agent, $agentList);

	if ($enableRegularArchive)
	{
		CAgent::AddAgent(
			"\\Bitrix\\Sale\\Archive\\Manager::archiveOnAgent(" . $archiveLimit . "," . $archiveTimeLimit . ");",
			"sale",
			"N",
			86400,
			"",
			"Y"
		);
	}

	$orderChangesCleanerActive = getSaleBooleanOptionFromRequest($request, 'order_changes_cleaner_active');
	if ($orderChangesCleanerActive !== null)
	{
		Option::set('sale', 'order_changes_cleaner_active', $orderChangesCleanerActive);
	}
	$orderChangesCleanerActive = Option::get('sale', 'order_changes_cleaner_active') === 'Y';

	$orderChangesCleanerDays = (int)getSaleStringOptionFromRequest($request, 'order_changes_cleaner_days');
	if ($orderChangesCleanerDays <= 0)
	{
		$orderChangesCleanerDays = 365;
	}
	Option::set('sale', 'order_changes_cleaner_days', $orderChangesCleanerDays);

	$orderChangesCleanerLimit = (int)getSaleStringOptionFromRequest($request, 'order_changes_cleaner_limit');
	if ($orderChangesCleanerLimit <= 0)
	{
		$orderChangesCleanerLimit = 10000;
	}
	Option::set('sale', 'order_changes_cleaner_limit',$orderChangesCleanerLimit);

	$agentsList = CAgent::GetList(
		[
			'ID' => 'DESC',
		],
		[
			'MODULE_ID' => 'sale',
			'NAME' => "\\Bitrix\\Sale\\OrderHistory::deleteOldAgent(%",
		]
	);
	while($agent = $agentsList->Fetch())
	{
		CAgent::Delete($agent["ID"]);
	}
	unset($agent, $agentList);

	if ($orderChangesCleanerActive)
	{
		CAgent::AddAgent(
			"\\Bitrix\\Sale\\OrderHistory::deleteOldAgent(" . $orderChangesCleanerDays ."," . $orderChangesCleanerLimit . ");",
			'sale',
			'N',
			60,
			'',
			'Y'
		);
	}

	ob_start();
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';
	ob_end_clean();

	$rawTrackingMapStatuses = $request->getPost('tracking_map_statuses');
	if (!empty($rawTrackingMapStatuses) && is_array($rawTrackingMapStatuses))
	{
		$trackingStatuses = Sale\Delivery\Tracking\Manager::getStatusesList();
		$shipmentStatuses = [];
		$iterator = Sale\Internals\StatusTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=TYPE' => Sale\Internals\StatusTable::TYPE_SHIPMENT,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$shipmentStatuses[$row['ID']] = $row['ID'];
		}
		unset($row, $iterator);
		$trackingMapStatuses = [];
		foreach ($rawTrackingMapStatuses as $trackStatusId => $shipmentStatusId)
		{
			if (!is_string($shipmentStatusId) || $shipmentStatusId === '')
			{
				continue;
			}
			if (!isset($trackingStatuses[$trackStatusId]) || !isset($shipmentStatuses[$shipmentStatusId]))
			{
				continue;
			}
			$trackingMapStatuses[$trackStatusId] = $shipmentStatusId;
		}
		Option::set('sale', 'tracking_map_statuses', serialize($trackingMapStatuses));
		unset($trackingMapStatuses);
		unset($shipmentStatuses, $trackingStatuses);
	}
	unset($rawTrackingMapStatuses);

	$trackingCheck = getSaleBooleanOptionFromRequest($request, 'tracking_check_switch');
	if ($trackingCheck !== null)
	{
		Option::set('sale', 'tracking_check_switch', $trackingCheck);
	}
	$trackingCheck = Option::get('sale', 'tracking_check_switch') === 'Y';

	$trackingPeriod = (int)getSaleStringOptionFromRequest($request, 'tracking_check_period');
	if ($trackingPeriod > 0)
	{
		Option::set('sale', 'tracking_check_period', $trackingPeriod);
	}
	$trackingPeriod = (int)Option::get('sale', 'tracking_check_period');

	$agentName = '\Bitrix\Sale\Delivery\Tracking\Manager::startRefreshingStatuses();';

	if ($trackingCheck && $trackingPeriod > 0)
	{
		$res = CAgent::GetList([], ['NAME' => $agentName]);
		$agent = $res->Fetch();
		unset($res);
		if ($agent)
		{
			CAgent::Update($agent['ID'], ['AGENT_INTERVAL' => $trackingPeriod * 3600]);
		}
		else
		{
			CAgent::AddAgent(
				$agentName,
				'sale',
				'Y',
				$trackingPeriod * 3600,
				'',
				'Y'
			);
		}
	}
	else
	{
		CAgent::RemoveAgent(
			$agentName,
			'sale'
		);
	}

	$checkTypeOnPay = getSaleStringOptionFromRequest($request, 'CHECK_TYPE_ON_PAY');
	if ($checkTypeOnPay === '')
	{
		$checkTypeOnPay = 'sell';
	}
	Option::set('sale', 'check_type_on_pay', $checkTypeOnPay);
	unset($checkTypeOnPay);

	$basketRefreshGap = (int)getSaleStringOptionFromRequest($request, 'BASKET_REFRESH_GAP');
	if ($basketRefreshGap < 0)
	{
		$basketRefreshGap = 0;
	}
	Option::set('sale', 'basket_refresh_gap', $basketRefreshGap);
	unset($basketRefreshGap);

	$orderStatuses = [];
	$iterator = Sale\Internals\StatusTable::getList([
		'select' => [
			'ID',
		],
		'filter' => [
			'=TYPE' => Sale\Internals\StatusTable::TYPE_ORDER,
		],
	]);
	while ($row = $iterator->fetch())
	{
		$orderStatuses[$row['ID']] = $row['ID'];
	}
	unset($row, $iterator);
	$allowPayStatus = getSaleStringOptionFromRequest($request, 'ALLOW_PAY_STATUS');
	if ($allowPayStatus === '' || !isset($orderStatuses[$allowPayStatus]))
	{
		$allowPayStatus = Sale\OrderStatus::getInitialStatus();
	}
	unset($orderStatuses);
	Option::set('sale', 'allow_pay_status', $allowPayStatus);
	unset($allowPayStatus);

	$allowGuestOrderView = getSaleBooleanOptionFromRequest($request, 'ALLOW_GUEST_ORDER_VIEW');
	if ($allowGuestOrderView !== null)
	{
		Option::set('sale', 'allow_guest_order_view', $allowGuestOrderView);
	}
	unset($allowGuestOrderView);

	$allowGuestOrderViewPath = $request->getPost('ALLOW_GUEST_ORDER_VIEW_PATH');
	if (!is_array($allowGuestOrderViewPath))
	{
		$allowGuestOrderViewPath = [];
	}
	//TODO: check values
	Option::set('sale', 'allow_guest_order_view_paths', serialize($allowGuestOrderViewPath));
	unset($allowGuestOrderViewPath);

	$allowGuestOrderViewStatus = $request->getPost('ALLOW_GUEST_ORDER_VIEW_STATUS');
	if (!is_array($allowGuestOrderViewStatus))
	{
		$allowGuestOrderViewStatus = [];
	}
	//TODO: check values
	Option::set('sale', 'allow_guest_order_view_status', serialize($allowGuestOrderViewStatus));
	unset($allowGuestOrderViewStatus);
}

$statusesWithoutNoChange = [];
$arStatuses = [
	'' => Loc::getMessage('SMO_STATUS'),
];
$iterator = Sale\Internals\StatusTable::getList([
	'select' => [
		'ID',
		'SORT',
		'NAME' => 'STATUS_LANG.NAME',
	],
	'filter' => [
		'=STATUS_LANG.LID' => LANGUAGE_ID,
		'=TYPE' => Sale\Internals\StatusTable::TYPE_ORDER,
	],
	'order' => [
		'SORT' => 'ASC',
		'ID' => 'ASC',
	],
]);
while ($arStatus = $iterator->fetch())
{
	$title = htmlspecialcharsbx('['  .$arStatus['ID'].'] '. $arStatus['NAME']);
	$arStatuses[$arStatus['ID']] = $title;
	$statusesWithoutNoChange[$arStatus['ID']] = $title;
}
unset($iterator);

$delieryStatuses = [
	'' => Loc::getMessage('SMO_STATUS'),
];
$delieryStatusesList = Sale\DeliveryStatus::getAllStatusesNames();
if (!empty($delieryStatusesList) && is_array($delieryStatusesList))
{
	foreach ($delieryStatusesList as $statusId => $statusName)
	{
		$delieryStatuses[$statusId] = htmlspecialcharsbx('[' . $statusId .'] ' . $statusName);
	}
}


if ($strWarning !== '')
{
	CAdminMessage::ShowMessage($strWarning);
}
elseif ($bWasUpdated)
{
	if ($currentAction === 'save' && $backUrl !== '')
	{
		LocalRedirect($backUrl);
	}
	else
	{
		LocalRedirect(
			$APPLICATION->GetCurPage()
			. '?lang=' . LANGUAGE_ID
			. '&mid=' . urlencode('sale')
			. '&mid_menu=1'
			. ($backUrl !== '' ? '&back_url_settings=' . urlencode($backUrl) : '')
			. '&' . $tabControl->ActiveTabParam()
		);
	}
}

$settings = [];
$settings['use_sale_discount_only'] = Option::get('sale', 'use_sale_discount_only');
$settings['get_discount_percent_from_base_price'] = Option::get('sale', 'get_discount_percent_from_base_price');
$settings['discount_apply_mode'] = (int)Option::get('sale', 'discount_apply_mode');
$settings['product_reserve_condition'] = Option::get('sale', 'product_reserve_condition');
$settings['product_reserve_clear_period'] = (int)Option::get('sale', 'product_reserve_clear_period');
$settings['tracking_map_statuses'] = [];
$option = Option::get('sale', 'tracking_map_statuses');
if ($option !== '')
{
	$settings['tracking_map_statuses'] = unserialize($option, ['allowed_classes' => false]);
}
if (!is_array($settings['tracking_map_statuses']))
{
	$settings['tracking_map_statuses'] = [];
}
$settings['tracking_check_switch'] = Option::get('sale', 'tracking_check_switch');
$settings['tracking_check_period'] = (int)Option::get('sale', 'tracking_check_period');

$tabControl->Begin();
?><form method="POST" action="<?= $APPLICATION->GetCurPage()?>?lang=<?= LANGUAGE_ID; ?>&mid=<?= $module_id; ?>&mid_menu=1" name="opt_form">
<?=bitrix_sessid_post();
$tabControl->BeginNextTab();
?>
<tr class="heading">
	<td colspan="2"><?= Loc::getMessage('SALE_SERVICE_AREA'); ?></td>
</tr>
<?php
foreach ($optionMainList as $option):
	$value = Option::get('sale', $option['ID'], $option['DEFAULT_VALUE'] ?? '');

	$optionName = htmlspecialcharsbx($option['ID']);

	?>
	<tr>
		<td style="width: 40%;">
			<?php
			if (isset($option['HINT']))
			{
				?><span id="hint_<?= $optionName; ?>"></span>
				<script>BX.hint_replace(BX('hint_<?= $optionName; ?>'), '<?= CUtil::JSEscape($option['HINT']); ?>');</script>&nbsp;<?php
			}
			if ($option['TYPE'] === 'checkbox')
			{
				echo '<label for="' . $optionName . '">' . $option['TITLE'] . '</label>';
			}
			else
			{
				echo $option['TITLE'];
			}
			?>
		</td>
		<td>
			<?php
			switch ($option['TYPE'])
			{
				case 'checkbox':
					?>
					<input type="hidden" name="<?= $optionName; ?>" id="<?= $optionName; ?>_hidden" value="N">
					<input type="checkbox" name="<?= $optionName; ?>" id="<?= $optionName; ?>" value="Y"<?= ($value === 'Y' ? ' checked' : ''); ?>>
					<?php
					break;
				case 'text':
					?>
					<input type="text" size="<?= (int)$option['SETTINGS']['LENGTH']; ?>" value="<?= htmlspecialcharsbx($value); ?>" name="<?= $optionName; ?>">
					<?php
					break;
				case 'path':
					$disabled = $allowEditPhp ? '' : ' disabled';
					?>
					<input type="text"<?= $disabled; ?> size="<?= (int)$option['SETTINGS']['LENGTH']; ?>" value="<?= htmlspecialcharsbx($value); ?>" name="<?= $optionName; ?>">
					<?php

					break;
			}
			?>
		</td>
	</tr>
	<?php
endforeach;

	$valDeductOnDelivery = Option::get('sale', 'allow_deduction_on_delivery');
	?>
	<tr>
		<td>
			<?= Loc::getMessage('SMO_FORMAT_QUANTITY_TITLE'); ?>:
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'format_quantity');
			$selectList = [
				'AUTO',
				'2',
				'3',
				'4',
			];
			?>
			<select name="FORMAT_QUANTITY">
			<?php
			foreach ($selectList as $option):
				?>
				<option value="<?= $option; ?>"<?= ($val === $option ? ' selected' : ''); ?>><?= Loc::getMessage('SMO_FORMAT_QUANTITY_' . $option); ?></option>
				<?php
			endforeach;
			unset($option, $selectList);
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SMO_VALUE_PRECISION_TITLE'); ?>:
		</td>
		<td>
			<?php
			$val = (int)Option::get('sale', 'value_precision');
			?>
			<select name="VALUE_PRECISION">
			<?php
			for ($i = 0; $i <= 4; $i++):
				?>
				<option value="<?= $i; ?>>"<?= ($i === $val ? ' selected' : ''); ?>><?= Loc::getMessage('SMO_VALUE_PRECISION_' . $i); ?></option>
				<?php
			endfor;
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_DEF_CURR'); ?>
		</td>
		<td>
			<?php
			echo CCurrency::SelectBox(
				'CURRENCY_DEFAULT',
				Option::get('sale', 'default_currency'),
				'',
				true,
				''
			);
			?>
		</td>
	</tr>
	<?php
	if (CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
	{
		?>
	<tr>
		<td>
			<?= Loc::getMessage('SMO_AFFILIATE_PLAN_TYPE'); ?>:
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'affiliate_plan_type');
			?>
			<select name="affiliate_plan_type">
				<option value="N"<?= ($val === 'N' ? ' selected' : ''); ?>><?= Loc::getMessage('SMO_AFFILIATE_PLAN_TYPE_N'); ?></option>
				<option value="S"<?= ($val === 'S' ? ' selected' : ''); ?>><?= Loc::getMessage('SMO_AFFILIATE_PLAN_TYPE_S'); ?></option>
			</select>
		</td>
	</tr>
		<?php
	}
	?>
	<tr>
		<td>
			<label for="EXPIRATION_PROCESSING_EVENTS"><?= Loc::getMessage('SALE_EXPIRATION_PROCESSING_EVENTS'); ?></label>
		</td>
		<td>
			<?php
			$valExpirationProcessingEvents = Option::get('sale', 'expiration_processing_events');
			?>
			<input type="hidden" name="EXPIRATION_PROCESSING_EVENTS" id="EXPIRATION_PROCESSING_EVENTS_hidden" value="N">
			<input type="checkbox" name="EXPIRATION_PROCESSING_EVENTS" id="EXPIRATION_PROCESSING_EVENTS" value="Y"<?= ($valExpirationProcessingEvents === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>

	<tr>
		<td>
			<label for='ORDER_HISTORY_LOG_LEVEL'><?= Loc::getMessage('SALE_ORDER_HISTORY_LOG_LEVEL'); ?></label>
		</td>
		<td>
			<?php
			$valOrderHistoryLogLevel = (int)Option::get('sale', 'order_history_log_level');
			?>
			<input type="hidden" name="ORDER_HISTORY_LOG_LEVEL" id="ORDER_HISTORY_LOG_LEVEL_hidden" value="0">
			<input type="checkbox" name="ORDER_HISTORY_LOG_LEVEL" id="ORDER_HISTORY_LOG_LEVEL" value="1"<?= ($valOrderHistoryLogLevel === 1 ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr>
		<td>
			<label for="ORDER_HISTORY_ACTION_LOG_LEVEL"><?= Loc::getMessage('SALE_ORDER_HISTORY_ACTION_LOG_LEVEL'); ?></label>
		</td>
		<td>
			<?php
			$valOrderHistoryActionLogLevel = (int)Option::get('sale', 'order_history_action_log_level');
			?>
			<input type="hidden" name="ORDER_HISTORY_ACTION_LOG_LEVEL" id="ORDER_HISTORY_ACTION_LOG_LEVEL_hidden" value="0">
			<input type="checkbox" name="ORDER_HISTORY_ACTION_LOG_LEVEL" id="ORDER_HISTORY_ACTION_LOG_LEVEL" value="1"<?= ($valOrderHistoryActionLogLevel === 1 ? ' checked' : ''); ?>>
		</td>
	</tr>

	<tr>
		<td valign="top">
			<?= Loc::getMessage('SALE_IS_SHOP'); ?>
		</td>
		<td>
			<select name="SHOP_SITE[]" multiple size="5">
			<?php
			foreach ($siteList as $val)
			{
				$site = Option::get('sale', 'SHOP_SITE_' . $val['ID'], '');
				?>
				<option value="<?= $val['SAFE_ID']; ?>"<?= ($site === $val['ID'] ? ' selected' : ''); ?>><?= $val['SAFE_NAME'] .' (' . $val['SAFE_ID'] . ')'; ?></option>
				<?php
			}
			unset($site, $val);
			?>
			</select>
		</td>
	</tr>

	<!-- ps success and fail paths -->
	<tr>
		<td>
			<?= Loc::getMessage('SALE_PS_SUCCESS_PATH'); ?>
		</td>
		<td>
			<input type="text" name="sale_ps_success_path" size="40" value="<?= htmlspecialcharsbx(Option::get('sale', 'sale_ps_success_path')); ?>">
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_PS_FAIL_PATH'); ?>
		</td>
		<td>
			<input type="text" name="sale_ps_fail_path" size="40" value="<?=htmlspecialcharsbx(Option::get('sale', 'sale_ps_fail_path')); ?>">
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage("SALE_ALLOW_PAY_STATUS"); ?>:
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'allow_pay_status');
			?>
			<select name="ALLOW_PAY_STATUS">
				<?php
				foreach($statusesWithoutNoChange as $statusId => $safeName)
				{
					?><option value="<?= $statusId; ?>"<?= ($val === $statusId ? ' selected' : ''); ?>><?= $safeName; ?></option><?php
				}
				?>
			</select>
		</td>
	</tr>
	<!-- start of check default type -->
	<?php
	if (Cashbox\Manager::isSupportedFFD105()):
		?>
		<tr class="heading" id="check_default_type_block">
			<td colspan="2"><?= Loc::getMessage('SALE_BLOCK_CHECK_TITLE'); ?></td>
		</tr>
		<tr>
			<td><?= Loc::getMessage('SALE_CHECK_TYPE_ON_PAY'); ?>:</td>
			<td>
				<?php
				$val = Option::get('sale', 'check_type_on_pay');
				$selectList = [
					'sell' => 'SALE_CHECK_TYPE_ON_PAY_SELL',
					'prepayment' => 'SALE_CHECK_TYPE_ON_PAY_PREPAYMENT',
					'advance' => 'SALE_CHECK_TYPE_ON_PAY_ADVANCE',
				];
				?>
				<select name="CHECK_TYPE_ON_PAY">
				<?php
				foreach ($selectList as $option => $messageId):
					?>
					<option value="<?= $option; ?>" <?=($val === $option ? 'selected': ''); ?>><?= Loc::getMessage($messageId); ?></option>
					<?php
				endforeach;
				?>
				</select>
			</td>
		</tr>
	<?php
	endif;
	?>
	<!-- start of basket behavior in public -->
	<tr class="heading" id="basket_public_behavior_block">
		<td colspan="2"><?= Loc::getMessage('SALE_BASKET_PUBLIC_BEHAVIOR_TITLE'); ?></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('SALE_BASKET_REFRESH_GAP'); ?>:</td>
		<td>
			<?php
			$refreshGapVal = (int)Option::get('sale', 'basket_refresh_gap');
			?>
			<input type="text" size="10" value="<?= $refreshGapVal; ?>" name="BASKET_REFRESH_GAP" id="basket_refresh_gap">
		</td>
	</tr>
	<?php
	if ($settings['use_sale_discount_only'] !== 'Y'):
		?>
		<tr id="basket_refresh_gap_warning" <?= ($refreshGapVal === 0 ? 'style="display: none;"' : ''); ?>>
			<td colspan="2" align="center">
				<div class="adm-info-message-wrap">
					<div class="adm-info-message">
						<div><?= Loc::getMessage('SALE_BASKET_REFRESH_GAP_WARNING'); ?></div>
					</div>
				</div>
			</td>
		</tr>
		<script>
			BX.bind(BX('basket_refresh_gap'), 'change', function(event){
				var target = BX.getEventTarget(event);
				var warning = BX('basket_refresh_gap_warning');

				if (BX.type.isDomNode(target) && BX.type.isDomNode(warning))
				{
					warning.style.display = parseInt(target.value) === 0 ? 'none' : '';
				}
			});
		</script>
	<?php
	endif;
	?>
	<!-- start of order guest view -->
	<tr class="heading" id="guest_order_view_block">
		<td colspan="2"><a name="section_guest_order_view"></a><?= Loc::getMessage('SALE_ALLOW_GUEST_ORDER_VIEW_TITLE'); ?></td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_ALLOW_GUEST_ORDER_VIEW'); ?>:
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'allow_guest_order_view');
			$rowStyle = ($val === 'Y' ? '' : 'style="display:none;"');
			?>
			<input type="hidden" value="N" name="ALLOW_GUEST_ORDER_VIEW">
			<input type="checkbox" value="Y" name="ALLOW_GUEST_ORDER_VIEW"<?= ($val === 'Y' ? ' checked' : ''); ?> onchange="showAllowGuestOrderViewPaths(this);">
		</td>
	</tr>
	<tr class="sale_allow_guest_order_view"<?= $rowStyle; ?>>
		<td valign='top'>
			<?= Loc::getMessage('SALE_ORDER_GUEST_VIEW_STATUS'); ?>
		</td>
		<td>
			<?php
			$guestStatuses = Option::get('sale', 'allow_guest_order_view_status');
			if ($guestStatuses !== '')
			{
				$guestStatuses = unserialize($guestStatuses, ['allowed_classes' => false]);
			}
			if (!is_array($guestStatuses))
			{
				$guestStatuses = [];
			}
			$statusList = array_slice($arStatuses,1);
			?>
			<select name="ALLOW_GUEST_ORDER_VIEW_STATUS[]" multiple size="3">
				<?php
				foreach($statusList as $id => $safeName):
					?>
					<option value="<?=$id?>" <?=(in_array($id, $guestStatuses) ? "selected" : "")?>><?= $safeName; ?>
					</option>
					<?php
				endforeach;
				?>
			</select>
		</td>
	</tr>
	<?php
	$paths = [];
	$serializedPass = Option::get('sale', 'allow_guest_order_view_paths');
	if ($serializedPass !== '')
	{
		$paths = unserialize($serializedPass, ['allowed_classes' => false]);
	}
	if (!is_array($paths))
	{
		$paths = [];
	}
	foreach($siteList as $site)
	{
		?>
		<tr class="sale_allow_guest_order_view" <?= $rowStyle; ?>>
			<td>
				<?= Loc::getMessage(
					'SALE_ALLOW_GUEST_ORDER_VIEW_PATH',
					[
						'#SITE_ID#' => $site['ID']
					]
				); ?>:
			</td>
			<td>
				<input type="text" size="40" value="<?= htmlspecialcharsbx($paths[$site["ID"]] ?? ''); ?>" name="ALLOW_GUEST_ORDER_VIEW_PATH[<?= $site['SAFE_ID']; ?>]">
			</td>
		</tr>
		<?php
	}
	unset($site);
	?>
	<tr class="sale_allow_guest_order_view" <?= $rowStyle; ?>>
		<td>
			<?= Loc::getMessage('SALE_ALLOW_GUEST_ORDER_VIEW_EXAMPLE'); ?>:
		</td>
		<td>
			/personal/orders/#order_id#
		</td>
	</tr>
	<!-- end of order guest view -->
	<?php
	unset($rowStyle);

	if (!(Loader::includeModule('crm') && !CCrmSaleHelper::isWithOrdersMode())):
		?>
		<tr class="heading">
			<td colspan="2"><a name="section_reservation"></a><?= Loc::getMessage('BX_SALE_SETTINGS_SECTION_RESERVATION'); ?></td>
		</tr>
		<tr>
			<td width="40%"><?= Loc::getMessage('BX_SALE_SETTINGS_OPTION_PRODUCT_RESERVE_CONDITION'); ?></td>
			<td width="60%"><select name="product_reserve_condition">
				<?php
				foreach (Sale\Configuration::getReservationConditionList(true) as $reserveId => $reserveTitle)
				{
					?><option value="<?= $reserveId; ?>"<?php
						echo ($reserveId == $settings['product_reserve_condition'] ? ' selected' : '')
					?>><?= htmlspecialcharsbx($reserveTitle); ?></option>
					<?php
				}
				unset($reserveId, $reserveTitle);
				?>
			</select></td>
		</tr>
		<tr>
			<td width="40%"><?= Loc::getMessage('BX_SALE_SETTINGS_OPTION_PRODUCT_RESERVE_CLEAR_PERIOD'); ?></td>
			<td width="60%">
				<input type="text" name="product_reserve_clear_period" value="<?= $settings['product_reserve_clear_period']; ?>">
			</td>
		</tr>
	<?php
	endif;
	?>
	<tr class="heading">
		<td colspan="2"><?= Loc::getMessage('BX_SALE_SETTINGS_SECTION_LOCATIONS'); ?></td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_LOCATION_WIDGET_APPEARANCE'); ?>:
		</td>
		<td>
			<?php
			$widgetType = Option::get('sale', 'sale_location_selector_appearance');
			$selectList = [
				'steps' => 'SALE_LOCATION_SELECTOR_APPEARANCE_STEPS',
				'search' => 'SALE_LOCATION_SELECTOR_APPEARANCE_SEARCH',
			];
			?>
			<select name="sale_location_selector_appearance">
			<?php
			foreach ($selectList as $option => $messageId):
				?>
				<option value="<?= $option; ?>"<?= ($option === $widgetType ? ' selected' : ''); ?>><?= Loc::getMessage($messageId); ?></option>
				<?php
			endforeach;
			?>
			</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><a name="section_discount"></a><?= Loc::getMessage('BX_SALE_SETTINGS_SECTION_DISCOUNT'); ?></td>
	</tr>
	<tr>
		<td width="40%"><?= Loc::getMessage('BX_SALE_SETTINGS_OPTION_USE_SALE_DISCOUNT_ONLY'); ?></td>
		<td width="60%">
			<input type="hidden" name="use_sale_discount_only" id="use_sale_discount_only_N" value="N">
			<input type="checkbox" name="use_sale_discount_only" id="use_sale_discount_only_Y" value="Y"<?= ($settings['use_sale_discount_only'] == 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<script>
		BX.bind(BX('use_sale_discount_only_Y'), 'change', function(event){
			var target = BX.getEventTarget(event);
			var warning = BX('use_sale_discount_only_warning');

			if (BX.type.isDomNode(target) && BX.type.isDomNode(warning))
			{
				warning.style.display = target.checked ? 'none' : '';
			}
		});
	</script>
	<tr id="use_sale_discount_only_warning" <?= ($settings['use_sale_discount_only'] === 'Y' || $refreshGapVal === 0 ? 'style="display: none;"' : ''); ?>>
		<td colspan="2" align="center">
			<div class="adm-info-message-wrap">
				<div class="adm-info-message">
					<div><?= Loc::getMessage('SALE_USE_SALE_DISCOUNT_ONLY_WARNING'); ?></div>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td width="40%"><?= Loc::getMessage('BX_SALE_SETTINGS_OPTION_PERCENT_FROM_BASE_PRICE'); ?></td>
		<td width="60%">
			<input type="hidden" name="get_discount_percent_from_base_price" id="get_discount_percent_from_base_price_N" value="N">
			<input type="checkbox" name="get_discount_percent_from_base_price" id="get_discount_percent_from_base_price_Y" value="Y"<?= ($settings['get_discount_percent_from_base_price'] == 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr id="tr_discount_apply_mode" style="display: <?=($settings['use_sale_discount_only'] == 'Y' ? 'none' : 'table-row'); ?>">
		<td width="40%"><?= Loc::getMessage('BX_SALE_SETTINGS_OPTION_DISCOUNT_APPLY_MODE'); ?></td>
		<td width="60%">
			<select name="discount_apply_mode" style="max-width: 300px;">
			<?php
			$modeList = Sale\Discount::getApplyModeList(true);
			foreach ($modeList as $modeId => $modeTitle)
			{
				?><option value="<?=$modeId; ?>"<?=($modeId == $settings['discount_apply_mode'] ? ' selected' : ''); ?>><?= htmlspecialcharsbx($modeTitle); ?></option><?php
			}
			unset($modeTitle, $modeId, $modeList);
			?>
			</select>
		</td>
	</tr>

	<!-- Recommended products -->
	<tr class="heading">
		<td colspan="2"><?= Loc::getMessage('SALE_P2P'); ?></td>
	</tr>
	<tr>
		<td align="right" width="40%">
			<label for="p2p_allow_collect_data"><?= Loc::getMessage('SALE_P2P_COLLECT_DATA'); ?></label>
		</td>
		<td width="60%">
			<input type="hidden" name="SALE_P2P_ALLOW_COLLECT_DATA" value="N" id="p2p_allow_collect_data_hidden">
			<input type="checkbox" name="SALE_P2P_ALLOW_COLLECT_DATA" value="Y" id="p2p_allow_collect_data"<?= (Option::get('sale', 'p2p_allow_collect_data') === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<?= Loc::getMessage('SALE_P2P_STATUS_LIST'); ?>
		</td>
		<td>
			<?php
			$recStatuses = Option::get('sale', 'p2p_status_list');
			if ($recStatuses !== '')
			{
				$recStatuses = unserialize($recStatuses, ['allowed_classes' => false]);
			}
			if (!is_array($recStatuses))
			{
				$recStatuses = [];
			}

			$p2pStatusesList = array_slice($arStatuses, 1);
			$p2pStatusesList = array_merge(
				$p2pStatusesList,
				[
					'F_CANCELED' => htmlspecialcharsbx(Loc::getMessage('F_CANCELED')),
					'F_DELIVERY' => htmlspecialcharsbx(Loc::getMessage('F_DELIVERY')),
					'F_PAY' => htmlspecialcharsbx(Loc::getMessage('F_PAY')),
					'F_OUT' => htmlspecialcharsbx(Loc::getMessage('F_OUT')),
				]
			);
			?>
			<select name="SALE_P2P_STATUS_LIST[]" multiple size="5">
				<?php
				foreach($p2pStatusesList as $id => $safeName):
					?>
					<option value="<?=$id?>"<?= (in_array($id, $recStatuses) ? ' selected' : '')?>><?= $safeName; ?>
					</option>
					<?php
				endforeach;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_P2P_STATUS_PERIOD'); ?>
		</td>
		<td>
			<input type="text" size="5" value="<?= (int)Option::get('sale', 'p2p_del_period'); ?>" name="p2p_del_period">
		</td>
	</tr>

	<tr>
		<td>
			<?= Loc::getMessage('SALE_P2P_EXP_DATE'); ?>
		</td>
		<td>
			<input type="text" size="5" value="<?= (int)Option::get('sale', 'p2p_del_exp'); ?>" name="p2p_del_exp">
		</td>
	</tr>

	<!-- Order history cleaner -->
	<tr class="heading">
		<td colspan="2"><?= Loc::getMessage('SALE_ORDER_HISTORY_CLEANER_TITLE'); ?></td>
	</tr>
	<tr>
		<td align="right" width="40%">
			<label for="order_changes_cleaner_active"><?= Loc::getMessage('SALE_ORDER_HISTORY_CLEANER_SWITCHER'); ?></label>
		</td>
		<td width="60%">
			<input type="hidden" name="order_changes_cleaner_active" value="N" id="order_changes_cleaner_active_hidden">
			<input type="checkbox" name="order_changes_cleaner_active" value="Y" id="order_changes_cleaner_active"<?= (Option::get('sale', 'order_changes_cleaner_active') === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_ORDER_HISTORY_CLEANER_DAYS'); ?>
		</td>
		<td>
			<input type="text" size="5" value="<?= (int)Option::get('sale', 'order_changes_cleaner_days'); ?>" name="order_changes_cleaner_days">
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_ORDER_HISTORY_CLEANER_BY_HIT'); ?>
		</td>
		<td>
			<input type="text" size="5" value="<?= (int)Option::get('sale', 'order_changes_cleaner_limit'); ?>" name="order_changes_cleaner_limit">
		</td>
	</tr>
	<!-- /Recommended products -->
	<?php
	if (CBXFeatures::IsFeatureEnabled('SaleAccounts'))
	{
		?>
	<tr class="heading">
		<td colspan="2"><?= Loc::getMessage('SALE_AMOUNT_NAME'); ?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table cellspacing="0" cellpadding="0" border="0" class="internal">
				<tr class="heading">
					<td valign="top">
						<?= Loc::getMessage('SALE_AMOUNT_VAL'); ?>
					</td>
					<td valign="top">
						<?= Loc::getMessage('SALE_AMOUNT_CURRENCY'); ?>
					</td>
				</tr>
				<?php
				$lastCurrency = '';
				$val = Option::get('sale', 'pay_amount');
				if ($val !== '')
				{
					$amountList = unserialize($val, ['allowed_classes' => false]);
					if (!is_array($amountList))
					{
						$amountList = [];
					}
					foreach($amountList as $amount)
					{
						?>
						<tr>
							<td><input type="text" name="amount_val[]" value="<?= (float)($amount['AMOUNT'] ?? 0); ?>"></td>
							<td><?= CCurrency::SelectBox(
								'amount_currency[]',
								$amount['CURRENCY'] ?? '',
								'',
								true,
								''
								);
							?></td>
						</tr>
						<?php
						$lastCurrency = $amount['CURRENCY'] ?? '';
					}
					unset($amountList);
				}
				?>
				<tr>
					<td><input type="text" name="amount_val[]" value=""></td>
					<td><?= CCurrency::SelectBox(
							'amount_currency[]',
							$lastCurrency
						);
						?>
					</td>
				</tr>
				<tr>
					<td><input type="text" name="amount_val[]" value=""></td>
					<td><?= CCurrency::SelectBox(
							'amount_currency[]',
							$lastCurrency
						);
						?>
					</td>
				</tr>
				<tr>
					<td><input type="text" name="amount_val[]" value=""></td>
					<td><?= CCurrency::SelectBox(
							'amount_currency[]',
							$lastCurrency
						);
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
		<?php
	}
	?>
	<tr>
		<td colspan="2">
			<?php
			$arReminder = [];
			$reminder = Option::get('sale', 'pay_reminder');
			if ($reminder !== '')
			{
				$arReminder = unserialize($reminder, ['allowed_classes' => false]);
			}
			if (!is_array($arReminder))
			{
				$arReminder = [];
			}

			$arSubscribeProd = [];
			$subscribeProd = Option::get('sale', 'subscribe_prod');
			if ($subscribeProd !== '')
			{
				$arSubscribeProd = unserialize($subscribeProd, ['allowed_classes' => false]);
			}
			if (!is_array($arSubscribeProd))
			{
				$arSubscribeProd = [];
			}

			$aTabs2 = [];
			foreach($siteList as $val)
			{
				$aTabs2[] = [
					'DIV' => 'reminder' . $val['SAFE_ID'],
					'TAB' => '['.$val['SAFE_ID'].'] ' . $val['SAFE_NAME'],
					'TITLE' => '[' . $val['SAFE_ID'] . '] ' . $val['SAFE_NAME'],
				];
			}
			$tabControl2 = new CAdminViewTabControl('tabControl2', $aTabs2);
			$tabControl2->Begin();
			foreach($siteIdList as $siteId)
			{
				$arStores = [];
				if (Loader::includeModule('catalog'))
				{
					$dbStore = CCatalogStore::GetList(
						[
							'SORT' => 'DESC',
							'ID' => 'ASC',
						],
						[
							'ACTIVE' => 'Y',
							'SHIPPING_CENTER' => 'Y',
							'+SITE_ID' => $siteId,
						]
					);
					while ($arStore = $dbStore->GetNext())
					{
						$arStore['ID'] = (int)$arStore['ID'];
						$arStores[] = $arStore;
					}
					unset($arStore, $dbStore);
				}
				$tabControl2->BeginNextTab();
				?>
				<table cellspacing="5" cellpadding="0" border="0" width="100%" align="center">
					<!-- default store -->
					<?php
					$deductStore = (int)Option::get('sale', 'deduct_store_id', '', $siteId);

					$showRow = (count($arStores) > 1 && $valDeductOnDelivery === 'Y');
					$display = $showRow ? 'table-row' : 'none';
					?>
					<tr class="default_deduct_store_control" style="display:<?= $display; ?>" id="default_deduct_store_control_<?= $siteId; ?>">
						<td align="right" width="40%"><?= Loc::getMessage('SALE_DEDUCT_STORE'); ?></td>
						<td width="60%">
							<select name="defaultDeductStore[<?= $siteId; ?>][id]" id="default_store_select_<?= $siteId; ?>">
								<?php
								foreach ($arStores as $storeId => $arStore):
								?>
									<option value="<?=$arStore["ID"]?>"<?= ($deductStore === $arStore['ID'] ? ' selected' : '');  ?>><?= $arStore['TITLE'] . ' ['. $arStore['ID'] . ']'; ?></option>
								<?php
								endforeach;
								?>
							</select>
							<input type="hidden" id="default_store_select_save_<?= $siteId; ?>" name="defaultDeductStore[<?= $siteId; ?>][save]" value="<?= ($showRow ? 'Y' : 'N'); ?>">
						</td>
					</tr>
					<!-- end of default store -->

					<tr class="heading">
						<td colspan="2"><?= Loc::getMessage('SMO_PRODUCT_SUBSCRIBE'); ?></td>
					</tr>
					<tr>
						<td align="right" width="40%"><label for="notify-<?=$siteId?>"><?= Loc::getMessage('SALE_NOTIFY_PRODUCT_USE'); ?></label></td>
						<td width="60%"><input type="checkbox" name="subscribProd[<?=$siteId?>][use]" value="Y" id="notify-<?=$siteId?>"<?= (($arSubscribeProd[$siteId]['use'] ?? 'N') === 'Y' ? ' checked' : ''); ?>></td>
					</tr>
					<tr>
						<td align="right"><?= Loc::getMessage("SALE_NOTIFY_PRODUCT")?></td>
						<td><input type="text" name="subscribProd[<?=$siteId?>][del_after]" value="<?= (int)($arSubscribeProd[$siteId]["del_after"] ?? 0); ?>" size="5" id="del-after-<?=$siteId?>"></td>
					</tr>
					<tr class="heading">
						<td colspan="2"><?= Loc::getMessage("SMO_ORDER_PAY_REMINDER")?></td>
					</tr>
					<tr>
						<td align="right" width="40%"><label for="use-<?=$siteId?>"><?= Loc::getMessage("SMO_ORDER_PAY_REMINDER_USE")?>:</label></td>
						<td width="60%"><input type="checkbox" name="reminder[<?=$siteId?>][use]" value="Y" id="use-<?=$siteId?>"<?= (($arReminder[$siteId]["use"] ?? 'N') === "Y" ? ' checked' : ''); ?>></td>
					</tr>
					<tr>
						<td align="right"><label for="after-<?=$siteId?>"><?= Loc::getMessage("SMO_ORDER_PAY_REMINDER_AFTER")?>:</label></td>
						<td><input type="text" name="reminder[<?=$siteId?>][after]" value="<?= (int)($arReminder[$siteId]["after"] ?? 0); ?>" size="5" id="after-<?=$siteId?>"></td>
					</tr>
					<tr>
						<td align="right"><label for="frequency-<?=$siteId?>"><?= Loc::getMessage("SMO_ORDER_PAY_REMINDER_FREQUENCY")?>:</label></td>
						<td><input type="text" name="reminder[<?=$siteId?>][frequency]" value="<?= (int)($arReminder[$siteId]["frequency"] ?? 0); ?>" size="5" id="frequency-<?=$siteId?>"></td>
					</tr>
					<tr>
						<td align="right"><label for="period-<?=$siteId?>"><?= Loc::getMessage("SMO_ORDER_PAY_REMINDER_PERIOD")?>:</label></td>
						<td><input type="text" name="reminder[<?=$siteId?>][period]" value="<?= (int)($arReminder[$siteId]["period"] ?? 0); ?>" size="5" id="period-<?=$siteId?>"></td>
					</tr>
				</table>
				<?php
			}
			$tabControl2->End();
			?>
		</td>
	</tr>
	<?php
	$tabControl->BeginNextTab();
	?>
<script>
var cur_site = {WEIGHT:'<?=CUtil::JSEscape($siteList[0]["ID"])?>',ADDRESS:'<?=CUtil::JSEscape($siteList[0]["ID"])?>'};
function changeSiteList(value, add_id)
{
	var SLHandler = document.getElementById(add_id + '_site_id');
	SLHandler.disabled = value;
}

function changeStoreDeductCondition(value, control_id)
{
	var SLDeductCondition = document.getElementById(control_id);
	SLDeductCondition.disabled = value;
}

function selectSite(current, add_id)
{
	if (current == cur_site[add_id]) return;

	var last_handler = document.getElementById('par_' + add_id + '_' +cur_site[add_id]);
	var current_handler = document.getElementById('par_' + add_id + '_' + current);
	var CSHandler = document.getElementById(add_id + '_current_site');

	last_handler.style.display = 'none';
	current_handler.style.display = 'inline';

	cur_site[add_id] = current;
	CSHandler.value = current;

	return;
}

function setWeightValue(obj)
{
	if (!obj.value) return;

	var selectorUnit = document.forms.opt_form['weight_unit[' + cur_site['WEIGHT'] + ']'];
	var selectorKoef = document.forms.opt_form['weight_koef[' + cur_site['WEIGHT'] + ']'];

	if (selectorKoef && selectorUnit)
	{
		selectorKoef.value = obj.value;
		selectorUnit.value = obj.options[obj.selectedIndex].text;
	}
}

function showAllowGuestOrderViewPaths(target)
{
	var allowPaths = document.getElementsByClassName('sale_allow_guest_order_view');
	for (id in allowPaths)
	{
		if (allowPaths[id] instanceof Node)
		{
			if (target.checked)
			{
				allowPaths[id].style.display = 'table-row';
			}
			else
			{
				allowPaths[id].style.display = 'none';
			}
		}
	}
}

function allowAutoDelivery(value)
{
	var allowDeliveryCheckbox = document.getElementById('PAYED_2_ALLOW_DELIVERY');

	if (value === false) {
		allowDeliveryCheckbox.disabled = true;
		allowDeliveryCheckbox.checked = false;
	} else {
		allowDeliveryCheckbox.disabled = false;
	}
}
</script>
	<?php
	$differentWeight = Option::get('sale', 'WEIGHT_different_set') === 'Y';
	?>
	<tr>
		<td valign="top" width="40%"><?= Loc::getMessage('SMO_PAR_DIF_SETTINGS'); ?></td>
		<td valign="top" width="60%">
			<input type="hidden" name="WEIGHT_dif_settings" value="N" id="dif_settings_hidden">
			<input type="checkbox" name="WEIGHT_dif_settings" value="Y" id="dif_settings" <?= ($differentWeight ? ' checked' : ''); ?> onclick="changeSiteList(!this.checked, 'WEIGHT')">
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('SMO_PAR_SITE_LIST'); ?></td>
		<td>
			<select name="site" id="WEIGHT_site_id"<?= ($differentWeight ? '' : ' disabled'); ?> onchange="selectSite(this.value, 'WEIGHT')">
			<?php
			foreach ($siteList as $site)
			{
				?>
				<option value="<?= $site['SAFE_ID']; ?>"><?= $site['SAFE_NAME']; ?></option>
				<?php
			}
			unset($site);
			?></select>
			<input type="hidden" name="WEIGHT_current_site" id="WEIGHT_current_site" value="<?=$siteList[0]['SAFE_ID']; ?>">
		</td>
	</tr>
	<tr>
		<td valign="top" colspan="2">
		<?php
		$arUnitList = CSaleMeasure::GetList('W');
		if (!is_array($arUnitList))
		{
			$arUnitList = [];
		}
		$firstSite = true;
		foreach ($siteList as $site):
			?>
			<div id="par_WEIGHT_<?= $site['SAFE_ID']; ?>" style="display: <?= ($firstSite ? 'inline' : 'none'); ?>;">
			<table cellpadding="0" cellspacing="2" class="adm-detail-content-table edit-table">
			<tr class="heading">
				<td align="center" colspan="2"><?= Loc::getMessage('SMO_PAR_SITE_PARAMETERS'); ?></td>
			</tr>
			<tr>
				<td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMO_PAR_SITE_WEIGHT_UNIT_SALE'); ?></td>
				<td width="60%" class="adm-detail-content-cell-r">
					<select name="weight_unit_tmp[<?= $site['SAFE_ID']; ?>]" onchange="setWeightValue(this)">
					<?php
					$selectedWeightUnit = Option::get(
						'sale',
						'weight_unit',
						Loc::getMessage('SMO_PAR_WEIGHT_UNIT_GRAMM'),
						$site['ID']
					);
					foreach ($arUnitList as $key => $arM)
					{
						?>
						<option value="<?= (float)$arM['KOEF']; ?>"<?= ($selectedWeightUnit === $arM['NAME'] ? ' selected' : ''); ?>><?= htmlspecialcharsbx($arM['NAME']); ?></option>
						<?php
					}
					?></select>
				</td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l"><?= Loc::getMessage('SMO_PAR_WEIGHT_UNIT'); ?></td>
				<td class="adm-detail-content-cell-r">
					<input type="text" name="weight_unit[<?= $site['SAFE_ID']; ?>]" size="5" value="<?= htmlspecialcharsbx($selectedWeightUnit); ?>">
				</td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l"><?= Loc::getMessage('SMO_PAR_WEIGHT_KOEF'); ?></td>
				<td class="adm-detail-content-cell-r">
					<input type="text" name="weight_koef[<?= $site['SAFE_ID']; ?>]" size="5" value="<?=htmlspecialcharsbx(Option::get('sale', 'weight_koef', 1, $site['ID'])); ?>">
				</td>
			</tr>
			</table>
			</div>
		<?php
			$firstSite = false;
		endforeach;
		unset($site, $firstSite);
		?>
		</td>
	</tr>
<?php
$tabControl->BeginNextTab();
	$differentAddress = Option::get('sale', 'ADDRESS_different_set') === 'Y';
?>
	<tr>
		<td width="40%"><?= Loc::getMessage('SMO_DIF_SETTINGS'); ?></td>
		<td width="60%">
			<input type="hidden" name="ADDRESS_dif_settings" value="N" id="ADDRESS_dif_settings_hidden">
			<input type="checkbox" name="ADDRESS_dif_settings" value="Y" id="ADDRESS_dif_settings"<?= ($differentAddress ? ' checked' : ''); ?> onclick="changeSiteList(!this.checked, 'ADDRESS')">
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('SMO_SITE_LIST'); ?></td>
		<td>
			<select name="site" id="ADDRESS_site_id"<?= ($differentAddress ? '' : ' disabled'); ?> onchange="selectSite(this.value, 'ADDRESS')">
			<?php
			foreach ($siteList as $site):
				?>
				<option value="<?= $site['SAFE_ID']; ?>"><?= $site['SAFE_NAME']; ?></option>
				<?php
			endforeach;
			unset($site);
			?>
			</select>
			<input type="hidden" name="ADDRESS_current_site" id="ADDRESS_current_site" value="<?= $siteList[0]['SAFE_ID']; ?>">
		</td>
	</tr>
	<tr>
		<td colspan="2" valign="top">
<?php
$firstSite = true;
foreach ($siteList as $site):
	$location_zip = Option::get('sale', 'location_zip', '', $site['ID']);
	$location = Option::get('sale', 'location', '', $site['ID']);

	if (!$lpEnabled)
	{
		$location = (int)$location;
	}

	if (!$lMigrated)
	{
		$sales_zone_countries = SalesZone::getCountriesIds($site['ID']);
		$sales_zone_regions = SalesZone::getRegionsIds($site['ID']);
		$sales_zone_cities = SalesZone::getCitiesIds($site['ID']);
	}

	if ($location_zip === '0')
	{
		$location_zip = '';
	}
?>
		<div id="par_ADDRESS_<?= $site['SAFE_ID']; ?>" style="display: <?= ($firstSite ? 'inline' : 'none'); ?>">
		<table cellpadding="0" cellspacing="2" border="0" width="60%" align="center">
			<tr class="heading">
				<td align="center" colspan="2"><?= Loc::getMessage('SMO_PAR_SITE_ADRES'); ?></td>
			</tr>
			<tr>
				<td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMO_LOCATION_ZIP'); ?></td>
				<td width="60%" class="adm-detail-content-cell-r">
					<input type="text" name="location_zip[<?= $site['SAFE_ID']; ?>]" value="<?= htmlspecialcharsbx($location_zip); ?>" size="5">
				</td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l"><?= Loc::getMessage('SMO_LOCATION_SHOP_CITY'); ?>:</td>
				<td class="adm-detail-content-cell-r">
					<?php
					if($lpEnabled):
						$APPLICATION->IncludeComponent(
							'bitrix:sale.location.selector.' . $widgetType,
							'',
							[
								'ID' => '',
								'CODE' => $location,
								'INPUT_NAME' => 'location['.$site['SAFE_ID'].']',
								'PROVIDE_LINK_BY' => 'code',
								'SHOW_ADMIN_CONTROLS' => 'N',
								'SELECT_WHEN_SINGLE' => 'N',
								'FILTER_BY_SITE' => 'N',
								'SHOW_DEFAULT_LOCATIONS' => 'N',
								'SEARCH_BY_PRIMARY' => 'Y',
							],
							false,
							[
								'HIDE_ICONS' => 'Y',
							]
						);
					else:
						?>
						<select name="location[<?= $site['SAFE_ID']; ?>]">
							<option value=""></option>
							<?php
							$dbLocationList = CSaleLocation::GetList(
								[
									'COUNTRY_NAME_LANG' => 'ASC',
									'REGION_NAME_LANG' => 'ASC',
									'CITY_NAME_LANG' => 'ASC',
								],
								[],
								LANGUAGE_ID
							);
							while ($arLocation = $dbLocationList->GetNext()):
								$arLocation['ID'] = (int)$arLocation['ID'];
								$locationName = (string)$arLocation['COUNTRY_NAME'];
								$arLocation['REGION_NAME'] = (string)$arLocation['REGION_NAME'];
								$arLocation['CITY_NAME'] = (string)$arLocation['CITY_NAME'];

								if ($arLocation['REGION_NAME'] !== '')
								{
									if ($locationName !== '')
									{
										$locationName .= ' - ';
									}
									$locationName .= $arLocation['REGION_NAME'];
								}
								if ($arLocation['CITY_NAME'] !== '')
								{
									if ($locationName !== '')
										$locationName .= ' - ';
									$locationName .= $arLocation['CITY_NAME'];
								}
								if ($locationName === '')
								{
									$locationName = $arLocation['ID'];
								}
								?>
								<option value="<?= $arLocation['ID']; ?>"<?= ($location === $arLocation['ID'] ? ' selected' : ''); ?>><?= htmlspecialcharsbx($locationName); ?></option>
								<?php
							endwhile;
							?>
						</select>
						<?php
					endif;
					?>
				</td>
			</tr>
			<?php
			if(!$lpEnabled):
				?>
			<tr>
				<td class="adm-detail-content-cell-l" valign="top">
					<?= Loc::getMessage('SMO_LOCATION_SALES_ZONE'); ?>:
					<script>
						BX.ready( function(){
							BX.bind(BX("sales_zone_countries_<?=$site['SAFE_ID']; ?>"), 'change', BX.Sale.Options.onCountrySelect);
							BX.bind(BX("sales_zone_regions_<?= $site['SAFE_ID']; ?>"), 'change', BX.Sale.Options.onRegionSelect);
						});
					</script>
				</td>
				<td class="adm-detail-content-cell-r">
					<?php
						$sales_zone_countries = SalesZone::getCountriesIds($site['ID']);
						$sales_zone_regions = SalesZone::getRegionsIds($site['ID']);
						$sales_zone_cities = SalesZone::getCitiesIds($site['ID']);
						?>
						<table><tr>
							<th><?= Loc::getMessage('SMO_LOCATION_COUNTRIES'); ?></th>
							<th><?= Loc::getMessage('SMO_LOCATION_REGIONS'); ?></th>
							<th><?= Loc::getMessage('SMO_LOCATION_CITIES'); ?></th>
						</tr><tr>
							<td>
								<select id="sales_zone_countries_<?= $site['SAFE_ID']; ?>" name="sales_zone_countries[<?= $site['SAFE_ID']; ?>][]" multiple size="10" class="sale-options-location-mselect">
									<option value=""<?= in_array('', $sales_zone_countries) ? ' selected' : ''; ?>><?= Loc::getMessage('SMO_LOCATION_ALL'); ?></option>
									<option value="NULL"<?=in_array('NULL', $sales_zone_countries) ? ' selected' : ''?>><?= Loc::getMessage('SMO_LOCATION_NO_COUNTRY'); ?></option>
									<?php
									$dbCountryList = CSaleLocation::GetCountryList(['NAME_LANG' => 'ASC']);
									while ($arCountry = $dbCountryList->fetch()):
										?>
										<option value="<?=(int)$arCountry['ID']?>"<?=in_array($arCountry['ID'], $sales_zone_countries) ? ' selected' : ''?>><?= htmlspecialcharsbx($arCountry['NAME_LANG']); ?></option>
										<?php
									endwhile;
									unset($dbCountryList);
									?>
								</select>
							</td><td>
								<select id="sales_zone_regions_<?= $site['SAFE_ID']; ?>" name="sales_zone_regions[<?= $site['SAFE_ID']; ?>][]" multiple size="10" class="sale-options-location-mselect">
									<option value=""<?= in_array('', $sales_zone_regions) ? ' selected' : ''?>><?= Loc::getMessage('SMO_LOCATION_ALL'); ?></option>
									<option value="NULL"<?= in_array('NULL', $sales_zone_regions) ? ' selected' : ''?>><?= Loc::getMessage('SMO_LOCATION_NO_REGION'); ?></option>
									<?php
									if (!in_array('', $sales_zone_countries)):
										$arRegions = SalesZone::getRegions($sales_zone_countries, LANGUAGE_ID);
										foreach ($arRegions as $regionId => $arRegionName):
											?>
											<option value="<?= $regionId; ?>"<?= in_array($regionId, $sales_zone_regions) ? ' selected' : ''; ?>><?= htmlspecialcharsbx($arRegionName); ?></option>
											<?php
										endforeach;
									endif;
									?>
								</select>
							</td><td>
							<select id="sales_zone_cities_<?= $site['SAFE_ID']; ?>" name="sales_zone_cities[<?= $site['SAFE_ID']; ?>][]" multiple size="10" class="sale-options-location-mselect">
								<option value=""<?= in_array('', $sales_zone_cities) ? ' selected' : ''?>><?= Loc::getMessage('SMO_LOCATION_ALL'); ?></option>
								<?php
								if (!in_array('', $sales_zone_regions)):
									$arCities = SalesZone::getCities($sales_zone_countries, $sales_zone_regions, LANGUAGE_ID);
									foreach($arCities as $cityId => $cityName):
										?>
										<option value="<?= $cityId; ?>"<?= in_array($cityId, $sales_zone_cities) ? ' selected' : ''?>><?= htmlspecialcharsbx($cityName); ?></option>
										<?php
									endforeach;
								endif;
								?>
							</select>
						</td>
						</tr></table>
				</td>
			</tr>
			<?php
			endif;
			?>
		</table>
		</div>
<?php
	$firstSite = false;
endforeach;
unset($site);
?>
		</td>
	</tr>
<?php
if (CBXFeatures::IsFeatureEnabled('SaleCCards') && Option::get('sale', 'use_ccards') === 'Y')
{
	$tabControl->BeginNextTab();

	if (!CSaleUserCards::CheckPassword())
	{
		?><tr>
			<td colspan="2"><?php
				CAdminMessage::ShowMessage(
					Loc::getMessage(
						'SMO_NO_VALID_PASSWORD',
						[
							'#ROOT#' => $_SERVER['DOCUMENT_ROOT'],
						]
					)
				);
			?></td>
		</tr><?php
	}
	?>
	<tr>
		<td valign="top" width="50%">
			<?= Loc::getMessage('SMO_PATH2CRYPT_FILE'); ?>
		</td>
		<td valign="middle" width="50%">
			<input type="text" size="40" value="<?= htmlspecialcharsbx(Option::get('sale', 'sale_data_file')); ?>" name="sale_data_file">
		</td>
	</tr>
	<tr>
		<td valign="top">
		<?= Loc::getMessage('SMO_CRYPT_ALGORITHM'); ?>
		</td>
		<td valign="middle">
				<?php
				$val = Option::get('sale', 'crypt_algorithm');
				$selectList = [
					'RC4' => 'RC4',
					'AES' => 'AES (Rijndael) - ' . Loc::getMessage('SMO_NEED_MCRYPT'),
					'3DES' => '3DES (Triple-DES) - ' . Loc::getMessage('SMO_NEED_MCRYPT'),
				];
				?>
				<select name="crypt_algorithm">
				<?php
				foreach ($selectList as $option => $message):
					?>
					<option value="<?= $option; ?>>"<?= ($val === $option ? ' selected' : ''); ?>><?= $message; ?>></option>
					<?php
				endforeach;
				unset($selectList);
				?>
				</select>
		</td>
	</tr>
	<?php
}

$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?= Loc::getMessage("SMO_ADDITIONAL_SITE_PARAMS")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
		<table cellspacing="0" cellpadding="0" border="0" class="internal">
		<tr class="heading">
			<td valign="top">
				<?= Loc::getMessage('SALE_LANG'); ?>
			</td>
			<td valign="top">
				<?= Loc::getMessage('SALE_CURRENCY'); ?>
			</td>
			<td valign="top">
				<?= Loc::getMessage('SMO_GROUPS2SITE'); ?>
			</td>
		</tr>
		<?php
		$userGroupList = [];
		$dbGroups = CGroup::GetList('c_sort', 'asc', ['ANONYMOUS' => 'N']);
		while ($arGroup = $dbGroups->Fetch())
		{
			$arGroup['ID'] = (int)$arGroup['ID'];

			if ($arGroup['ID'] === 1 || $arGroup['ID'] === 2)
				continue;

			$userGroupList[] = [
				'ID' => $arGroup['ID'],
				'SAFE_NAME' => htmlspecialcharsbx($arGroup['NAME']),
			];
		}

		foreach($siteList as $site)
		{
			?>
			<tr>
				<td valign="top">
					[<a href="site_edit.php?LID=<?= $site['SAFE_ID']; ?>&lang=<?= LANGUAGE_ID; ?>" title="<?= htmlspecialcharsbx(Loc::getMessage('SALE_SITE_ALT')); ?>"><?= $site['SAFE_ID']; ?></a>] <?= $site['SAFE_NAME']; ?>
				</td>
				<td valign="top">
					<?php
					$arCurr = CSaleLang::GetByID($site['ID']);
					echo CCurrency::SelectBox(
						'CURRENCY_' . $site['SAFE_ID'],
						$arCurr['CURRENCY'] ?? '',
						Loc::getMessage('SALE_NOT_SET')
					);
					?>
				</td>
				<td valign="top">
					<?php
					$arCurrentGroups = [];
					$dbSiteGroupsList = CSaleGroupAccessToSite::GetList(
						[],
						[
							'SITE_ID' => $site['ID'],
						],
						false,
						false,
						[
							'GROUP_ID',
						]
					);
					while ($arSiteGroup = $dbSiteGroupsList->Fetch())
					{
						$arCurrentGroups[] = (int)$arSiteGroup['GROUP_ID'];
					}
					unset($arSiteGroup, $dbSiteGroupsList);

					unset($arGroup, $dbGroups);
					?>
					<select name="SITE_USER_GROUPS_<?= $site['SAFE_ID']; ?>[]" multiple size="5">
					<?php
					foreach ($userGroupList as $userGroup):
						?>
						<option value="<?= $userGroup['ID'] ?>"<?= (in_array($userGroup['ID'], $arCurrentGroups) ? ' selected' : ''); ?>><?= $userGroup['SAFE_NAME']; ?></option>
						<?php
					endforeach;
					?>
					</select>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		</td>
	</tr>
<?php
$tabControl->BeginNextTab();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';

$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan='2'><?= Loc::getMessage('SALE_AUTO_ORDER_STATUS_TITLE'); ?></td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_PAY_TO_STATUS'); ?>
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'status_on_paid');
			?>
			<select name="PAID_STATUS">
			<?php
			foreach($arStatuses as $statusId => $safeName):
				?>
				<option value="<?= $statusId; ?>"<?= ($val === $statusId ? ' selected' : ''); ?>><?= $safeName; ?></option>
				<?php
			endforeach;
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage("SALE_HALF_PAY_TO_STATUS"); ?>
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'status_on_half_paid');
			?>
			<select name="HALF_PAID_STATUS">
			<?php
			foreach($arStatuses as $statusId => $safeName):
				?>
				<option value="<?= $statusId; ?>"<?= ($val === $statusId ? ' selected' : ''); ?>><?= $safeName; ?></option>
			<?php
			endforeach;
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_CHANGE_ALLOW_DELIVERY_AFTER_PAID'); ?>
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'status_on_change_allow_delivery_after_paid');
			$isPayed2AllowDelivery = Option::get('sale', 'status_on_payed_2_allow_delivery');

			if ($val === '')
			{
				$val = ($isPayed2AllowDelivery == 'Y') ? Sale\Configuration::ALLOW_DELIVERY_ON_FULL_PAY : 'N';
			}
			?>
			<select name="CHANGE_ALLOW_DELIVERY_AFTER_PAID">
				<option value="N"<?= ($val === 'N' ? ' selected' : ''); ?>><?= Loc::getMessage('SALE_DENY_STATUS'); ?></option>
				<?php
				foreach (Sale\Configuration::getAllowDeliveryAfterPaidConditionList(true) as $payTypeId => $payTitle):
					?>
					<option value="<?= $payTypeId; ?>"<?= ($payTypeId === $val ? ' selected' : ''); ?>><?= htmlspecialcharsbx($payTitle); ?></option>
					<?php
				endforeach;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_ALLOW_DELIVERY_TO_STATUS'); ?>
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'status_on_allow_delivery');
			?>
			<select name="ALLOW_DELIVERY_STATUS">
			<?php
			foreach($arStatuses as $statusId => $safeName):
				?>
				<option value="<?= $statusId; ?>"<?= ($val === $statusId ? ' selected' : ''); ?>><?= $safeName; ?></option>
				<?php
			endforeach;
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_ALLOW_DELIVERY_ONE_OF_TO_STATUS'); ?>
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'status_on_allow_delivery_one_of');
			?>
			<select name="ALLOW_DELIVERY_ONE_OF_STATUS">
			<?php
			foreach($arStatuses as $statusId => $safeName):
				?>
				<option value="<?= $statusId; ?>"<?= ($val === $statusId ? ' selected' : ''); ?>><?= $safeName; ?></option>
				<?php
			endforeach;
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_SHIPMENT_SHIPPED_TO_STATUS'); ?>
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'status_on_shipped_shipment');
			?>
			<select name="SHIPMENT_SHIPPED_STATUS">
			<?php
			foreach($arStatuses as $statusId => $safeName):
				?>
				<option value="<?= $statusId; ?>"<?= ($val === $statusId ? ' selected' : ''); ?>><?= $safeName; ?></option>
				<?php
			endforeach;
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_SHIPMENT_SHIPPED_ONE_OF_TO_STATUS'); ?>
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'status_on_shipped_shipment_one_of');
			?>
			<select name="SHIPMENT_SHIPPED_ONE_OF_STATUS">
			<?php
			foreach($arStatuses as $statusId => $safeName):
				?>
				<option value="<?= $statusId; ?>"<?= ($val === $statusId ? ' selected' : ''); ?>><?= $safeName; ?></option>
				<?php
			endforeach;
			?>
			</select>
		</td>
	</tr>
	<tr class='heading'>
		<td colspan='2'><?= Loc::getMessage('SALE_AUTO_SHIPMENT_STATUS_TITLE'); ?></td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_SHIPMENT_ALLOW_DELIVERY_TO_SHIPMENT_STATUS'); ?>
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'shipment_status_on_allow_delivery');
			?>
			<select name="SHIPMENT_ALLOW_DELIVERY_TO_SHIPMENT_STATUS">
			<?php
			foreach ($delieryStatuses as $statusId => $safeName):
				?>
				<option value="<?= $statusId; ?>"<?= ($val === $statusId ? ' selected' : ''); ?>><?= $safeName; ?></option>
				<?php
			endforeach;
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<?= Loc::getMessage('SALE_SHIPMENT_SHIPPED_TO_SHIPMENT_STATUS'); ?>
		</td>
		<td>
			<?php
			$val = Option::get('sale', 'shipment_status_on_shipped');
			?>
			<select name="SHIPMENT_SHIPPED_TO_SHIPMENT_STATUS">
			<?php
			foreach ($delieryStatuses as $statusId => $safeName):
				?>
				<option value="<?= $statusId; ?>"<?= ($val === $statusId ? ' selected' : ''); ?>><?= $safeName; ?></option>
				<?php
			endforeach;
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<label for="ALLOW_DEDUCTION_ON_DELIVERY"><?= Loc::getMessage('SALE_ALLOW_DEDUCTION_ON_DELIVERY'); ?></label>
		</td>
		<td>
			<?php
			//$valDeductOnDelivery = Option::get("sale", "allow_deduction_on_delivery", "");
			?>
			<input type="hidden" name="ALLOW_DEDUCTION_ON_DELIVERY" id="ALLOW_DEDUCTION_ON_DELIVERY_hidden" value="N">
			<input type="checkbox" name="ALLOW_DEDUCTION_ON_DELIVERY" id="ALLOW_DEDUCTION_ON_DELIVERY" value="Y"<?= ($valDeductOnDelivery === 'Y' ? ' checked' : ''); ?> onclick="javascript:toggleDefaultStores(this);">
			<script>
				function toggleDefaultStores(el)
				{
					var elements = document.getElementsByClassName('default_deduct_store_control');
					for (var i = 0; i < elements.length; ++i)
					{
						var site_id = elements[i].id.replace('default_deduct_store_control_', ''),
							selector = BX("default_store_select_" + site_id);

						elements[i].style.display = (el.checked && selector.length > 0) ? 'table-row' : 'none';
						BX("default_store_select_save_" + site_id).value = (el.checked && selector.length > 0) ? "Y" : "N";
					}

				}
			</script>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?= Loc::getMessage('SALE_AUTO_SHP_TR_STATUS_ON'); ?></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('SALE_TRACKING_CHECK_SWITCH'); ?>:</td>
		<td>
			<input id="sale-option-tracking-auto-switch_hidden" type="hidden" value="N" name="tracking_check_switch">
			<input id="sale-option-tracking-auto-switch" type="checkbox" value="Y" onClick="toggleTrackingAuto();" name="tracking_check_switch"<?= ($settings["tracking_check_switch"] === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr class="sale-option-tracking-auto">
		<td><?= Loc::getMessage('SALE_TRACKING_CHECK_PERIOD'); ?>:</td>
		<td><input type="text" name="tracking_check_period" value="<?= ($settings['tracking_check_period'] ?: '0'); ?>"></td>
	</tr>

	<tr class="heading sale-option-tracking-auto">
		<td colspan="2"><?= Loc::getMessage('SALE_AUTO_SHP_TR_STATUS_MAP'); ?></td>
	</tr>
	<?php
	$shipmentStatuses = [];
	$dbRes = Sale\Internals\StatusTable::getList([
		'select' => [
			'ID',
			'SORT',
			'NAME' => 'STATUS_LANG.NAME',
		],
		'filter' => [
			'=STATUS_LANG.LID' => LANGUAGE_ID,
			'=TYPE' => Sale\Internals\StatusTable::TYPE_SHIPMENT,
		],
		'order' => [
			'SORT' => 'ASC',
			'ID' => 'ASC',
		],
	]);
	while ($shipmentStatus = $dbRes->fetch())
	{
		$shipmentStatuses[$shipmentStatus['ID']] = htmlspecialcharsbx($shipmentStatus['NAME'] . ' [' . $shipmentStatus['ID'] . ']');
	}
	unset($dbRes);

	$trackingStatuses = Sale\Delivery\Tracking\Manager::getStatusesList();
	?><tr class="sale-option-tracking-auto"><td><b><?= Loc::getMessage("SALE_TRACKING_TSTATUSES")?></b></td><td><b><?= Loc::getMessage("SALE_TRACKING_SSTATUSES")?></b></td></tr><?php
	foreach($trackingStatuses as $trackingStatusId => $trackingStatusName):?>
		<tr class="sale-option-tracking-auto">
			<td><?=$trackingStatusName?>:</td>
			<td>
				<select name="tracking_map_statuses[<?=$trackingStatusId?>]">
					<option value=""><?= Loc::getMessage("SALE_TRACKING_NOT_USE")?></option>
					<?php
					foreach($shipmentStatuses as $statusId => $safeName):
						?>
						<option value="<?= $statusId; ?>"<?= (($settings['tracking_map_statuses'][$trackingStatusId] ?? '') === $statusId ? " selected" : ""); ?>><?= $safeName; ?></option>
						<?php
					endforeach;
					?>
				</select>
			</td>
		</tr>
	<?php
	endforeach;
	$tabControl->BeginNextTab();

	$filterValues = [];
	$serializedFilterValue = Option::get('sale', 'archive_params');
	if ($serializedFilterValue !== '')
	{
		$filterValues = unserialize($serializedFilterValue, ['allowed_classes' => false]);
	}
	if (!is_array($filterValues))
	{
		$filterValues = [];
	}
	if (isset($filterValues['LID']) && !isset($filterValues['@LID']))
	{
		$filterValues['@LID'] = $filterValues['LID'];
		unset($filterValues['LID']);
	}
	if (isset($filterValues['STATUS_ID']) && !isset($filterValues['@STATUS_ID']))
	{
		$filterValues['@STATUS_ID'] = $filterValues['STATUS_ID'];
		unset($filterValues['STATUS_ID']);
	}
	$enableRegularArchive = Option::get('sale', 'regular_archive_active') === 'Y';
	$archiveLimit = (int)Option::get('sale', 'archive_limit');
	$archiveTimeLimit = (int)Option::get('sale', 'archive_time_limit');
	?>
	<tr>
		<td>
			<label for="ORDER_ARCHIVE_REGULAR_ACCEPT"><?= Loc::getMessage('SALE_ORDER_ARCHIVE_ACCEPT'); ?>:</label>
		</td>
		<td>
			<input type="hidden" name="archive_regular_accept" id="ORDER_ARCHIVE_REGULAR_ACCEPT_hidden" value="N">
			<input type="checkbox" name="archive_regular_accept" id="ORDER_ARCHIVE_REGULAR_ACCEPT" value="Y"<?= ($enableRegularArchive ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr>
		<td>
			<label for="archive_limit"><?= Loc::getMessage('SALE_ORDER_ARCHIVE_LIMIT_BY_HIT'); ?>:</label>
		</td>
		<td>
			<input type="text" name="archive_limit" value="<?= $archiveLimit ?: 10; ?>" size="5" id="archive_limit">
		</td>
	</tr>
	<tr>
		<td><label for="archive_time_limit"><?= Loc::getMessage('SALE_ORDER_ARCHIVE_MAX_TIME_BY_HIT'); ?>:</label></td>
		<td>
			<input type="text" name="archive_time_limit" value="<?= $archiveTimeLimit ?: 5; ?>" size="5" id="archive_time_limit">
			<?= Loc::getMessage("SALE_ORDER_ARCHIVE_SEC"); ?>
		</td>
	</tr>
	<?php
	if (!$enableRegularArchive)
	{
		?>
		<tr>
			<td align="center" colspan="2">
				<a href="sale_archive.php"><?= Loc::getMessage('SALE_ORDER_ARCHIVE_FIRST_START_NOTE'); ?></a>
			</td>
		</tr>
		<?php
	}
	?>
	<tr class="heading">
		<td colspan="2"><?= Loc::getMessage('SALE_ORDER_ARCHIVE_TITLE'); ?></td>
	</tr>
	<tr>
		<td><label for="archive_period"><?= Loc::getMessage('SALE_ORDER_ARCHIVE_PERIOD'); ?>:</label></td>
		<td><?php
		$filterValuePeriod = (int)($filterValues['PERIOD'] ?? 0);
		if ($filterValuePeriod <= 0)
		{
			$filterValuePeriod = 365;
		}
		?><input type="text" name="archive_period" value="<?= $filterValuePeriod; ?>" size="5" id="archive_period"></td>
	</tr>
	<tr>
		<td valign="top"><label for="archive_blocked_order_accept"><?= Loc::getMessage('ARCHIVE_BLOCKED_ORDER_ACCEPT'); ?>:</label></td>
		<td>
			<input type="hidden" name="archive_blocked_order_accept" id="archive_blocked_order_accept_hidden" value="N">
			<input type="checkbox" name="archive_blocked_order_accept" id="archive_blocked_order_accept" value="Y"<?= (Option::get('sale', 'archive_blocked_order') === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<?php
	if ($siteCount > 1)
	{
		$nonEmptyArchiveSites = !empty($filterValues['@LID']) && is_array($filterValues['@LID']);
	?>
		<tr valign="top">
			<td><label for="archive_site"><?=Loc::getMessage('SALE_LANG'); ?>:</label></td>
			<td>
				<select name="archive_site[]" id="archive_site" multiple size="<?=($siteCount < 5) ? $siteCount : 5; ?>">
					<?php
						foreach ($siteList as $site)
						{
							?>
							<option
								value="<?= $site['SAFE_ID']; ?>"
								<?php
									$checkedSite = true;
									if ($nonEmptyArchiveSites)
									{
										$checkedSite = in_array($site['ID'], $filterValues['@LID']);
									}
									if ($checkedSite)
									{
										echo ' selected';
									}
								?>
							>
								<?= $site['SAFE_NAME']; ?>
							</option>
							<?php
						}
					?>
				</select>
			</td>
		</tr>
	<?php
		unset($nonEmptyArchiveSites);
	}
	?>
	<tr>
		<td valign="top"><?= Loc::getMessage("SALE_ORDER_ARCHIVE_STATUS")?>:</td>
		<td>
			<select name="archive_status_id[]" multiple size="3">
				<?php
				$statusesList = Sale\OrderStatus::getStatusesUserCanDoOperations(
					$USER->GetID(),
					array('view')
				);
				$allStatusNames = Sale\OrderStatus::getAllStatusesNames();
				$nonEmptyStatuses = !empty($filterValues['@STATUS_ID']) && is_array($filterValues['@STATUS_ID']);
				foreach($statusesList as  $statusCode)
				{
					if (!$statusName = $allStatusNames[$statusCode])
						continue;
					?>
					<option
						value="<?= htmlspecialcharsbx($statusCode) ?>"
						<?php
							$checkedStatus = true;
							if ($nonEmptyStatuses)
							{
								$checkedStatus = in_array($statusCode, $filterValues['@STATUS_ID']);
							}
							if ($checkedStatus)
							{
								echo " selected";
							}
						?>
					>
						[<?= htmlspecialcharsbx($statusCode) ?>] <?= htmlspecialcharsbx($statusName) ?>
					</option>
					<?php
				}
				unset($nonEmptyStatuses);
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<label for="ORDER_ARCHIVE_PAYED"><?= Loc::getMessage("SALE_ORDER_ARCHIVE_PAYED"); ?>:</label>
		</td>
		<td><?php
			$optionValue = $filterValues['=PAYED'] ?? '';
			if ($optionValue !== 'Y' && $optionValue !== 'N')
			{
				$optionValue = '';
			}
			?>
			<select name="archive_payed" id="ORDER_ARCHIVE_PAYED">
				<option value=""<?= ($optionValue === '' ? ' selected' : ''); ?>><?= Loc::getMessage("SALE_ORDER_ARCHIVE_ALL"); ?></option>
				<option value="Y"<?= ($optionValue === 'Y' ? ' selected' : ''); ?>><?= Loc::getMessage("SALE_ORDER_ARCHIVE_YES"); ?></option>
				<option value="N"<?= ($optionValue === 'N' ? ' selected' : ''); ?>><?= Loc::getMessage("SALE_ORDER_ARCHIVE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<label for="ORDER_ARCHIVE_CANCELED"><?= Loc::getMessage("SALE_ORDER_ARCHIVE_CANCELED"); ?>:</label>
		</td>
		<td><?php
			$optionValue = $filterValues['=CANCELED'] ?? '';
			if ($optionValue !== 'Y' && $optionValue !== 'N')
			{
				$optionValue = '';
			}
			?>
			<select name="archive_canceled" id="ORDER_ARCHIVE_CANCELED">
				<option value=""<?= ($optionValue === '' ? ' selected' : ''); ?>><?= Loc::getMessage("SALE_ORDER_ARCHIVE_ALL"); ?></option>
				<option value="Y"<?= ($optionValue === 'Y' ? ' selected' : ''); ?>><?= Loc::getMessage("SALE_ORDER_ARCHIVE_YES"); ?></option>
				<option value="N"<?= ($optionValue === 'N' ? ' selected' : ''); ?>><?= Loc::getMessage("SALE_ORDER_ARCHIVE_NO"); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<label for="ORDER_ARCHIVE_DEDUCTED"><?= Loc::getMessage("SALE_ORDER_ARCHIVE_DEDUCTED"); ?>:</label>
		</td>
		<td><?php
			$optionValue = $filterValues['=DEDUCTED'] ?? '';
			if ($optionValue !== 'Y' && $optionValue !== 'N')
			{
				$optionValue = '';
			}
			?>
			<select name="archive_deducted" id="ORDER_ARCHIVE_DEDUCTED">
				<option value=""<?= ($optionValue === '' ? ' selected' : ''); ?>><?= Loc::getMessage("SALE_ORDER_ARCHIVE_ALL"); ?></option>
				<option value="Y"<?= ($optionValue === 'Y' ? ' selected' : ''); ?>><?= Loc::getMessage("SALE_ORDER_ARCHIVE_YES"); ?></option>
				<option value="N"<?= ($optionValue === 'N' ? ' selected' : ''); ?>><?= Loc::getMessage("SALE_ORDER_ARCHIVE_NO"); ?></option>
			</select>
		</td>
	</tr>
	<?php
	$tabControl->BeginNextTab();
	?>
	<div class="adm-numerator-use-template-checkbox-outer">
		<span class="adm-numerator-use-template-checkbox-title"><?= Loc::getMessage('NUMERATOR_NOT_USE_CHECKBOX_TITLE'); ?></span>
		<div class="adm-numerator-use-template-checkbox-inner">
			<input type="hidden" name="hideNumeratorSettings" id="hideNumeratorSettings_hidden" value="N">
			<input type="checkbox" class="adm-designed-checkbox" name="hideNumeratorSettings" id="hideNumeratorSettings"
				value="Y"
				<?php
				if ($numeratorForOrdersId==''):
					?>
					checked=""
					<?php
				endif;
				?>>
			<label class="adm-designed-checkbox-label" for="hideNumeratorSettings" title=""></label>
		</div>
	</div>
	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:main.numerator.edit',
		'admin',
		[
			'NUMERATOR_TYPE' => 'ORDER',
			'CSS_WRAP_CLASS' => 'js-numerator-form',
			'NUMERATOR_ID' => $numeratorForOrdersId,
			'IS_HIDE_NUMERATOR_NAME' => true,
			'IS_HIDE_IS_DIRECT_NUMERATION' => true,
		]
	);
	?>
<?php
$tabControl->Buttons();
$buttonDisable = ($SALE_RIGHT < 'W' ? ' disabled' : '');
?>
<input type="hidden" name="Update" value="Y">
<input type="submit"<?= $buttonDisable; ?> name="Save" value="<?= Loc::getMessage("MAIN_SAVE"); ?>" title="<?= Loc::getMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
<input type="submit"<?= $buttonDisable; ?> name="Apply" value="<?= Loc::getMessage("MAIN_OPT_APPLY"); ?>" title="<?= Loc::getMessage("MAIN_OPT_APPLY_TITLE")?>">
<?php
if ($backUrl !== ''):
	?>
	<input type="button" name="Cancel" value="<?=htmlspecialcharsbx(GetMessage("MAIN_OPT_CANCEL")); ?>" onclick="window.location='<?= htmlspecialcharsbx(CUtil::addslashes($backUrl)); ?>'">
	<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($backUrl)?>">
	<?php
endif;
?>
<input type="submit"<?= $buttonDisable; ?> name="RestoreDefaults" title="<?= htmlspecialcharsbx(GetMessage("MAIN_HINT_RESTORE_DEFAULTS")); ?>" onclick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?= htmlspecialcharsbx(GetMessage("MAIN_RESTORE_DEFAULTS")); ?>">
<?php
$tabControl->End();
?>
</form>
<h2><?= Loc::getMessage('SALE_SYSTEM_PROCEDURES'); ?></h2>
	<?php
	$showbasketDiscountConvert = Option::get('sale', 'basket_discount_converted') !== 'Y' && Main\ModuleManager::isModuleInstalled('catalog');
	if ($showbasketDiscountConvert)
	{
		if (CSaleBasketDiscountConvert::getAllCounter() === 0)
		{
			$adminNotifyIterator = CAdminNotify::GetList(
				[],
				[
					'MODULE_ID' => 'sale',
					'TAG' => 'BASKET_DISCOUNT_CONVERTED',
				]
			);
			if ($adminNotifyIterator)
			{
				if ($adminNotify = $adminNotifyIterator->Fetch())
					CAdminNotify::Delete($adminNotify['ID']);
				unset($adminNotify);
			}
			unset($adminNotifyIterator);
			$showbasketDiscountConvert = false;
		}
	}
	$systemTabs[] = [
		'DIV' => 'saleSysTabReindex',
		'TAB' => Loc::getMessage('SALE_SYSTEM_TAB_REINDEX'),
		'ICON' => 'sale_settings',
		'TITLE' => Loc::getMessage('SALE_SYSTEM_TAB_REINDEX_TITLE'),
	];
	if ($showbasketDiscountConvert)
	{
		$systemTabs[] = [
			'DIV' => 'saleSysTabConvert',
			'TAB' => Loc::getMessage('SALE_SYSTEM_TAB_CONVERT'),
			'ICON' => 'sale_settings',
			'TITLE' => Loc::getMessage('SALE_SYSTEM_TAB_CONVERT_TITLE'),
		];
	}

$systemTabControl = new CAdminTabControl('saleSysTabControl', $systemTabs, true, true);

	$systemTabControl->Begin();
	$systemTabControl->BeginNextTab();
	?><tr><td align="left"><?php
	$firstTop = ' style="margin-top: 0;"';
	?><h4<?= $firstTop; ?>><?= Loc::getMessage('SALE_SYS_PROC_REINDEX_DISCOUNT'); ?></h4>
	<input class="adm-btn-save" type="button" id="sale_discount_reindex" value="<?= htmlspecialcharsbx(Loc::getMessage('SALE_SYS_PROC_REINDEX_DISCOUNT_BTN')); ?>">
	<p><?= Loc::getMessage('SALE_SYS_PROC_REINDEX_DISCOUNT_ALERT'); ?></p><?php
	$firstTop = '';
	?></td></tr><?php
	if ($showbasketDiscountConvert)
	{
		$systemTabControl->BeginNextTab();
		?>
		<tr>
		<td align="left"><?php
		$firstTop = ' style="margin-top: 0;"';
		?><h4<?= $firstTop; ?>><?= Loc::getMessage('SALE_SYS_PROC_CONVERT_BASKET_DISCOUNT'); ?></h4>
		<input class="adm-btn-save" type="button" id="sale_basket_discount" value="<?= htmlspecialcharsbx(Loc::getMessage('SALE_SYS_PROC_CONVERT_BASKET_DISCOUNT_BTN')); ?>">
		<p><?= Loc::getMessage('SALE_SYS_PROC_CONVERT_BASKET_DISCOUNT_ALERT'); ?></p><?php
		$firstTop = '';
		?></td></tr><?php
	}
	$systemTabControl->End();
	?>
<script>
	BX.ready(function(){
		var numeratorSettingsToggle = BX('hideNumeratorSettings');

		if (BX('hideNumeratorSettings').checked)
		{
			hideNumeratorSettings();
		}
		if (!!numeratorSettingsToggle)
		{
			BX.bind(numeratorSettingsToggle, 'click', hideNumeratorSettings);
		}
	});

	function hideNumeratorSettings()
	{
		var numForm = document.querySelector('.js-numerator-form');
		if (numForm)
		{
			if (numForm.style.display === 'none')
			{
				numForm.style.display = 'block'
			}
			else
			{
				numForm.style.display = 'none'
			}
		}
	}

	function toggleTrackingAuto()
	{
		var nodes = BX.findChildren(document, {className:"sale-option-tracking-auto"}, true),
			switchStateOn = BX("sale-option-tracking-auto-switch").checked;

		for(var i in nodes)
			nodes[i].style.display = switchStateOn ? '' : 'none';
	}

	function showDiscountReindex()
	{
		var obDiscount, params;

		params = {
			bxpublic: 'Y',
			sessid: BX.bitrix_sessid()
		};

		var obBtn = {
			title: '<?= CUtil::JSEscape(Loc::getMessage('SALE_OPTIONS_POPUP_WINDOW_CLOSE_BTN')); ?>',
			id: 'close',
			name: 'close',
			action: function () {
				this.parentWindow.Close();
			}
		};

		obDiscount = new BX.CAdminDialog({
			'content_url': '/bitrix/tools/sale/discount_reindex.php?lang=<?= LANGUAGE_ID; ?>',
			'content_post': params,
			'draggable': true,
			'resizable': true,
			'buttons': [obBtn]
		});
		obDiscount.Show();
		return false;
	}
	function showBasketDiscountConvert()
	{
		var obDiscount, params;

		params = {
			bxpublic: 'Y',
			sessid: BX.bitrix_sessid()
		};

		var obBtn = {
			title: '<?= CUtil::JSEscape(Loc::getMessage('SALE_OPTIONS_POPUP_WINDOW_CLOSE_BTN')); ?>',
			id: 'close',
			name: 'close',
			action: function () {
				this.parentWindow.Close();
			}
		};

		obDiscount = new BX.CAdminDialog({
			'content_url': '/bitrix/tools/sale/basket_discount_convert.php?lang=<?= LANGUAGE_ID; ?>',
			'content_post': params,
			'draggable': true,
			'resizable': true,
			'buttons': [obBtn]
		});
		obDiscount.Show();
		return false;
	}
	function showApplyDiscountMode()
	{
		var modeList = BX('tr_discount_apply_mode'),
			showMode = BX('use_sale_discount_only_Y');
		if (!BX.type.isElementNode(modeList) || !BX.type.isElementNode(showMode))
			return;
		BX.style(modeList, 'display', (showMode.checked ? 'none' : 'table-row'));
	}
	BX.ready( function(){
		BX.message['SMO_LOCATION_JS_GET_DATA_ERROR'] = '<?= CUtil::JSEscape(Loc::getMessage('SMO_LOCATION_JS_GET_DATA_ERROR')); ?>';
		BX.message['SMO_LOCATION_ALL'] = '<?= CUtil::JSEscape(Loc::getMessage('SMO_LOCATION_ALL')); ?>';
		BX.message['SMO_LOCATION_NO_COUNTRY'] = '<?= CUtil::JSEscape(Loc::getMessage('SMO_LOCATION_NO_COUNTRY')); ?>';
		BX.message['SMO_LOCATION_NO_REGION'] = '<?= CUtil::JSEscape(Loc::getMessage('SMO_LOCATION_NO_REGION')); ?>';

		var discountReindex = BX('sale_discount_reindex'),
			basketDiscount = BX('sale_basket_discount'),
			showMode = BX('use_sale_discount_only_Y');

		if (!!discountReindex)
			BX.bind(discountReindex, 'click', showDiscountReindex);
		if (!!basketDiscount)
			BX.bind(basketDiscount, 'click', showBasketDiscountConvert);
		if (BX.type.isElementNode(showMode))
			BX.bind(showMode, 'click', showApplyDiscountMode);

		toggleTrackingAuto();
	});
</script>
<?php
