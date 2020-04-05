<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule('sale'))
	return;

$dbSite = CSite::GetByID(WIZARD_SITE_ID);
if($arSite = $dbSite -> Fetch())
	$lang = $arSite["LANGUAGE_ID"];
if(strlen($lang) <= 0)
	$lang = "ru";
$bRus = false;
if($lang == "ru")
	$bRus = true;

$defCurrency = "EUR";
if($lang == "ru")
	$defCurrency = "RUB";
elseif($lang == "en")
	$defCurrency = "USD";

$delivery = $wizard->GetVar("delivery");
$shopLocalization = $wizard->GetVar("shopLocalization");

WizardServices::IncludeServiceLang("step2.php", $lang);

//Init delivery handlers classes
\Bitrix\Sale\Delivery\Services\Manager::getHandlersList();
$deliveryItems = array();
$arLocation4Delivery = Array();

if(COption::GetOptionString("eshop", "wizard_installed", "N", WIZARD_SITE_ID) != "Y")
{
	$locationGroupID = 0;
	$arLocationArr = Array();

	if(\Bitrix\Main\Config\Option::get('sale', 'sale_locationpro_migrated', '') == 'Y') // CSaleLocation::isLocationProMigrated()
	{
		$res = \Bitrix\Sale\Location\LocationTable::getList(array('filter' => array('=TYPE.CODE' => 'COUNTRY'), 'select' => array('ID')));
		while($item = $res->fetch())
		{
			$arLocation4Delivery[] = Array("LOCATION_ID" => $item["ID"], "LOCATION_TYPE"=>"L");
		}
	}
	else
	{
		$dbLocation = CSaleLocation::GetList(Array(), array("LID" => $lang));
		while($arLocation = $dbLocation->Fetch())
		{
			$arLocation4Delivery[] = Array("LOCATION_ID" => $arLocation["ID"], "LOCATION_TYPE"=>"L");
			$arLocationArr[] = $arLocation["ID"];
		}

		$dbGroup = CSaleLocationGroup::GetList();
		if($arGroup = $dbGroup->Fetch())
		{
			$arLocation4Delivery[] = Array("LOCATION_ID" => $arGroup["ID"], "LOCATION_TYPE"=>"G");
		}
		else
		{
			$groupLang = array(
				array("LID" => "en", "NAME" => "Group 1")
			);

			if($bRus)
				$groupLang[] = array("LID" => $lang, "NAME" => GetMessage("SALE_WIZARD_GROUP"));
				
			$locationGroupID = CSaleLocationGroup::Add(
					array(
						"SORT" => 150,
						"LOCATION_ID" => $arLocationArr,
						"LANG" => $groupLang
					)
				);
		}
		//Location group
		if(IntVal($locationGroupID) > 0)
			$arLocation4Delivery[] = Array("LOCATION_ID" => $locationGroupID, "LOCATION_TYPE"=>"G");
	}

	$dbRes = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
		'filter' => array(
			'=CLASS_NAME' => '\Bitrix\Sale\Delivery\Restrictions\BySite',
			'=SERVICE_TYPE' => \Bitrix\Sale\Delivery\Restrictions\Manager::SERVICE_TYPE_SHIPMENT
		)
	));

	$dlvBySiteExist = false;

	while($rstr = $dbRes->fetch())
	{
		$lids = $rstr["PARAMS"]["SITE_ID"];

		if(is_array($lids))
			$dlvBySiteExist = in_array(WIZARD_SITE_ID, $lids);
		else
			$dlvBySiteExist = (WIZARD_SITE_ID == $lids);

		if($dlvBySiteExist)
			break;
	}

	if(!$dlvBySiteExist)
	{
		$deliveryItems[] = array(
			"NAME" => GetMessage("SALE_WIZARD_COUR"),
			"DESCRIPTION" => GetMessage("SALE_WIZARD_COUR_DESCR"),
			"CLASS_NAME" => '\Bitrix\Sale\Delivery\Services\Configurable',
			"CURRENCY" => $defCurrency,
			"SORT" => 100,
			"ACTIVE" => $delivery["courier"] == "Y" ? "Y" : "N",
			"LOGOTIP" => "/bitrix/modules/sale/ru/delivery/courier_logo.png",
			"CONFIG" => array(
				"MAIN" => array(
					"PRICE" => ($bRus ? "500" : "30"),
					"CURRENCY" => $defCurrency,
					"PERIOD" => array(
						"FROM" => 0,
						"TO" => 0,
						"TYPE" => "D"
					)
				)
			)
		);

		$deliveryItems[] = array(
			"NAME" => GetMessage("SALE_WIZARD_COUR1"),
			"DESCRIPTION" => GetMessage("SALE_WIZARD_COUR1_DESCR"),
			"CLASS_NAME" => '\Bitrix\Sale\Delivery\Services\Configurable',
			"CURRENCY" => $defCurrency,
			"SORT" => 200,
			"ACTIVE" => $delivery["self"] == "Y" ? "Y" : "N",
			"LOGOTIP" => "/bitrix/modules/sale/ru/delivery/self_logo.png",
			"CONFIG" => array(
				"MAIN" => array(
					"PRICE" => 0,
					"CURRENCY" => $defCurrency,
					"PERIOD" => array(
						"FROM" => 0,
						"TO" => 0,
						"TYPE" => "D"
					)
				)
			)
		);
	}
}

$dbRes = \Bitrix\Sale\Delivery\Services\Table::getList(array(
	'filter' => array(
		'=CLASS_NAME' => array(
			'\Sale\Handlers\Delivery\SpsrHandler',
			'\Bitrix\Sale\Delivery\Services\Automatic',
			'\Sale\Handlers\Delivery\AdditionalHandler'
		)
	),
	'select' => array('ID', 'CODE', 'ACTIVE', 'CLASS_NAME')
));

$existAutoDlv = array();

while($dlv = $dbRes->fetch())
{
	if($dlv['CLASS_NAME'] == '\Sale\Handlers\Delivery\SpsrHandler')
		$existAutoDlv['spsr'] = $dlv;
	elseif($dlv['CLASS_NAME'] == '\Sale\Handlers\Delivery\AdditionalHandler' && $dlv['CONFIG']['MAIN']['SERVICE_TYPE'] == 'RUSPOST')
		$existAutoDlv['ruspost'] = $dlv;
	elseif(!empty($dlv['CODE']))
		$existAutoDlv[$dlv['CODE']] = $dlv;
}

if($bRus)
{
	if ($shopLocalization == "ru")
	{
		if(empty($existAutoDlv["spsr"]))
		{
			$deliveryItems[] = array(
				"NAME" => GetMessage("SALE_WIZARD_SPSR"),
				"DESCRIPTION" => GetMessage("SALE_WIZARD_SPSR_DESCR"),
				"CLASS_NAME" => '\Sale\Handlers\Delivery\SpsrHandler',
				"CURRENCY" => $defCurrency,
				"SORT" => 100,
				"LOGOTIP" => "/bitrix/modules/sale/handlers/delivery/spsr/logo.png",
				"ACTIVE" => $delivery["spsr"] == "Y" ? "Y" : "N",
				"CONFIG" => array(
					"MAIN" => array(
						"CALCULATE_IMMEDIATELY" => "Y",
						"DEFAULT_WEIGHT" => 1000,
						"AMOUNT_CHECK" => 1,
						"NATURE" => 1,
						"LOGIN" => "",
						"PASS" => "",
						"ICN" => ""
					)
				)
			);
		}

		//new russian post
		if(!empty($delivery["rus_post"]))
		{
			$deliveryItems["rus_post"] = array(
				"NAME" => GetMessage("SALE_WIZARD_MAIL2"),
				"DESCRIPTION" => GetMessage("SALE_WIZARD_MAIL_DESC2"),
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\Services\Automatic',
				"CURRENCY" => $defCurrency,
				"SORT" => 400,
				"LOGOTIP" => "/bitrix/modules/sale/ru/delivery/rus_post_logo.png",
				"ACTIVE" => $delivery["rus_post"] == "Y" ? "Y" : "N",
				"CONFIG" => array(
					"MAIN" => array(
						"SID" => "rus_post",
						"MARGIN_VALUE" => 0,
						"MARGIN_TYPE" => "%"
					)
				)
			);
		}
	}
	elseif ($shopLocalization == "ua")
	{
		if(!empty($delivery["ua_post"]))
		{
			$deliveryItems["ua_post"] = array(
				"NAME" => GetMessage("SALE_WIZARD_UA_POST"),
				"DESCRIPTION" => "",
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\Services\Automatic',
				"CURRENCY" => $defCurrency,
				"SORT" => 600,
				"ACTIVE" => $delivery["ua_post"] == "Y" ? "Y" : "N",
				"LOGOTIP" => "/bitrix/modules/sale/ru/delivery/ua_post_logo.png",
				"CONFIG" => array(
					"MAIN" => array(
						"SID" => "ua_post",
						"MARGIN_VALUE" => 0,
						"MARGIN_TYPE" => "%"
					)
				)
			);
		}
	}
	elseif ($shopLocalization == "kz")
	{
		if(!empty($delivery["kaz_post"]))
		{
			$deliveryItems["kaz_post"] = array(
			"NAME" => GetMessage("SALE_WIZARD_KAZ_POST"),
			"DESCRIPTION" => "",
			"CLASS_NAME" => '\Bitrix\Sale\Delivery\Services\Automatic',
			"CURRENCY" => $defCurrency,
			"SORT" => 600,
			"ACTIVE" => $delivery["kaz_post"] == "Y" ? "Y" : "N",
			"LOGOTIP" => "/bitrix/modules/sale/ru/delivery/kaz_post_logo.png",
			"CONFIG" => array(
				"MAIN" => array(
					"SID" => "kaz_post",
					"MARGIN_VALUE" => 0,
					"MARGIN_TYPE" => "%"
				)
			));
		}
	}
}

if(!empty($delivery["ups"]))
{
	$deliveryItems["ups"] = array(
		"NAME" => "UPS",
		"DESCRIPTION" => GetMessage("SALE_WIZARD_UPS"),
		"CLASS_NAME" => '\Bitrix\Sale\Delivery\Services\Automatic',
		"CURRENCY" => $defCurrency,
		"SORT" => 300,
		"ACTIVE" => $delivery["ups"] == "Y" ? "Y" : "N",
		"LOGOTIP" => "/bitrix/modules/sale/delivery/ups_logo.gif",
		"CONFIG" => array(
			"MAIN" => array(
				"SID" => "ups",
				"MARGIN_VALUE" => 0,
				"MARGIN_TYPE" => "%",
				"OLD_SETTINGS" => array(
					"SETTINGS" => "/bitrix/modules/sale/delivery/ups/ru_csv_zones.csv;/bitrix/modules/sale/delivery/ups/ru_csv_export.csv",
				)
			)
		)
	);
}

if(!empty($delivery["dhlusa"]))
{
	$deliveryItems["dhlusa"] = array(
		"NAME" => "DHL (USA)",
		"DESCRIPTION" => GetMessage("SALE_WIZARD_UPS"),
		"CLASS_NAME" => '\Bitrix\Sale\Delivery\Services\Automatic',
		"CURRENCY" => $defCurrency,
		"SORT" => 300,
		"ACTIVE" => $delivery["dhlusa"] == "Y" ? "Y" : "N",
		"LOGOTIP" => "/bitrix/modules/sale/delivery/dhlusa_logo.gif",
		"CONFIG" => array(
			"MAIN" => array(
				"SID" => "dhlusa",
				"MARGIN_VALUE" => 0,
				"MARGIN_TYPE" => "%"
			)
		)
	);
}

foreach($deliveryItems as $code => $fields)
{
	//If service already exist just set activity
	if($fields['CLASS_NAME'] == '\Bitrix\Sale\Delivery\Services\Automatic' && !empty($existAutoDlv[$code]) && $fields["ACTIVE"] == "Y")
	{
		\Bitrix\Sale\Delivery\Services\Manager::update(
			$existAutoDlv[$code]["ID"],
			array("ACTIVE" => "Y")
		);
	}
	//not exist
	else
	{
		if(!empty($fields["LOGOTIP"]))
		{
			if (file_exists($_SERVER["DOCUMENT_ROOT"].$fields["LOGOTIP"]))
			{
				$fields["LOGOTIP"] = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].$fields["LOGOTIP"]);
				$fields["LOGOTIP"]["MODULE_ID"] = "sale";
				CFile::SaveForDB($fields, "LOGOTIP", "sale/delivery/logotip");
			}
		}

		try
		{
			if($service = \Bitrix\Sale\Delivery\Services\Manager::createObject($fields))
				$fields = $service->prepareFieldsForSaving($fields);
		}
		catch(\Bitrix\Main\SystemException $e)
		{
			continue;
		}

		$res = \Bitrix\Sale\Delivery\Services\Manager::add($fields);

		if($res->isSuccess())
		{
			if(!$fields["CLASS_NAME"]::isInstalled())
				$fields["CLASS_NAME"]::install();

			if($fields["CLASS_NAME"] == '\Bitrix\Sale\Delivery\Services\Configurable')
			{
				$newId = $res->getId();

				$res = \Bitrix\Sale\Internals\ServiceRestrictionTable::add(array(
					"SERVICE_ID" => $newId,
					"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
					"CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\BySite',
					"PARAMS" => array(
						"SITE_ID" => array(WIZARD_SITE_ID),
					)
				));

				\Bitrix\Sale\Location\Admin\LocationHelper::resetLocationsForEntity(
					$newId,
					$arLocation4Delivery,
					\Bitrix\Sale\Delivery\Services\Manager::getLocationConnectorEntityName(),
					false // is locations codes?
				);

				$res = \Bitrix\Sale\Internals\ServiceRestrictionTable::add(array(
					"SERVICE_ID" => $newId,
					"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
					"CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\ByLocation'
				));

				//Link delivery "pickup" to store
				if($fields["NAME"] == GetMessage("SALE_WIZARD_COUR1"))
				{
					\Bitrix\Main\Loader::includeModule('catalog');
					$dbStores = CCatalogStore::GetList(array(), array("ACTIVE" => 'Y'), false, false, array("ID"));

					if($store = $dbStores->Fetch())
					{
						\Bitrix\Sale\Delivery\ExtraServices\Manager::saveStores(
							$newId,
							array($store['ID'])
						);
					}
				}
			}
		}
	}
}

if(CModule::IncludeModule('subscribe'))
{
	$templates_dir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/subscribe/templates";
	$template = $templates_dir."/store_news_".WIZARD_SITE_ID;
	//Copy template from module if where was no template
	if(!file_exists($template))
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/php_interface/subscribe/templates/news", $template, false, true);
		$fname = $template."/template.php";
		if(file_exists($fname) && is_file($fname) && ($fh = fopen($fname, "rb")))
		{
			$php_source = fread($fh, filesize($fname));
			$php_source = preg_replace("#([\"'])(SITE_ID)(\\1)(\\s*=>\s*)([\"'])(.*?)(\\5)#", "\\1\\2\\3\\4\\5".WIZARD_SITE_ID."\\7", $php_source);
			$php_source = str_replace("Windows-1251", $arSite["CHARSET"], $php_source);
			$php_source = str_replace("Hello!", GetMessage("SUBSCR_1"), $php_source);
			$php_source = str_replace("<P>Best Regards!</P>", "", $php_source);
			fclose($fh);
			$fh = fopen($fname, "wb");
			if($fh)
			{
				fwrite($fh, $php_source);
				fclose($fh);
			}
		}
	}

	$rsRubric = CRubric::GetList(array(), array(
		"NAME" => GetMessage("SUBSCR_1"),
		"LID" => WIZARD_SITE_ID,
	));
	if(!$rsRubric->Fetch())
	{
		//Database actions
		$arFields = Array(
			"ACTIVE"	=> "Y",
			"NAME"		=> GetMessage("SUBSCR_1"),
			"SORT"		=> 100,
			"DESCRIPTION"	=> GetMessage("SUBSCR_2"),
			"LID"		=> WIZARD_SITE_ID,
			"AUTO"		=> "Y",
			"DAYS_OF_MONTH"	=> "",
			"DAYS_OF_WEEK"	=> "1,2,3,4,5,6,7",  
			"TIMES_OF_DAY"	=> "05:00",
			"TEMPLATE"	=> substr($template, strlen($_SERVER["DOCUMENT_ROOT"]."/")),
			"VISIBLE"	=> "Y",
			"FROM_FIELD"	=> COption::GetOptionString("main", "email_from", "info@ourtestsite.com"),
			"LAST_EXECUTED"	=> ConvertTimeStamp(false, "FULL"), 
		);
		$obRubric = new CRubric;
		$ID = $obRubric->Add($arFields);
	}
	COption::SetOptionString('subscribe', 'subscribe_section', '#SITE_DIR#personal/subscribe/');
}

$shopEmail = $wizard->GetVar("shopEmail");
$siteName = $wizard->GetVar("siteName");
COption::SetOptionString('main', 'email_from', $shopEmail);
COption::SetOptionString('main', 'new_user_registration', 'Y');
COption::SetOptionString('main', 'captcha_registration', 'Y');
COption::SetOptionString('main', 'site_name', $siteName);
COption::SetOptionInt("search", "suggest_save_days", 250);

if(strlen(COption::GetOptionString('main', 'CAPTCHA_presets', '')) <= 0)
{
	COption::SetOptionString('main', 'CAPTCHA_transparentTextPercent', '0');
	COption::SetOptionString('main', 'CAPTCHA_arBGColor_1', 'FFFFFF');
	COption::SetOptionString('main', 'CAPTCHA_arBGColor_2', 'FFFFFF');
	COption::SetOptionString('main', 'CAPTCHA_numEllipses', '0');
	COption::SetOptionString('main', 'CAPTCHA_arEllipseColor_1', '7F7F7F');
	COption::SetOptionString('main', 'CAPTCHA_arEllipseColor_2', 'FFFFFF');
	COption::SetOptionString('main', 'CAPTCHA_bLinesOverText', 'Y');
	COption::SetOptionString('main', 'CAPTCHA_numLines', '0');
	COption::SetOptionString('main', 'CAPTCHA_arLineColor_1', 'FFFFFF');
	COption::SetOptionString('main', 'CAPTCHA_arLineColor_2', 'FFFFFF');
	COption::SetOptionString('main', 'CAPTCHA_textStartX', '40');
	COption::SetOptionString('main', 'CAPTCHA_textFontSize', '26');
	COption::SetOptionString('main', 'CAPTCHA_arTextColor_1', '000000');
	COption::SetOptionString('main', 'CAPTCHA_arTextColor_2', '000000');
	COption::SetOptionString('main', 'CAPTCHA_textAngel_1', '-15');
	COption::SetOptionString('main', 'CAPTCHA_textAngel_2', '15');
	COption::SetOptionString('main', 'CAPTCHA_textDistance_1', '-2');
	COption::SetOptionString('main', 'CAPTCHA_textDistance_2', '-2');
	COption::SetOptionString('main', 'CAPTCHA_bWaveTransformation', 'Y');
	COption::SetOptionString('main', 'CAPTCHA_arBorderColor', '000000');
	COption::SetOptionString('main', 'CAPTCHA_arTTFFiles', 'bitrix_captcha.ttf');
	COption::SetOptionString('main', 'CAPTCHA_letters', 'ABCDEFGHJKLMNPQRSTWXYZ23456789');
	COption::SetOptionString('main', 'CAPTCHA_presets', '2');
}	
COption::SetOptionString('socialnetwork', 'allow_tooltip', 'N', false ,  WIZARD_SITE_ID);

//Edit profile task
$editProfileTask = false;
$dbResult = CTask::GetList(Array(), Array("NAME" => "main_change_profile"));
if ($arTask = $dbResult->Fetch())
	$editProfileTask = $arTask["ID"];
//Registered users group
$dbResult = CGroup::GetList($by, $order, Array("STRING_ID" => "REGISTERED_USERS"));
if (!$dbResult->Fetch())
{
	$group = new CGroup;
	$arFields = Array(
		"ACTIVE" => "Y",
		"C_SORT" => 3,
		"NAME" => GetMessage("REGISTERED_USERS"),
		"STRING_ID" => "REGISTERED_USERS",
	);

	$groupID = $group->Add($arFields);
	if ($groupID > 0)
	{
		COption::SetOptionString("main", "new_user_registration_def_group", $groupID);
		if ($editProfileTask)
			CGroup::SetTasks($groupID, Array($editProfileTask), true);
	}
}

$rsGroups = CGroup::GetList(($by="c_sort"), ($order="desc"), array("ACTIVE"=>"Y", "ADMIN"=>"N", "ANONYMOUS"=>"N")); 
if(!($rsGroups->Fetch()))
{
	$group = new CGroup;
	$arFields = Array(
		"ACTIVE"       => "Y",
		"C_SORT"       => 100,
		"NAME"         => GetMessage("REGISTERED_USERS"),
		"DESCRIPTION"  => "",
		);
	$NEW_GROUP_ID = $group->Add($arFields);
	COption::SetOptionString('main', 'new_user_registration_def_group', $NEW_GROUP_ID);
	
	$rsTasks = CTask::GetList(array(), array("MODULE_ID"=>"main", "SYS"=>"Y", "BINDIG"=>"module","LETTER"=>"P"));
	if($arTask = $rsTasks->Fetch())
	{
		CGroup::SetModulePermission($NEW_GROUP_ID, $arTask["MODULE_ID"], $arTask["ID"]);
	}
}

$userGroupID = "";
$dbGroup = CGroup::GetList($by = "", $order = "", Array("STRING_ID" => "sale_administrator"));
if($arGroup = $dbGroup -> Fetch())
{
	$userGroupID = $arGroup["ID"];
}
else
{
	$group = new CGroup;
	$arFields = Array(
		"ACTIVE"       => "Y",
		"C_SORT"       => 200,
		"NAME"         => GetMessage("SALE_WIZARD_ADMIN_SALE"),
		"DESCRIPTION"  => GetMessage("SALE_WIZARD_ADMIN_SALE_DESCR"),
		"USER_ID"      => array(),
		"STRING_ID"      => "sale_administrator",
		);
	$userGroupID = $group->Add($arFields);
}

if(IntVal($userGroupID) > 0)
{
	WizardServices::SetFilePermission(Array($siteID, "/bitrix/admin"), Array($userGroupID => "R"));
	WizardServices::SetFilePermission(Array($siteID, "/bitrix/admin"), Array($userGroupID => "R"));
	
	$new_task_id = CTask::Add(array(
			"NAME" => GetMessage("SALE_WIZARD_ADMIN_SALE"),
			"DESCRIPTION" => GetMessage("SALE_WIZARD_ADMIN_SALE_DESCR"),
			"LETTER" => "Q",
			"BINDING" => "module",
			"MODULE_ID" => "main",
	));
	if($new_task_id)
	{
		$arOps = array();
		$rsOp = COperation::GetList(array(), array("NAME"=>"cache_control|view_own_profile|edit_own_profile"));
		while($arOp = $rsOp->Fetch())
			$arOps[] = $arOp["ID"];
		CTask::SetOperations($new_task_id, $arOps);
	}
	
	$rsTasks = CTask::GetList(array(), array("MODULE_ID"=>"main", "SYS"=>"N", "BINDIG"=>"module","LETTER"=>"Q"));
	if($arTask = $rsTasks->Fetch())
	{
		CGroup::SetModulePermission($userGroupID, $arTask["MODULE_ID"], $arTask["ID"]);
	}
	
	CMain::SetGroupRight("sale", $userGroupID, "U");
	
	$rsTasks = CTask::GetList(array(), array("MODULE_ID"=>"catalog", "SYS"=>"Y", "BINDIG"=>"module","LETTER"=>"T"));
	while($arTask = $rsTasks->Fetch())
	{
		CGroup::SetModulePermission($userGroupID, $arTask["MODULE_ID"], $arTask["ID"]);
	}

	if (COption::GetOptionString("main", "~sale_converted_15", "") == "Y")
	{
		$dbTask = Bitrix\Main\TaskTable::getList(array(
			'select' => array('ID'),
			'filter' => array('NAME' => 'sale_status_all')
		));
		if ($task = $dbTask->Fetch())
		{
			$dbTasks = Bitrix\Sale\Internals\StatusGroupTaskTable::getList(array('filter' => array(
				'GROUP_ID' => $userGroupID,
				'TASK_ID' => $task['ID'],
			)));
			if (!$dbTasks->Fetch())
			{
				$dbStatus = Bitrix\Sale\Internals\StatusTable::getList(array(
					'filter' => array('TYPE' => array('O', 'D')),
					'select' => array('ID')
				));

				while($status = $dbStatus->Fetch())
				{
					$groupTasks = array(
						'STATUS_ID' => $status['ID'],
						'GROUP_ID' => $userGroupID,
						'TASK_ID' => $task['ID'],
					);
					Bitrix\Sale\Internals\StatusGroupTaskTable::add($groupTasks);
				}
			}
		}
	}
}

$userGroupID = "";
$dbGroup = CGroup::GetList($by = "", $order = "", Array("STRING_ID" => "content_editor"));

if($arGroup = $dbGroup -> Fetch())
{
	$userGroupID = $arGroup["ID"];
}
else
{
	$group = new CGroup;
	$arFields = Array(
		"ACTIVE"       => "Y",
		"C_SORT"       => 300,
		"NAME"         => GetMessage("SALE_WIZARD_CONTENT_EDITOR"),
		"DESCRIPTION"  => GetMessage("SALE_WIZARD_CONTENT_EDITOR_DESCR"),
		"USER_ID"      => array(),
		"STRING_ID"      => "content_editor",
		);
	$userGroupID = $group->Add($arFields);
	$DB->Query("INSERT INTO b_sticker_group_task(GROUP_ID, TASK_ID)	SELECT ".intVal($userGroupID).", ID FROM b_task WHERE NAME='stickers_edit' AND MODULE_ID='fileman'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
}
if(IntVal($userGroupID) > 0)
{
	WizardServices::SetFilePermission(Array($siteID, "/bitrix/admin"), Array($userGroupID => "R"));
	
	$rsTasks = CTask::GetList(array(), array("MODULE_ID"=>"main", "SYS"=>"Y", "BINDIG"=>"module","LETTER"=>"P"));
	if($arTask = $rsTasks->Fetch())
	{
		CGroup::SetModulePermission($userGroupID, $arTask["MODULE_ID"], $arTask["ID"]);
	}
	
	$rsTasks = CTask::GetList(array(), array("MODULE_ID"=>"fileman", "SYS"=>"Y", "BINDIG"=>"module","LETTER"=>"F"));
	while($arTask = $rsTasks->Fetch())
	{
		CGroup::SetModulePermission($userGroupID, $arTask["MODULE_ID"], $arTask["ID"]);
	}
	
	$SiteDir = "";
	if(WIZARD_SITE_ID != "s1")
	{
		$SiteDir = "/site_" . WIZARD_SITE_ID;
	}
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/index.php"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/about/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/news/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/catalog/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/personal/"), Array($userGroupID => "W"));
}
COption::SetOptionString("eshop", "wizard_installed", "Y", false, WIZARD_SITE_ID);
?>