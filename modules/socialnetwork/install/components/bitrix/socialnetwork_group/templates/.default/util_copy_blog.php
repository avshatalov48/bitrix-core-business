<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;
use Bitrix\Blog\Copy\Integration\Group as BlogGroup;
use Bitrix\Main\Localization\Loc;

$file = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/lang/".LANGUAGE_ID."/util_copy_blog.php")));
Loc::loadLanguageFile($file);

if (Loader::includeModule("blog"))
{
	$APPLICATION->includeComponent(
		"bitrix:socialnetwork.copy.checker",
		"",
		[
			"moduleId" => BlogGroup::MODULE_ID,
			"queueId" => $arResult["VARIABLES"]["group_id"],
			"stepperClassName" => BlogGroup::STEPPER_CLASS,
			"checkerOption" => BlogGroup::CHECKER_OPTION,
			"errorOption" => BlogGroup::ERROR_OPTION,
			"titleMessage" => Loc::getMessage("BLG_STEPPER_PROGRESS_TITLE"),
			"errorMessage" => Loc::getMessage("BLG_STEPPER_PROGRESS_ERROR"),
		],
		$this->getComponent(),
		["HIDE_ICONS" => "Y"]
	);
}

