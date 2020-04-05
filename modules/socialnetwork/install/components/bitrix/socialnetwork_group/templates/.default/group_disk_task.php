<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var \Bitrix\Disk\Internals\BaseComponent $component */

if(isset($_REQUEST["action"]))
{
	$backUrl = CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_GROUP_DISK_FILE"], array('group_id' => $arResult['VARIABLES']['group_id'], "FILE_PATH" => $_REQUEST["file"]));
	$backUrl .= '#tab-bp';
}
else
{
	$backUrl = urldecode($_REQUEST["back_url"]);
	$backUrl .= '#tab-bp';
}
if(!preg_match('#^(?:/|\?|https?://)(?:\w|$)#D', $backUrl))
{
	$backUrl = '#';
}

$arResult['TOOLBAR'] = array(
	'BUTTONS' => array(
		array(
			'TEXT' => Loc::getMessage('DISK_FILE_VIEW_START_BIZPROC_GO_BACK_TEXT'),
			'TITLE' => Loc::getMessage('DISK_FILE_VIEW_START_BIZPROC__GO_BACK_TITLE'),
			'LINK' => $backUrl,
			'ICON' => 'back',
		),
	),
);

$APPLICATION->includeComponent(
	'bitrix:disk.interface.toolbar',
	'',
	array(
		'TOOLBAR_ID' => 'bp_task_toolbar',
		'CLASS_NAME' => 'bx-filepage',
		'BUTTONS'    => $arResult['TOOLBAR']['BUTTONS'],
	),
	$component,
	array('HIDE_ICONS' => 'Y')
);

$arParams["TASK_ID"] = intval($arResult["VARIABLES"]["ID"]);
$arParams["USER_ID"] = $GLOBALS["USER"]->GetID();

if ($arParams["TASK_ID"] > 0)
{
	$dbTask = \Bitrix\Disk\BizProcDocument::getTaskServiceList($arParams["TASK_ID"], $arParams["USER_ID"]);
	$arResult["TASK"] = $dbTask->GetNext();
}
?>
<div class="bx-disk-bizproc-section">
<?
if ($arResult["TASK"])
{
	$docID = $arResult["TASK"]["PARAMETERS"]["DOCUMENT_ID"][2];
	$arResult["VARIABLES"]["ELEMENT_ID"] = $docID;
	$arResult["VARIABLES"]["ACTION"] = "EDIT";

	$APPLICATION->IncludeComponent(
		"bitrix:bizproc.task",
		"",
		Array(
			"TASK_ID"       => $arResult["VARIABLES"]["ID"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]),
		$component,
		array("HIDE_ICONS" => "Y")
	);
}
else
{
	$back_url = (isset($_REQUEST["back_url"])) ? urldecode($_REQUEST["back_url"]) : CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('group_id' => $arResult['VARIABLES']['group_id'], "PATH" => ""));
	LocalRedirect($back_url);
}
?>
</div>
