<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */
$this->setFrameMode(false);
/** @var CCacheManager $CACHE_MANAGER */
global $CACHE_MANAGER;

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CC_BLFE_MODULE_NOT_INSTALLED"));
	return;
}

$lists_perm = CListPermissions::CheckAccess(
	$USER,
	$arParams["~IBLOCK_TYPE_ID"],
	intval($arParams["~IBLOCK_ID"]),
	$arParams["~SOCNET_GROUP_ID"] ?? null
);
if($lists_perm < 0)
{
	switch($lists_perm)
	{
	case CListPermissions::WRONG_IBLOCK_TYPE:
		ShowError(GetMessage("CC_BLFE_WRONG_IBLOCK_TYPE"));
		return;
	case CListPermissions::WRONG_IBLOCK:
		ShowError(GetMessage("CC_BLFE_WRONG_IBLOCK"));
		return;
	case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
		ShowError(GetMessage("CC_BLFE_LISTS_FOR_SONET_GROUP_DISABLED"));
		return;
	default:
		ShowError(GetMessage("CC_BLFE_UNKNOWN_ERROR"));
		return;
	}
}
elseif(
	(
		$arParams["~IBLOCK_ID"] > 0
		&& $lists_perm < CListPermissions::IS_ADMIN
		&& !CIBlockRights::UserHasRightTo($arParams["~IBLOCK_ID"], $arParams["~IBLOCK_ID"], "iblock_edit")
	) || (
		$arParams["~IBLOCK_ID"] == 0
		&& $lists_perm < CListPermissions::IS_ADMIN
	)
)
{
	ShowError(GetMessage("CC_BLFE_ACCESS_DENIED"));
	return;
}

$arParams["CAN_EDIT"] =
	$lists_perm >= CListPermissions::IS_ADMIN
	|| (
		$arParams["~IBLOCK_ID"] > 0
		&& CIBlockRights::UserHasRightTo($arParams["~IBLOCK_ID"], $arParams["~IBLOCK_ID"], "iblock_edit")
	)
;

$arIBlock = CIBlock::GetArrayByID(intval($arParams["~IBLOCK_ID"]));
$arResult["~IBLOCK"] = $arIBlock;
$arResult["IBLOCK"] = htmlspecialcharsex($arIBlock);
$arResult["IBLOCK_ID"] = $arIBlock["ID"];

if(isset($arParams["SOCNET_GROUP_ID"]) && $arParams["SOCNET_GROUP_ID"] > 0)
	$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
else
	$arParams["SOCNET_GROUP_ID"] = "";

$arResult["GRID_ID"] = "lists_fields";
$arResult["FORM_ID"] = "lists_field_edit";

$arResult["~LISTS_URL"] = str_replace(
	array("#group_id#"),
	array($arParams["SOCNET_GROUP_ID"]),
	$arParams["~LISTS_URL"]
);
$arResult["LISTS_URL"] = htmlspecialcharsbx($arResult["~LISTS_URL"]);

$arResult["~LIST_URL"] = CHTTP::urlAddParams(str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
), array("list_section_id" => ""));
$arResult["LIST_URL"] = htmlspecialcharsbx($arResult["~LIST_URL"]);

$arResult["~LIST_EDIT_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_EDIT_URL"]
);
$arResult["LIST_EDIT_URL"] = htmlspecialcharsbx($arResult["~LIST_EDIT_URL"]);

$arResult["~LIST_FIELDS_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_FIELDS_URL"]
);
$arResult["LIST_FIELDS_URL"] = htmlspecialcharsbx($arResult["~LIST_FIELDS_URL"]);

$obList = new CList($arIBlock["ID"]);

$arFields = $obList->GetFields();
if(array_key_exists($arParams["FIELD_ID"], $arFields))
{
	$arResult["FIELD"] = $arFields[$arParams["FIELD_ID"]];
	$arResult["FIELD_ID"] = $arParams["FIELD_ID"];
}
else
{
	$arResult["FIELD"] = false;
	$arResult["FIELD_ID"] = false;
}

$arResult["~LIST_FIELD_EDIT_URL"] = str_replace(
	array("#list_id#", "#field_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arResult["FIELD_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_FIELD_EDIT_URL"]
);
$arResult["LIST_FIELD_EDIT_URL"] = htmlspecialcharsbx($arResult["~LIST_FIELD_EDIT_URL"]);

//Assume there was no error
$bVarsFromForm = false;

//Form submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
{
	//When Save or Apply buttons was pressed
	if(isset($_POST["save"]) || isset($_POST["apply"]))
	{
		$strError = "";

		//Gather fields for update
		$arField = array(
			"SORT" => $_POST["SORT"],
			"NAME" => trim($_POST["NAME"], " \n\r\t\x0"),
			"IS_REQUIRED" => $_POST["IS_REQUIRED"],
			"MULTIPLE" => $_POST["MULTIPLE"] ?? 'N',
			"CODE" => $_POST["CODE"] ?? null,
			"TYPE" => $_POST["TYPE"],
			"DEFAULT_VALUE" => $_POST["DEFAULT_VALUE"] ?? '',
			"USER_TYPE_SETTINGS" => $_POST["USER_TYPE_SETTINGS"] ?? null,
			"SETTINGS" => $_POST["SETTINGS"],
		);

		if (isset($arField["SETTINGS"]["ADD_READ_ONLY_FIELD"]) && $arField["SETTINGS"]["ADD_READ_ONLY_FIELD"] == "Y")
		{
			switch ($arField["TYPE"])
			{
				// todo Make validation for all field types common to the whole module
				case "L":
					if (is_array($_POST["LIST_DEF"]))
					{
						$listDefaultValue = current($_POST["LIST_DEF"]);
						if (empty($listDefaultValue))
							$strError = GetMessage("CC_BLFE_BAD_FIELD_ADD_READ_ONLY")."<br>";
					}
					break;
				case "S:HTML":
					if (empty($arField["DEFAULT_VALUE"]["TEXT"]))
						$strError = GetMessage("CC_BLFE_BAD_FIELD_ADD_READ_ONLY")."<br>";
					break;
				default:
					if (is_string($arField["DEFAULT_VALUE"]) && $arField["DEFAULT_VALUE"] == '')
						$strError = GetMessage("CC_BLFE_BAD_FIELD_ADD_READ_ONLY")."<br>";
					if (is_array($arField["DEFAULT_VALUE"]) && empty($arField["DEFAULT_VALUE"]))
						$strError = GetMessage("CC_BLFE_BAD_FIELD_ADD_READ_ONLY")."<br>";
			}
		}

		if($arField["NAME"] == '')
			$strError = GetMessage("CC_BLFE_BAD_FIELD_NAME")."<br>";

		if($arField["TYPE"] == "PREVIEW_PICTURE")
		{
			$arField["DEFAULT_VALUE"]["METHOD"] = "resample";
			$arField["DEFAULT_VALUE"]["COMPRESSION"] = intval(COption::GetOptionString('main', 'image_resize_quality', '95'));
		}
		elseif($arField["TYPE"] == "S:Date")
		{
			if(!empty($arField["DEFAULT_VALUE"]) && !CheckDateTime($arField["DEFAULT_VALUE"], FORMAT_DATE))
			{
				$strError = GetMessage("CC_BLFE_INVALID_DEFAULT_VALUE")."<br>";
			}
		}
		elseif($arField["TYPE"] == "S:DateTime")
		{
			if(!empty($arField["DEFAULT_VALUE"]) && !CheckDateTime($arField["DEFAULT_VALUE"]))
			{
				$strError = GetMessage("CC_BLFE_INVALID_DEFAULT_VALUE")."<br>";
			}
		}

		if(preg_match("/^(G|G:|E|E:)/", $arField["TYPE"]))
		{
			$arField["LINK_IBLOCK_ID"] = intval($_POST["LINK_IBLOCK_ID"]);
			$arIBLOCKS = CLists::GetIBlocks($arParams["~IBLOCK_TYPE_ID"], !$arParams["CAN_EDIT"], $arParams["~SOCNET_GROUP_ID"] ?? false);

			if(mb_substr($arField["TYPE"], 0, 1) == "G")
				unset($arIBLOCKS[$arResult["IBLOCK_ID"]]);

			if(!array_key_exists($arField["LINK_IBLOCK_ID"], $arIBLOCKS))
				$strError = GetMessage("CC_BLFE_WRONG_LINK_IBLOCK")."<br>";
		}


		if(isset($_POST["ROW_COUNT"]) && $_POST["ROW_COUNT"] > 0)
			$arField["ROW_COUNT"] = intval($_POST["ROW_COUNT"]);
		if(isset($_POST["COL_COUNT"]) && $_POST["COL_COUNT"] > 0)
			$arField["COL_COUNT"] = intval($_POST["COL_COUNT"]);

		if(isset($_POST["LIST"]))
			$arField["LIST"] = $_POST["LIST"];
		if (!isset($arField["LIST"]) || !is_array($arField["LIST"]))
		{
			$arField["LIST"] = [];
		}

		//Import values from textarea
		if(isset($_POST["LIST_TEXT_VALUES"]) && mb_strlen($_POST["LIST_TEXT_VALUES"]))
		{
			$max_sort = 0;

			//create values "map"
			$arListMap = array();
			foreach($arField["LIST"] as $i => $arEnum)
			{
				if($arEnum["SORT"] > $max_sort)
					$max_sort = intval($arEnum["SORT"]);

				$arListMap[trim($arEnum["VALUE"], " \t\n\r")] = $arEnum["ID"];
			}

			//add non empty lines to the list
			foreach(explode("\n", $_POST["LIST_TEXT_VALUES"]) as $value_line)
			{
				$value = trim($value_line, " \t\n\r");
				if($value <> '' && !isset($arListMap[$value]))
				{
					$max_sort += 10;
					$arListMap[$value] = "m".$max_sort;
					$arField["LIST"]["m".$max_sort] = array(
						"SORT" => $max_sort,
						"VALUE" => $value,
					);
				}
			}
		}

		if(isset($_POST["LIST_DEF"]) && is_array($_POST["LIST_DEF"]))
		{
			foreach($arField["LIST"] as $i => $arEnum)
			{
				$arField["LIST"][$i]["DEF"] = "N";
			}

			foreach($_POST["LIST_DEF"] as $def)
			{
				$def = intval($def);
				if($def > 0 && isset($arField["LIST"][$def]))
				{
					$arField["LIST"][$def]["DEF"] = "Y";
				}
			}
		}

		if(!$strError)
		{
			try
			{
				if ($arResult["FIELD_ID"])
				{
					unset($arField["TYPE"]);
					$arResult["FIELD_ID"] = $obList->UpdateField($arResult["FIELD_ID"], $arField);
				}
				else
				{
					$arResult["FIELD_ID"] = $obList->AddField($arField);
				}

				//Clear components cache
				$CACHE_MANAGER->ClearByTag("lists_list_".$arIBlock["ID"]);

				$tab_name = $arResult["FORM_ID"]."_active_tab";

				$obList->actualizeDocumentAdminPage(str_replace(
					array("#list_id#", "#group_id#"),
					array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]), $arParams["LIST_ELEMENT_URL"]));

				//And go to proper page
				if (isset($_POST["save"]))
					LocalRedirect($arResult["~LIST_FIELDS_URL"]);
				elseif ($arResult["FIELD_ID"])
					LocalRedirect(
						CHTTP::urlAddParams(str_replace(
							array("#list_id#", "#field_id#", "#group_id#"),
							array($arResult["IBLOCK_ID"], $arResult["FIELD_ID"], $arParams["SOCNET_GROUP_ID"]),
							$arParams["~LIST_FIELD_EDIT_URL"]
						),
							array($tab_name => $_POST[$tab_name]),
							array("skip_empty" => true, "encode" => true)
						)
					);
				else
					LocalRedirect($arResult["~LIST_FIELDS_URL"]);
			}
			catch (Bitrix\Main\SystemException $exception)
			{
				ShowError($exception->getMessage());
				if (!$arResult["FIELD_ID"])
				{
					$bVarsFromForm = true;
				}
			}
		}
		else
		{
			ShowError($strError);
			$bVarsFromForm = true;
		}
	}
	elseif(isset($_POST["action"]) && $_POST["action"]==="type_changed")
	{
		$bVarsFromForm = true;
	}
	elseif($arResult["FIELD_ID"] && isset($_POST["action"]) && $_POST["action"]==="delete")
	{
		$obList->DeleteField($arResult["FIELD_ID"]);
		$obList->Save();

		//Clear components cache
		$CACHE_MANAGER->ClearByTag("lists_list_".$arIBlock["ID"]);

		LocalRedirect($arResult["~LIST_FIELDS_URL"]);
	}
	else
	{
		//Go to lists page
		LocalRedirect($arResult["~LISTS_URL"]);
	}
}

$arResult["TYPES"] = $obList->GetAvailableTypes($arResult["FIELD_ID"]);

$data = array();

if($bVarsFromForm)
{//There was an error so display form values
	$data["SORT"] = $_POST["SORT"];
	$data["NAME"] = $_POST["NAME"];
	$data["IS_REQUIRED"] = $_POST["IS_REQUIRED"];
	$data["MULTIPLE"] = $_POST["MULTIPLE"] ?? 'N';
	$data["CODE"] = $_POST["CODE"] ?? '';
	$data["TYPE"] = $_POST["TYPE"];
	$data["ROW_COUNT"] = (isset($_POST["ROW_COUNT"]) ? $_POST["ROW_COUNT"] : 1);
	$data["COL_COUNT"] = (isset($_POST["COL_COUNT"]) ? $_POST["COL_COUNT"] : 30);
	if (isset($_POST["COL_COUNT"]))
		$data["COL_COUNT"] = $_POST["COL_COUNT"];

	if(!$arResult["FIELD"] || $data["TYPE"] !== $arResult["FIELD"]["TYPE"])
	{
		//field type was changed so it needs adjustment
		$arMatch = array();
		if(preg_match("/^(.):(.+)$/", $data["TYPE"], $arMatch))
		{
			$arResult["FIELD"]["PROPERTY_USER_TYPE"] = CIBlockProperty::GetUserType($arMatch[2]);
		}
	}

	if(
		$data["TYPE"] !== "PREVIEW_PICTURE"
		&& $data["TYPE"] !== "DETAIL_PICTURE"
		&& is_array($_POST["DEFAULT_VALUE"] ?? null)
	)
	{
		$data["DEFAULT_VALUE"] = "";
	}
	else
	{
		$data["DEFAULT_VALUE"] = $_POST["DEFAULT_VALUE"] ?? null;
	}

	$data["SETTINGS"] = $_POST["SETTINGS"];

	if(isset($_POST["LIST"]) && is_array($_POST["LIST"]))
	{
		$n = 0;
		$arResult["LIST"] = array();
		foreach($_POST["LIST"] as $k => $v)
		{
			$match = array();
			if(preg_match("/^n(\d+)$/", $k, $match))
			{
				if(intval($match[1]) > $n)
					$n = intval($match[1]);
			}
			$arResult["LIST"][$k] = array(
				"ID" => $k,
				"SORT" => $v["SORT"],
				"VALUE" => $v["VALUE"],
			);
		}
		while($n >= 0)
		{
			if(array_key_exists("n".$n, $arResult["LIST"]))
			{
				if($arResult["LIST"]["n".$n]["VALUE"] <> '')
					break;
				else
					unset($arResult["LIST"]["n".$n]);
			}

			$n--;
		}
		$arResult["LIST"][] = array(
			"ID" => "n".($n+1),
			"SORT" => 500,
			"NAME" => "",
		);
	}
	elseif(preg_match("/^(L|L:)/", $data["TYPE"]))
	{
		$arResult["LIST"] = array();
		$arResult["LIST"][] = array(
			"ID" => "n0",
			"SORT" => 500,
			"NAME" => "",
		);
	}
	else
	{
		$arResult["LIST"] = false;
	}

	$data["LIST_TEXT_VALUES"] = $_POST["LIST_TEXT_VALUES"] ?? '';

	if(isset($_POST["LIST_DEF"]) && is_array($_POST["LIST_DEF"]))
	{
		$n = 0;
		$arResult["LIST_DEF"] = array();
		foreach($_POST["LIST_DEF"] as $def)
		{
			if(array_key_exists($def, $arResult['LIST']))
				$arResult["LIST_DEF"][$def] = true;
		}
	}
	elseif(preg_match("/^(L|L:)/", $data["TYPE"]))
	{
		$arResult["LIST_DEF"] = array();
	}
	else
	{
		$arResult["LIST_DEF"] = false;
	}

	if(isset($_POST["LINK_IBLOCK_ID"]) && $_POST["LINK_IBLOCK_ID"] > 0)
	{
		$data["LINK_IBLOCK_ID"] = intval($_POST["LINK_IBLOCK_ID"]);
	}
}
elseif($arResult["FIELD_ID"])
{//Edit existing field
	$data["SORT"] = $arResult["FIELD"]["SORT"];
	$data["NAME"] = $arResult["FIELD"]["NAME"];
	$data["IS_REQUIRED"] = $arResult["FIELD"]["IS_REQUIRED"];
	$data["MULTIPLE"] = $arResult["FIELD"]["MULTIPLE"] ?? 'N';
	$data["CODE"] = $arResult["FIELD"]["CODE"] ?? '';
	$data["TYPE"] = $arResult["FIELD"]["TYPE"];
	$data["DEFAULT_VALUE"] = $arResult["FIELD"]["DEFAULT_VALUE"];
	$data["SETTINGS"] = $arResult["FIELD"]["SETTINGS"];
	$data["ROW_COUNT"] = $arResult["FIELD"]["ROW_COUNT"] ?? 0;
	$data["COL_COUNT"] = $arResult["FIELD"]["COL_COUNT"] ?? 0;

	if(isset($arResult["FIELD"]["LINK_IBLOCK_ID"]))
	{
		$data["LINK_IBLOCK_ID"] = intval($arResult["FIELD"]["LINK_IBLOCK_ID"]);
	}

	if(preg_match("/^PROPERTY_(\\d+)$/", $arResult["FIELD_ID"], $arMatch))
	{
		$arResult["LIST"] = array();
		$arResult["LIST_DEF"] = array();

		$rsEnum = CIBlockPropertyEnum::GetList(array("sort"=>"asc", "value"=>"asc"), array("PROPERTY_ID" => $arMatch[1]));
		while($ar = $rsEnum->GetNext())
		{
			$arResult["LIST"][$ar["ID"]] = $ar;
			if($ar["DEF"] == "Y")
				$arResult["LIST_DEF"][$ar["ID"]] = true;
		}

		$arResult["LIST"][] = array(
			"ID" => "n0",
			"SORT" => 500,
			"NAME" => "",
		);
	}
	else
	{
		$arResult["LIST"] = false;
		$arResult["LIST_DEF"] = false;
	}
	$data["LIST_TEXT_VALUES"] = '';
}
else
{//New one
	$data["ID"] = "";
	$data["SORT"] = 500;
	$data["NAME"] = GetMessage("CC_BLFE_FIELD_NAME_DEFAULT");
	$data["IS_REQUIRED"] = "N";
	$data["MULTIPLE"] = "N";
	$data["CODE"] = "";
	$data["TYPE"] = key($arResult["TYPES"]);
	$arResult["LIST"] = false;
	$data["LIST_TEXT_VALUES"] = '';
	$arResult["LIST_DEF"] = false;
}

if(preg_match("/^(G|G:|E|E:)/", $data["TYPE"]))
{
	$arResult["LINK_IBLOCKS"] = CLists::GetIBlocks($arParams["~IBLOCK_TYPE_ID"], !$arParams["CAN_EDIT"], $arParams["~SOCNET_GROUP_ID"] ?? false);
	if(mb_substr($data["TYPE"], 0, 1) == "G")
		unset($arResult["LINK_IBLOCKS"][$arResult["IBLOCK_ID"]]);
}

$arResult["FORM_DATA"] = array();
foreach($data as $key => $value)
{
	$arResult["FORM_DATA"]["~".$key] = $value;
	if(is_array($value))
	{
		foreach($value as $key1 => $value1)
			$value[$key1] = htmlspecialcharsbx($value1);
		$arResult["FORM_DATA"][$key] = $value;
	}
	else
	{
		$arResult["FORM_DATA"][$key] = htmlspecialcharsbx($value);
	}
}

$arResult['RAND_STRING'] = $this->randString();
$arResult["CAN_BE_MULTIPLE"] = !$obList->is_field($data["TYPE"]);
$arResult["IS_PROPERTY"] = $arResult["CAN_BE_MULTIPLE"];

if($data["TYPE"] == "S:map_yandex")
	$arResult["CAN_BE_MULTIPLE"] = false;
$arResult["CAN_BE_OPTIONAL"] = $data["TYPE"] != "NAME";
$arResult["IS_READ_ONLY"] = $arResult["FIELD_ID"]? $obList->is_readonly($arResult["FIELD_ID"]): CListFieldTypeList::GetByID($data["TYPE"])->IsReadonly();
$arResult["IS_MULTIPLE_ONLY"] = $data["TYPE"] == "S:DiskFile";

$this->IncludeComponentTemplate();

if($arResult["FIELD_ID"])
	$APPLICATION->SetTitle(GetMessage("CC_BLFE_TITLE_EDIT", array("#NAME#" => htmlspecialcharsex($arResult["FIELD"]["NAME"]))));
else
	$APPLICATION->SetTitle(GetMessage("CC_BLFE_TITLE_NEW"));

$APPLICATION->AddChainItem($arResult["IBLOCK"]["NAME"], $arResult["~LIST_URL"]);
$APPLICATION->AddChainItem(GetMessage("CC_BLFE_CHAIN_LIST_EDIT"), $arResult["~LIST_EDIT_URL"]);
$APPLICATION->AddChainItem(GetMessage("CC_BLFE_CHAIN_FIELDS"), $arResult["~LIST_FIELDS_URL"]);

?>