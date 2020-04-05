<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!empty($arResult["Post"]))
{
	$arResult["Post"]["SPERM_SHOW"] = $arResult["Post"]["SPERM"];
}

if (
	isset($arResult["Post"])
	&& isset($arResult["Post"]["SPERM"])
	&& isset($arResult["Post"]["SPERM"]["CRMCONTACT"])
	&& is_array($arResult["Post"]["SPERM"]["CRMCONTACT"])
	&& !empty($arResult["Post"]["SPERM"]["CRMCONTACT"])
	&& isset($arResult["Post"]["SPERM"]["U"])
	&& is_array($arResult["Post"]["SPERM"]["U"])
	&& !empty($arResult["Post"]["SPERM"]["U"])
)
{
	$arDestinationList = $arResult["Post"]["SPERM"];

	foreach($arDestinationList["CRMCONTACT"] as $key => $arDestination)
	{
		foreach($arDestinationList["U"] as $key2 => $arDestination2)
		{
			if (
				isset($arDestination2["CRM_ENTITY"])
				&& $arDestination2["CRM_ENTITY"] == 'C_'.$arDestination["ID"]
			)
			{
				$arDestinationList["CRMCONTACT"][$key]["CRM_USER_ID"] = $arDestinationList["U"][$key2]["ID"];
				unset($arDestinationList["U"][$key2]);
			}
		}
	}

	$arResult["Post"]["SPERM_SHOW"] = $arDestinationList;
}

if (empty($arResult["urlToEdit"]))
{
	$arResult["urlToEdit"] = CComponentEngine::makePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array(
		"post_id" => $arResult["Post"]["ID"],
		"user_id" => $arResult["Post"]["AUTHOR_ID"]
	));
}

if (empty($arResult["urlToHide"]))
{
	$arResult["urlToHide"] = CHTTP::urlAddParams(
		CHTTP::urlDeleteParams(
			$arResult["urlToPost"],
			array("sessid", "success", "hide", "delete")
		),
		array(
			"hide" => "Y",
			"SONET_GROUP_ID" => (isset($arParams["SONET_GROUP_ID"]) && intval($arParams["SONET_GROUP_ID"]) > 0 ? intval($arParams["SONET_GROUP_ID"]) : false),
			"sessid" => bitrix_sessid()
		)
	);
}

$parser = new \CTextParser();
$hashTags = $parser->detectTags(htmlspecialcharsBack($arResult["Post"]["DETAIL_TEXT"]));

if (
	!empty($arResult["Category"])
	&& !empty($hashTags)
)
{
	foreach($arResult["Category"] as $key => $category)
	{
		if (in_array($category['~NAME'], $hashTags))
		{
			unset($arResult["Category"][$key]);
		}
	}
}

?>