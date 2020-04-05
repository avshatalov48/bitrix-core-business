<?
$installSiteID = Trim($installSiteID);
$installPath = Trim(Trim(Trim($installPath), "\\/"));
$install404 = ($install404 ? true : false);
$installRewrite = ($installRewrite ? true : false);

if (StrLen($installSiteID) <= 0)
	return;
if (StrLen($installPath) <= 0)
	return;

$errorString = "";

$arSite = array();
$arSites = array();
$dbResult = CSite::GetList(($b = ""), ($o = ""), Array("ID" => $installSiteID, "ACTIVE" => "Y"));
while ($arResult = $dbResult->Fetch())
{
	$arSites[] = $arResult["LID"];
	if ($arResult["LID"] == $installSiteID)
	{
		$arSite = array(
			"LANGUAGE_ID" => $arResult["LANGUAGE_ID"],
			"ABS_DOC_ROOT" => $arResult["ABS_DOC_ROOT"],
			"DIR" => $arResult["DIR"],
			"SITE_ID" => $arResult["LID"],
			"SERVER_NAME" => $arResult["SERVER_NAME"],
			"NAME" => $arResult["NAME"]
		);
	}
}

$arLanguages = array();
$dbResult = CLanguage::GetList($lby="sort", $lorder="asc");
while ($arResult = $dbResult->Fetch())
	$arLanguages[] = $arResult;

if (Count($arLanguages) <= 0)
	return;
if (Count($arSite) <= 0)
	return;

if (!function_exists("GetSocNetMessageLocal"))
{
	function GetSocNetMessageLocal($message, $lang)
	{
		global $arGetSocNetMessageLocalCache;
		if (!is_array($arGetSocNetMessageLocalCache))
			$arGetSocNetMessageLocalCache = array();
		if (!array_key_exists($lang, $arGetSocNetMessageLocalCache))
		{
			$langFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/lang/".$lang."/install/install.php";
			if (!file_exists($langFile))
			{
				$langFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/lang/en/install/install.php";
				if (!file_exists($langFile))
					$langFile = "";
			}

			$MESS = array();
			if (StrLen($langFile) > 0)
				include($langFile);

			$arGetSocNetMessageLocalCache[$lang] = $MESS;
		}

		if (array_key_exists($message, $arGetSocNetMessageLocalCache[$lang]))
			return $arGetSocNetMessageLocalCache[$lang][$message];
		else
			return "";
	}

	function SonetDebugTmp($var, $name = "")
	{
		$fff = fopen($_SERVER["DOCUMENT_ROOT"]."/~test.tmp", "a");
		if (is_array($var))
			fwrite($fff, ((StrLen($name) > 0) ? $name.":\n" : "").print_r($var, true)."\n\n");
		else
			fwrite($fff, ((StrLen($name) > 0) ? $name."=" : "").$var."\n\n");
		fclose($fff);
	}
}

// ------------------ USER PROPERTY -------------------------------
if (CModule::IncludeModule("iblock"))
{
	$iblockTypeID = "car_catalogue_demo";

	$dbIBlockType = CIBlockType::GetList(array(), array("=ID" => $iblockTypeID));
	if ($arIBlockType = $dbIBlockType->Fetch())
	{
		$iblockTypeID = $arIBlockType["ID"];
	}
	else
	{
		$arFieldsLang = array();
		foreach ($arLanguages as $arLang)
			$arFieldsLang[$arLang["LID"]] = array("NAME" => GetSocNetMessageLocal("SONET_I_IBLOCK_TYPE_NAME", $arLang["LID"]));

		$arFields = array(
			"ID" => $iblockTypeID,
			"LANG" => $arFieldsLang,
			"SECTIONS" => "Y"
		);

		$iblockType = new CIBlockType;
		$iblockTypeID = $iblockType->Add($arFields);
		if (strLen($iblockTypeID) <= 0)
			$errorString .= $iblockType->LAST_ERROR;
	}

	if (StrLen($iblockTypeID) > 0)
	{
		$iblockCode = "car_catalogue_iblock_demo";
		$iblockID = 0;

		$dbIBlock = CIBlock::GetList(array(), array("TYPE" => $iblockTypeID, "CODE" => $iblockCode));
		if ($arIBlock = $dbIBlock->Fetch())
		{
			$iblockID = IntVal($arIBlock["ID"]);
		}
		else
		{
			$arFields = array(
				"ACTIVE" => "Y",
				"NAME" => GetSocNetMessageLocal("SONET_I_IBLOCK_NAME", $arSite["LANGUAGE_ID"]),
				"IBLOCK_TYPE_ID" => $iblockTypeID,
				"CODE" => $iblockCode,
				"RSS_ACTIVE" => "N",
				"WORKFLOW" => "N",
				"INDEX_ELEMENT" => "N",
				"LID" => array()
			);

			$iblock = new CIBlock;

			foreach ($arSites as $siteID)
				$arFields["LID"][] = $siteID;

			$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
			while ($arUGroups = $dbUGroups->Fetch())
			{
				if ($arUGroups["ANONYMOUS"] == "Y")
					$arFields["GROUP_ID"][$arUGroups["ID"]] = "R";
			}
			
			$iblockID = $iblock->Add($arFields);
			if ($iblockID <= 0)
				$errorString .= $iblock->LAST_ERROR;
		
			if ($iblockID > 0)
			{
				$arCars = array(
					array(
						"NAME" => "MERCEDES-BENZ",
						"CHILDREN_TMP" => array(
							array(
								"NAME" => "MERCEDES-BENZ 190",
							),
							array(
								"NAME" => "MERCEDES-BENZ 300",
							),
							array(
								"NAME" => "MERCEDES-BENZ A-KLASSE",
							),
							array(
								"NAME" => "MERCEDES-BENZ C-KLASSE",
							),
						),
					),
					array(
						"NAME" => "VOLKSWAGEN",
						"CHILDREN_TMP" => array(
							array(
								"NAME" => "VOLKSWAGEN GOLF",
							),
							array(
								"NAME" => "VOLKSWAGEN PASSAT",
							),
							array(
								"NAME" => "VOLKSWAGEN POLO",
							),
							array(
								"NAME" => "VOLKSWAGEN TIGUAN",
							),
						),
					),
					array(
						"NAME" => "RENAULT",
						"CHILDREN_TMP" => array(
							array(
								"NAME" => "RENAULT CLIO",
							),
							array(
								"NAME" => "RENAULT LOGAN",
							),
							array(
								"NAME" => "RENAULT MEGANE",
							),
						),
					),
					array(
						"NAME" => "TOYOTA",
						"CHILDREN_TMP" => array(
							array(
								"NAME" => "TOYOTA AVENSIS",
							),
							array(
								"NAME" => "TOYOTA CAMRY",
							),
							array(
								"NAME" => "TOYOTA CELICA",
							),
							array(
								"NAME" => "TOYOTA COROLLA",
							),
							array(
								"NAME" => "TOYOTA YARIS",
							),
						),
					),
					array(
						"NAME" => "OPEL",
						"CHILDREN_TMP" => array(
							array(
								"NAME" => "OPEL ASCONA",
							),
							array(
								"NAME" => "OPEL CORSA",
							),
							array(
								"NAME" => "OPEL KADETT",
							),
							array(
								"NAME" => "OPEL OMEGA",
							),
						),
					),
					array(
						"NAME" => "NISSAN",
						"CHILDREN_TMP" => array(
							array(
								"NAME" => "NISSAN ALMERA",
							),
							array(
								"NAME" => "NISSAN MICRA",
							),
							array(
								"NAME" => "NISSAN PATROL",
							),
						),
					),
					array(
						"NAME" => "FORD",
						"CHILDREN_TMP" => array(
							array(
								"NAME" => "FORD C-MAX",
							),
							array(
								"NAME" => "FORD ESCORT",
							),
							array(
								"NAME" => "FORD FIESTA",
							),
							array(
								"NAME" => "FORD FOCUS",
							),
						),
					),
					array(
						"NAME" => "MITSUBISHI",
						"CHILDREN_TMP" => array(
							array(
								"NAME" => "MITSUBISHI GALANT",
							),
							array(
								"NAME" => "MITSUBISHI PAJERO",
							),
						),
					),
					array(
						"NAME" => "BMW",
						"CHILDREN_TMP" => array(
							array(
								"NAME" => "BMW M",
							),
							array(
								"NAME" => "BMW X",
							),
						),
					),
					array(
						"NAME" => "AUDI",
						"CHILDREN_TMP" => array(
							array(
								"NAME" => "AUDI A4",
							),
							array(
								"NAME" => "AUDI A5",
							),
							array(
								"NAME" => "AUDI A6",
							),
						),
					),
					array(
						"NAME" => "FIAT",
						"CHILDREN_TMP" => array(
							array(
								"NAME" => "FIAT BRAVO",
							),
							array(
								"NAME" => "FIAT 900 T/E",
							),
						),
					),
				);

				foreach ($arCars as $arCar)
				{
					$arFields = array(
						"IBLOCK_ID" => $iblockID,
						"IBLOCK_SECTION_ID" => 0,
						"ACTIVE" => "Y",
						"NAME" => $arCar["NAME"]
					);

					$iblockSection = new CIBlockSection;
					$iblockSectionID = $iblockSection->Add($arFields, true, false);
					if ($iblockSectionID <= 0)
					{
						$errorString .= $iblockSection->LAST_ERROR;
					}
					else
					{
						if (array_key_exists("CHILDREN_TMP", $arCar) && Count($arCar["CHILDREN_TMP"]) > 0)
						{
							foreach ($arCar["CHILDREN_TMP"] as $arChildCar)
							{
								$arFields = array(
									"IBLOCK_ID" => $iblockID,
									"IBLOCK_SECTION_ID" => $iblockSectionID,
									"ACTIVE" => "Y",
									"NAME" => $arChildCar["NAME"]
								);

								$iblockSection = new CIBlockSection;
								$iblockChildSectionID = $iblockSection->Add($arFields, true, false);
								if ($iblockChildSectionID <= 0)
									$errorString .= $iblockSection->LAST_ERROR;
							}
						}
					}
				}
			}
		}
	}

	$iblockID = IntVal($iblockID);
	if (StrLen($iblockTypeID) > 0 && $iblockID > 0)
	{
		$dbUserField = CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "USER", "FIELD_NAME" => "UF_USER_CAR_DEMO"));
		if ($arUserField = $dbUserField->Fetch())
		{
		}
		else
		{
			$arFields = array(
				"ENTITY_ID" => "USER",
				"FIELD_NAME" => "UF_USER_CAR_DEMO",
				"USER_TYPE_ID" => "iblock_section",
				"XML_ID" => "",
				"SORT" => 100,
				"MULTIPLE" => "Y",
				"MANDATORY" => "",
				"SHOW_FILTER" => "S",
				"SHOW_IN_LIST" => "Y",
				"EDIT_IN_LIST" => "Y",
				"IS_SEARCHABLE" => "Y",
				"SETTINGS" => array(
					"IBLOCK_TYPE_ID" => $iblockTypeID,
					"IBLOCK_ID" => $iblockID,
					"DISPLAY" => "LIST",
					"LIST_HEIGHT" => 5,
				),
				"EDIT_FORM_LABEL" => array(),
				"LIST_COLUMN_LABEL" => array(),
				"LIST_FILTER_LABEL" => array(),
			);

			foreach ($arLanguages as $arLang)
			{
				$arFields["EDIT_FORM_LABEL"][$arLang["LID"]] = GetSocNetMessageLocal("SONET_I_USER_PROP_EFL", $arLang["LID"]);
				$arFields["LIST_COLUMN_LABEL"][$arLang["LID"]] = GetSocNetMessageLocal("SONET_I_USER_PROP_LCL", $arLang["LID"]);
				$arFields["LIST_FILTER_LABEL"][$arLang["LID"]] = GetSocNetMessageLocal("SONET_I_USER_PROP_LFL", $arLang["LID"]);
			}

			$userField = new CUserTypeEntity;
			$userFieldID = $userField->Add($arFields);

			if (!$userFieldID || $userFieldID <= 0)
			{
				if ($e = $GLOBALS["APPLICATION"]->GetException())
					$errorString .= $e->GetString();
			}
		}
	}
}

// ------------------ GROUPS -------------------------------
if (CModule::IncludeModule("socialnetwork"))
{
	$bSonetError = false;

	$cnt = CSocNetGroupSubject::GetList(array(), array("SITE_ID" => $arSite["SITE_ID"]), array());
	if (IntVal($cnt) <= 0)
	{
		$arGroupSubjects = array();
		$arGroupSubjectsId = array();

		for ($i = 0; $i < 3; $i++)
		{
			$arGroupSubjects[$i] = array(
				"SITE_ID" => $arSite["SITE_ID"],
				"NAME" => GetSocNetMessageLocal("SONET_GROUP_SUBJECT_".$i, $arSite["LANGUAGE_ID"]),
			);
			$arGroupSubjectsId[$i] = 0;
		}

		foreach ($arGroupSubjects as $ind => $arGroupSubject)
		{
			$idTmp = CSocNetGroupSubject::Add($arGroupSubject);
			if ($idTmp)
			{
				$arGroupSubjectsId[$ind] = IntVal($idTmp);
			}
			else
			{
				if ($e = $GLOBALS["APPLICATION"]->GetException())
					$errorString .= $e->GetString();

				$bSonetError = true;
			}
		}

		if (!$bSonetError)
		{
			$pathToImages = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/public/images/";

			$arGroupsId = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0);
			$arGroups = array(
				0 => array(
					"SITE_ID" => $arSite["SITE_ID"],
					"NAME" => GetSocNetMessageLocal("SONET_GROUP_NAME_0", $arSite["LANGUAGE_ID"]),
					"DESCRIPTION" => GetSocNetMessageLocal("SONET_GROUP_DESCRIPTION_0", $arSite["LANGUAGE_ID"]),
					"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"ACTIVE" => "Y",
					"VISIBLE" => "Y",
					"OPENED" => "N",
					"SUBJECT_ID" => $arGroupSubjectsId[2],
					"OWNER_ID" => 1,
					"KEYWORDS" => GetSocNetMessageLocal("SONET_GROUP_KEYWORDS_0", $arSite["LANGUAGE_ID"]),
					"IMAGE_ID" => array(
						"name" => "02_repair.jpg",
						"type" => "image/jpeg",
						"tmp_name" => $pathToImages."02_repair.jpg",
						"error" => "0",
						"size" => @filesize($pathToImages."02_repair.jpg"),
						"MODULE_ID" => "socialnetwork"
					),
					"NUMBER_OF_MEMBERS" => 1,
					"INITIATE_PERMS" => "E",
					"SPAM_PERMS" => "K",
					"=DATE_ACTIVITY" => $GLOBALS["DB"]->CurrentTimeFunction(),
				),
				1 => array(
					"SITE_ID" => $arSite["SITE_ID"],
					"NAME" => GetSocNetMessageLocal("SONET_GROUP_NAME_1", $arSite["LANGUAGE_ID"]),
					"DESCRIPTION" => GetSocNetMessageLocal("SONET_GROUP_DESCRIPTION_1", $arSite["LANGUAGE_ID"]),
					"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"ACTIVE" => "Y",
					"VISIBLE" => "Y",
					"OPENED" => "N",
					"SUBJECT_ID" => $arGroupSubjectsId[0],
					"OWNER_ID" => 1,
					"KEYWORDS" => GetSocNetMessageLocal("SONET_GROUP_KEYWORDS_1", $arSite["LANGUAGE_ID"]),
					"IMAGE_ID" => array(
						"name" => "04_vwclub.jpg",
						"type" => "image/jpeg",
						"tmp_name" => $pathToImages."04_vwclub.jpg",
						"error" => "0",
						"size" => @filesize($pathToImages."04_vwclub.jpg"),
						"MODULE_ID" => "socialnetwork"
					),
					"NUMBER_OF_MEMBERS" => 1,
					"INITIATE_PERMS" => "E",
					"SPAM_PERMS" => "K",
					"=DATE_ACTIVITY" => $GLOBALS["DB"]->CurrentTimeFunction(),
				),
				2 => array(
					"SITE_ID" => $arSite["SITE_ID"],
					"NAME" => GetSocNetMessageLocal("SONET_GROUP_NAME_2", $arSite["LANGUAGE_ID"]),
					"DESCRIPTION" => GetSocNetMessageLocal("SONET_GROUP_DESCRIPTION_2", $arSite["LANGUAGE_ID"]),
					"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"ACTIVE" => "Y",
					"VISIBLE" => "Y",
					"OPENED" => "N",
					"SUBJECT_ID" => $arGroupSubjectsId[1],
					"OWNER_ID" => 1,
					"KEYWORDS" => GetSocNetMessageLocal("SONET_GROUP_KEYWORDS_2", $arSite["LANGUAGE_ID"]),
					"IMAGE_ID" => array(
						"name" => "03_race.jpg",
						"type" => "image/jpeg",
						"tmp_name" => $pathToImages."03_race.jpg",
						"error" => "0",
						"size" => @filesize($pathToImages."03_race.jpg"),
						"MODULE_ID" => "socialnetwork"
					),
					"NUMBER_OF_MEMBERS" => 1,
					"INITIATE_PERMS" => "E",
					"SPAM_PERMS" => "K",
					"=DATE_ACTIVITY" => $GLOBALS["DB"]->CurrentTimeFunction(),
				),
				3 => array(
					"SITE_ID" => $arSite["SITE_ID"],
					"NAME" => GetSocNetMessageLocal("SONET_GROUP_NAME_3", $arSite["LANGUAGE_ID"]),
					"DESCRIPTION" => GetSocNetMessageLocal("SONET_GROUP_DESCRIPTION_3", $arSite["LANGUAGE_ID"]),
					"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"ACTIVE" => "Y",
					"VISIBLE" => "Y",
					"OPENED" => "Y",
					"SUBJECT_ID" => $arGroupSubjectsId[2],
					"OWNER_ID" => 1,
					"KEYWORDS" => GetSocNetMessageLocal("SONET_GROUP_KEYWORDS_3", $arSite["LANGUAGE_ID"]),
					"IMAGE_ID" => array(
						"name" => "05_tuning.jpg",
						"type" => "image/jpeg",
						"tmp_name" => $pathToImages."05_tuning.jpg",
						"error" => "0",
						"size" => @filesize($pathToImages."05_tuning.jpg"),
						"MODULE_ID" => "socialnetwork"
					),
					"NUMBER_OF_MEMBERS" => 1,
					"INITIATE_PERMS" => "K",
					"SPAM_PERMS" => "K",
					"=DATE_ACTIVITY" => $GLOBALS["DB"]->CurrentTimeFunction(),
				),
				4 => array(
					"SITE_ID" => $arSite["SITE_ID"],
					"NAME" => GetSocNetMessageLocal("SONET_GROUP_NAME_4", $arSite["LANGUAGE_ID"]),
					"DESCRIPTION" => GetSocNetMessageLocal("SONET_GROUP_DESCRIPTION_4", $arSite["LANGUAGE_ID"]),
					"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"ACTIVE" => "Y",
					"VISIBLE" => "Y",
					"OPENED" => "N",
					"SUBJECT_ID" => $arGroupSubjectsId[1],
					"OWNER_ID" => 1,
					"KEYWORDS" => GetSocNetMessageLocal("SONET_GROUP_KEYWORDS_4", $arSite["LANGUAGE_ID"]),
					"IMAGE_ID" => array(
						"name" => "01_4x4.jpg",
						"type" => "image/jpeg",
						"tmp_name" => $pathToImages."01_4x4.jpg",
						"error" => "0",
						"size" => @filesize($pathToImages."01_4x4.jpg"),
						"MODULE_ID" => "socialnetwork"
					),
					"NUMBER_OF_MEMBERS" => 1,
					"INITIATE_PERMS" => "E",
					"SPAM_PERMS" => "K",
					"=DATE_ACTIVITY" => $GLOBALS["DB"]->CurrentTimeFunction(),
				),
			);

			foreach ($arGroups as $ind => $arGroup)
			{
				$idTmp = CSocNetGroup::Add($arGroup);
				if ($idTmp)
				{
					$arGroupsId[$ind] = IntVal($idTmp);
				}
				else
				{
					if ($e = $GLOBALS["APPLICATION"]->GetException())
						$errorString .= $e->GetString();
					$bSonetError = true;
				}
			}
		}

		if (!$bSonetError)
		{
			foreach ($arGroupsId as $ind => $val)
			{
				CSocNetUserToGroup::Add(
					array(
						"USER_ID" => 1,
						"GROUP_ID" => $val,
						"ROLE" => "A",
						"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
						"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
						"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
						"INITIATED_BY_USER_ID" => 1,
						"MESSAGE" => false,
					)
				);

				CSocNetSubscription::Set(1, "SG".$val, "Y");
			}

			foreach ($arGroupsId as $ind => $val)
			{
				CSocNetFeatures::Add(
					array(
						"ENTITY_TYPE" => SONET_ENTITY_GROUP,
						"ENTITY_ID" => $val,
						"FEATURE" => "forum",
						"FEATURE_NAME" => GetSocNetMessageLocal("SONET_I_FEATURE_FORUM", $arSite["LANGUAGE_ID"]),
						"ACTIVE" => "Y",
						"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
						"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction()
					)
				);

				CSocNetFeatures::Add(
					array(
						"ENTITY_TYPE" => SONET_ENTITY_GROUP,
						"ENTITY_ID" => $val,
						"FEATURE" => "blog",
						"FEATURE_NAME" => GetSocNetMessageLocal("SONET_I_FEATURE_BLOG", $arSite["LANGUAGE_ID"]),
						"ACTIVE" => "Y",
						"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
						"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction()
					)
				);
			}
		}
	}
}

// ------------------ FORUM -------------------------------
$forumID = 0;
if (CModule::IncludeModule("forum"))
{
	$dbResult = CForumNew::GetListEx(array(), array("SITE_ID" => $arSite["SITE_ID"], "XML_ID" => "car_forum_demo"));
	if ($arResult = $dbResult->Fetch())
	{
		$forumID = $arResult["ID"];
	}
	else
	{
		$arFields = array(
			"NAME" => GetSocNetMessageLocal("SONET_I_FORUM_NAME", $arSite["LANGUAGE_ID"]),
			"ACTIVE" => "Y",
			"XML_ID" => "car_forum_demo",
			"MODERATION" => "N",
			"SITES" => array($arSite["SITE_ID"] => (StrLen($arSite["DIR"]) > 0 ? $arSite["DIR"] : "/")),
			"ALLOW_UPLOAD" => "Y"
		);

		$forumID = CForumNew::Add($arFields);
	}
}

// ------------------ BLOG -------------------------------
$blogGroupID = 0;
if (CModule::IncludeModule("blog"))
{
	$dbResult = CBlogGroup::GetList(array("ID" => "ASC"), array("SITE_ID" => $arSite["SITE_ID"]));
	if ($arResult = $dbResult->Fetch())
	{
		$blogGroupID = $arResult["ID"];
	}
	else
	{
		$blogGroupID = CBlogGroup::Add(array("SITE_ID" => $arSite["SITE_ID"], "NAME" => GetSocNetMessageLocal("SONET_I_BLOG_NAME", $arSite["LANGUAGE_ID"])));
	}
}

// ------------------ PHOTO -------------------------------
$photoIBlockTypeID = "car_gallery_demo";
$photoUserIBlockID = 0;
$photoGroupIBlockID = 0;
if (CModule::IncludeModule("iblock"))
{
	$dbIBlockType = CIBlockType::GetList(array(), array("=ID" => $photoIBlockTypeID));
	if ($arIBlockType = $dbIBlockType->Fetch())
	{
		$photoIBlockTypeID = $arIBlockType["ID"];
	}
	else
	{
		$arFieldsLang = array();
		foreach ($arLanguages as $arLang)
			$arFieldsLang[$arLang["LID"]] = array("NAME" => GetSocNetMessageLocal("SONET_I_PHOTO_IBLOCK_TYPE_NAME", $arLang["LID"]));

		$arFields = array(
			"ID" => $photoIBlockTypeID,
			"LANG" => $arFieldsLang,
			"SECTIONS" => "Y"
		);

		$iblockType = new CIBlockType;
		$photoIBlockTypeID = $iblockType->Add($arFields);
		if (strLen($photoIBlockTypeID) <= 0)
			$errorString .= $iblockType->LAST_ERROR;
	}

	if (StrLen($photoIBlockTypeID) > 0)
	{
		$iblockCode = "car_photo_user_demo";
		$photoUserIBlockID = 0;

		$dbIBlock = CIBlock::GetList(array(), array("SITE_ID" => $arSite["SITE_ID"], "TYPE" => $photoIBlockTypeID, "CODE" => $iblockCode));
		if ($arIBlock = $dbIBlock->Fetch())
		{
			$photoUserIBlockID = IntVal($arIBlock["ID"]);
		}
		else
		{
			$arFields = array(
				"ACTIVE" => "Y",
				"NAME" => GetSocNetMessageLocal("SONET_I_IBLOCK_PHOTO_USER_NAME", $arSite["LANGUAGE_ID"]),
				"IBLOCK_TYPE_ID" => $photoIBlockTypeID,
				"CODE" => $iblockCode,
				"RSS_ACTIVE" => "N",
				"WORKFLOW" => "N",
				"INDEX_ELEMENT" => "N",
				"LID" => array($arSite["SITE_ID"])
			);

			$iblock = new CIBlock;

			$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
			while ($arUGroups = $dbUGroups->Fetch())
			{
				if ($arUGroups["ANONYMOUS"] == "Y")
					$arFields["GROUP_ID"][$arUGroups["ID"]] = "R";
			}

			$photoUserIBlockID = $iblock->Add($arFields);
			if ($photoUserIBlockID <= 0)
				$errorString .= $iblock->LAST_ERROR;
		}

		$iblockCode = "car_photo_group_demo";
		$photoGroupIBlockID = 0;

		$dbIBlock = CIBlock::GetList(array(), array("SITE_ID" => $arSite["SITE_ID"], "TYPE" => $photoIBlockTypeID, "CODE" => $iblockCode));
		if ($arIBlock = $dbIBlock->Fetch())
		{
			$photoGroupIBlockID = IntVal($arIBlock["ID"]);
		}
		else
		{
			$arFields = array(
				"ACTIVE" => "Y",
				"NAME" => GetSocNetMessageLocal("SONET_I_IBLOCK_PHOTO_GROUP_NAME", $arSite["LANGUAGE_ID"]),
				"IBLOCK_TYPE_ID" => $photoIBlockTypeID,
				"CODE" => $iblockCode,
				"RSS_ACTIVE" => "N",
				"WORKFLOW" => "N",
				"INDEX_ELEMENT" => "N",
				"LID" => array($arSite["SITE_ID"])
			);

			$iblock = new CIBlock;

			$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
			while ($arUGroups = $dbUGroups->Fetch())
			{
				if ($arUGroups["ANONYMOUS"] == "Y")
					$arFields["GROUP_ID"][$arUGroups["ID"]] = "R";
			}

			$photoGroupIBlockID = $iblock->Add($arFields);
			if ($photoGroupIBlockID <= 0)
				$errorString .= $iblock->LAST_ERROR;
		}
	}
}


// ------------------ FILES -------------------------------
if (!function_exists("file_get_contents"))
{
	function file_get_contents($filename)
	{
		$fd = fopen("$filename", "rb");
		$content = fread($fd, filesize($filename));
		fclose($fd);
		return $content;
	}
}

if (StrLen($installPath) > 0)
{
	$folder = ($install404 ? "SEF" : "NSEF");
	$lng = ($arSite["LANGUAGE_ID"] == "ru") ? "ru" : (($arSite["LANGUAGE_ID"] == "de") ? "de" : "en");

	CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/public/".$folder."/".$lng, $arSite['ABS_DOC_ROOT'].$arSite["DIR"].$installPath, $installRewrite, true);

	if (file_exists($arSite['ABS_DOC_ROOT'].$arSite["DIR"].$installPath."/index.php"))
	{
		$file = file_get_contents($arSite['ABS_DOC_ROOT'].$arSite["DIR"].$installPath."/index.php");
		if ($file)
		{
			$file = str_replace("#SEF_FOLDER#", "/".$installPath."/", $file);
			$file = str_replace("#BLOG_GROUP_ID#", $blogGroupID, $file);
			$file = str_replace("#FORUM_ID#", $forumID, $file);
			$file = str_replace("#PHOTO_IBLOCK_TYPE#", $photoIBlockTypeID, $file);
			$file = str_replace("#PHOTO_USER_IBLOCK_ID#", $photoUserIBlockID, $file);
			$file = str_replace("#PHOTO_GROUP_IBLOCK_ID#", $photoGroupIBlockID, $file);

			if ($f = fopen($arSite['ABS_DOC_ROOT'].$arSite["DIR"].$installPath."/index.php", "w"))
			{
				@fwrite($f, $file);
				@fclose($f);
			}
		}
	}

	if (file_exists($arSite['ABS_DOC_ROOT'].$arSite["DIR"].$installPath."/.left.menu.php"))
	{
		$file = file_get_contents($arSite['ABS_DOC_ROOT'].$arSite["DIR"].$installPath."/.left.menu.php");
		if ($file)
		{
			$file = str_replace("#SEF_FOLDER#", "/".$installPath."/", $file);
			if ($f = fopen($arSite['ABS_DOC_ROOT'].$arSite["DIR"].$installPath."/.left.menu.php", "w"))
			{
				@fwrite($f, $file);
				@fclose($f);
			}
		}
	}

	if (file_exists($arSite['ABS_DOC_ROOT'].$arSite["DIR"].$installPath."/sect_inc.php"))
	{
		$file = file_get_contents($arSite['ABS_DOC_ROOT'].$arSite["DIR"].$installPath."/sect_inc.php");
		if ($file)
		{
			$file = str_replace("#SEF_FOLDER#", "/".$installPath."/", $file);
			if ($f = fopen($arSite['ABS_DOC_ROOT'].$arSite["DIR"].$installPath."/sect_inc.php", "w"))
			{
				@fwrite($f, $file);
				@fclose($f);
			}
		}
	}

	if ($folder == "SEF")
	{
		$arFields = array(
			"CONDITION" => "#^/".$installPath."/#",
			"RULE" => "",
			"ID" => "bitrix:socialnetwork",
			"PATH" => "/".$installPath."/index.php"
		);
		CUrlRewriter::Add($arFields);
	}
}

// ------------------  -------------------------------
?>