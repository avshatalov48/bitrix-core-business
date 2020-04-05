<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */

$arResult['Owner'] = false;
if (
	!empty($arResult['Moderators'])
	&& !empty($arResult['Moderators']['List'])
	&& is_array($arResult['Moderators']['List'])
)
{
	foreach($arResult['Moderators']['List'] as $moderator)
	{
		if (!empty($moderator['IS_OWNER']))
		{
			$arResult['Owner'] = $moderator;
		}
	}
}
$arResult["PATH_TO_GROUP_INVITE"] = (
	!empty($arParams["PATH_TO_GROUP_INVITE"])
		? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_INVITE"], array("group_id" => $arResult["Group"]["ID"]))
		: $arResult["Urls"]["GroupEdit"].(strpos($arResult["Urls"]["GroupEdit"], "?") === false ? "?" : "&")."tab=invite"
);

?>