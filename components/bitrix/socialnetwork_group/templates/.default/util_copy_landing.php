<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;
use Bitrix\Landing\Copy\Integration\Group as LandingGroup;
use Bitrix\Main\Localization\Loc;

$file = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/lang/".LANGUAGE_ID."/util_copy_landing.php")));
Loc::loadLanguageFile($file);

if (Loader::includeModule("landing"))
{
	$APPLICATION->includeComponent(
		"bitrix:socialnetwork.copy.checker",
		"",
		[
			"moduleId" => LandingGroup::MODULE_ID,
			"queueId" => $arResult["VARIABLES"]["group_id"],
			"stepperClassName" => LandingGroup::STEPPER_CLASS,
			"checkerOption" => LandingGroup::CHECKER_OPTION,
			"errorOption" => LandingGroup::ERROR_OPTION,
			"titleMessage" => Loc::getMessage("LANDING_STEPPER_PROGRESS_TITLE"),
			"errorMessage" => Loc::getMessage("LANDING_STEPPER_PROGRESS_ERROR"),
		],
		$this->getComponent(),
		["HIDE_ICONS" => "Y"]
	);
}

