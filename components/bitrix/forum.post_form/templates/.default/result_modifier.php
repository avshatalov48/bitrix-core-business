<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*******************************************************************/
if (!$this->__component->__parent || empty($this->__component->__parent->__name))
{
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
};
CUtil::InitJSCore(array("ajax", "fx"));
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["SHOW_TAGS"] = (isset($arParams["SHOW_TAGS"]) && $arParams["SHOW_TAGS"] != "N" ? "Y" : "N");
$arParams["IMAGE_SIZE"] = (isset($arParams["IMAGE_SIZE"]) && intval($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 100);
$arParams["SMILES_COUNT"] = (isset($arParams["SMILES_COUNT"]) && intval($arParams["SMILES_COUNT"]) > 0 ? intval($arParams["SMILES_COUNT"]) : 0);
$arParams["form_index"] = $_REQUEST["INDEX"] ?? null;
if (!empty($arParams["form_index"]))
	$arParams["form_index"] = preg_replace("/[^a-z0-9]/is", "_", $arParams["form_index"]);
$arParams["tabIndex"] = intval(isset($arParams["TAB_INDEX"]) && intval($arParams["TAB_INDEX"]) > 0 ? $arParams["TAB_INDEX"] : 10);
$arParams["FORM_ID"] = "REPLIER".$arParams["form_index"];
$arParams["EDITOR_CODE_DEFAULT"] = ($arParams["EDITOR_CODE_DEFAULT"] == "Y" ? "Y" : "N");
$arResult["QUESTIONS"] = array_values($arResult["QUESTIONS"]);
$arParams["SEO_USE_AN_EXTERNAL_SERVICE"] = ($arParams["SEO_USE_AN_EXTERNAL_SERVICE"] == "N" ? "N" : "Y");
/*******************************************************************/
if (LANGUAGE_ID == 'ru')
{
	$path = str_replace(array("\\", "//"), "/", __DIR__."/ru/script.php");
	@include_once($path);
}
/********************************************************************
				/Input params
********************************************************************/
// Add Event for "main.file.input.upload"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST['mfi_mode']) && ($_REQUEST['mfi_mode'] == "upload"))
{
	if (!function_exists("__FPF_AddEntityInForumFiles"))
	{
		function __FPF_AddEntityInForumFiles(&$arCustomFile, $arParams = null)
		{
			static $arFileParams = array();

			if ($arParams !== null)
				$arFileParams = $arParams;
			$arFiles = array(array("FILE_ID" => $arCustomFile["fileID"]));
			if ((!is_array($arCustomFile)) || !isset($arCustomFile['fileID'])):
				return false;
			elseif(!CForumFiles::CheckFields($arFiles, $arFileParams, "NOT_CHECK_DB")):
				$ex = $GLOBALS["APPLICATION"]->GetException();
				return ($ex ? $ex->GetString() : "File upload error.");
			elseif(!empty($arFiles)):
				$GLOBALS["APPLICATION"]->RestartBuffer();
				CForumFiles::Add($arCustomFile['fileID'], $arFileParams);
			endif;
		}
	}

	AddEventHandler('main',  "main.file.input.upload", '__FPF_AddEntityInForumFiles');

	$Null = null;
	__FPF_AddEntityInForumFiles(
		$Null,
		array(
			"FORUM_ID" => $arParams["FID"],
			"TOPIC_ID" => $arParams["TID"],
			"MESSAGE_ID" => $arParams["MID"],
			"USER_ID" => intval($GLOBALS["USER"]->GetID())
		));
}
?>
