<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;
use Bitrix\Tasks\Copy\Integration\Group;
use Bitrix\Main\Localization\Loc;

$file = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/lang/".LANGUAGE_ID."/util_copy_tasks.php")));
Loc::loadLanguageFile($file);

if (Loader::includeModule("tasks"))
{
	$APPLICATION->includeComponent(
		"bitrix:socialnetwork.copy.checker",
		"",
		[
			"moduleId" => Group::MODULE_ID,
			"queueId" => $arResult["VARIABLES"]["group_id"],
			"stepperClassName" => Group::STEPPER_CLASS,
			"checkerOption" => Group::CHECKER_OPTION,
			"errorOption" => Group::ERROR_OPTION,
			"titleMessage" => GetMessage("TASKS_STEPPER_PROGRESS_TITLE"),
			"errorMessage" => GetMessage("TASKS_STEPPER_PROGRESS_ERROR"),
		],
		$component,
		["HIDE_ICONS" => "Y"]
	);
}
