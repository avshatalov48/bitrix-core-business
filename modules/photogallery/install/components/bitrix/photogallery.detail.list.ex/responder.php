<?
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("photogallery");

if (!check_bitrix_sessid())
	die('<script>window.bxph_error = \''.GetMessage("IBLOCK_WRONG_SESSION").'\';</script>');

if (CPGalleryInterface::CheckSign($_REQUEST['sigh'], $_REQUEST["checkParams"]))
{
	$APPLICATION->RestartBuffer();
	$UCID = preg_replace("/[^a-z0-9\_]+/is" , "", $_REQUEST["UCID"]);

	?><script>
	if (!window.BX && top.BX)
		BX = top.BX;
	</script>
	<?

	CUtil::JSPostUnEscape();
	$arParams = array_merge($_REQUEST["checkParams"], $_REQUEST["reqParams"]);

	$elementId = intVal($_REQUEST["ELEMENT_ID"]);
	if ($_REQUEST['getRaiting'] == 'Y' && $arParams["USE_RATING"] == "Y" && $arParams["PERMISSION"] >= "R")
	{
		if ($arParams["DISPLAY_AS_RATING"] == "rating_main")
		{
			// Don't delete <!--BX_PHOTO_RATING-->, <!--BX_PHOTO_RATING_END--> comments - they are used in js to catch html content
			?><!--BX_PHOTO_RATING--><?
			$arParams["RATING_MAIN_TYPE"] = COption::GetOptionString("main", "rating_vote_template", COption::GetOptionString("main", "rating_vote_type", "standart") == "like"? "like": "standart");
			if ($arParams["RATING_MAIN_TYPE"] == "like_graphic")
				$arParams["RATING_MAIN_TYPE"] = "like";
			else if ($arParams["RATING_MAIN_TYPE"] == "standart")
				$arParams["RATING_MAIN_TYPE"] = "standart_text";
			$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:rating.vote",
				$arParams["RATING_MAIN_TYPE"],
				Array(
					"ENTITY_TYPE_ID" => "IBLOCK_ELEMENT",
					"ENTITY_ID" => $elementId,
					"OWNER_ID" => intval($_REQUEST["AUTHOR_ID"]),
					"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
					"AJAX_MODE" => "Y",
				),
				null,
				array("HIDE_ICONS" => "Y")
			);
			?><!--BX_PHOTO_RATING_END--><?
		}
		else
		{
			// It's important for correct functionality of iblock.vote component
			$_REQUEST["AJAX_CALL"] = "N";
			$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:iblock.vote",
				"ajax_photo",
				Array(
					"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ELEMENT_ID" => $elementId,
					"READ_ONLY" => $arParams["READ_ONLY"],
					"MAX_VOTE" => $arParams["MAX_VOTE"],
					"VOTE_NAMES" => $arParams["VOTE_NAMES"],
					"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
					"INCLUDE_JS_FILE" => "N",
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"]
				),
				null,
				array("HIDE_ICONS" => "Y")
			);
		}
	}

	if ($_REQUEST['increaseCounter'] == 'Y' && $arParams["PERMISSION"] >= "R")
	{
		CModule::IncludeModule("iblock");
		CIBlockElement::CounterInc($elementId);
		PClearComponentCacheEx($arParams["IBLOCK_ID"], array($arParams["SECTION_ID"]), false, false, false);
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php")
?>