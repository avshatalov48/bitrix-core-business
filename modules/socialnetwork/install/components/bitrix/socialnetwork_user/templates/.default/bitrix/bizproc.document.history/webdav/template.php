<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!empty($arResult["ERROR_MESSAGE"])):
	ShowError($arResult["ERROR_MESSAGE"]);
endif;
if (!empty($arResult["OK_MESSAGE"])):
	ShowNote($arResult["OK_MESSAGE"]);
endif;
global $by, $order; 



foreach($arResult['GRID_VERSIONS'] as $rowID=>$row)
{
    ob_start();
    $APPLICATION->IncludeComponent("bitrix:main.user.link",
        '',
        array(
            "ID" => $row['data']['USER_ID'],
            "HTML_ID" => "hist_auth_".$row['data']['USER_ID'],
            "NAME_TEMPLATE" => (isset($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat()),
            "USE_THUMBNAIL_LIST" => "N",
            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
            "CACHE_TIME" => $arParams["CACHE_TIME"],
        ),
        false, 
        array("HIDE_ICONS" => "Y")
    );
    $createdUser = ob_get_clean();
    $arResult['GRID_VERSIONS'][$rowID]['columns']['USER'] = $createdUser;
    $arResult['GRID_VERSIONS'][$rowID]['columns']["MODIFIED"] = FormatDate('X', MakeTimeStamp($row['data']["MODIFIED"]));
}

foreach ($arResult["GRID_VERSIONS"] as $docID => &$oHist)
{
	if (
		isset($oHist['data']['DOCUMENT']['PROPERTIES']['WEBDAV_SIZE']['VALUE'])
		&& (intval($oHist['data']['DOCUMENT']['PROPERTIES']['WEBDAV_SIZE']['VALUE']) <= 0)
		&& (sizeof($oHist['actions']) == 2) // safety ...
	)
	{
		$oHist['actions'][0] = $oHist['actions'][1];
		unset($oHist['actions'][1]); // restore prohibited if size=0
	}
}

?><?$APPLICATION->IncludeComponent(
	"bitrix:main.interface.grid",
	"",
	array(
		"GRID_ID" => $arParams["GRID_ID"],
		"HEADERS" => array(
			array("id" => "NAME", "name" => GetMessage("BPADH_NAME"), "default" => true, "sort" => "name"), 
			array("id" => "FILE_SIZE", "name" => GetMessage("BPADH_SIZE"), "default" => true, "sort" => "size"), 
			array("id" => "USER", "name" => GetMessage("BPADH_AUTHOR"), "default" => true, "sort" => "user_name"),
			array("id" => "MODIFIED", "name" => GetMessage("BPADH_MODIFIED"), "default" => true, "sort" => "modified"), 
		), 
		"SORT" => array(mb_strtolower($by) => mb_strtolower($order)),
		"ROWS" => $arResult["GRID_VERSIONS"],
		"FOOTER" => array(array("title" => GetMessage("BPADH_ALL"), "value" => count($arResult["GRID_VERSIONS"]))),
		"EDITABLE" => false,
		"ACTIONS" => array(
			"delete" => true
        ),
        "TAB_ID" => (isset($arParams["TAB_ID"]) ? $arParams["TAB_ID"] : ""),
        "FORM_ID" => (isset($arParams["FORM_ID"]) ? $arParams["FORM_ID"] : ""),
		"ACTION_ALL_ROWS" => false,
		"NAV_OBJECT" => $arResult["NAV_RESULT"],
		"AJAX_MODE" => "N",
	),
	($this->__component->__parent ? $this->__component->__parent : $component)
);
?>
