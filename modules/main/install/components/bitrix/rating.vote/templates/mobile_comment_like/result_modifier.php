<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

if (
	defined("LANGUAGE_ID")
	&& defined("LANG_ADMIN_LID")
	&& (LANGUAGE_ID != LANG_ADMIN_LID)
)
{
	$arResult["RATING_TEXT_LIKE_Y"] = GetMessage("RATING_COMMENT_LIKE_Y");
	$arResult["RATING_TEXT_LIKE_N"] = GetMessage("RATING_COMMENT_LIKE_N");
	$arResult["RATING_TEXT_LIKE_D"] = GetMessage("RATING_COMMENT_LIKE_D");
}
else
{
	$arResult["RATING_TEXT_LIKE_Y"] = COption::GetOptionString("main", "rating_text_like_y", GetMessage("RATING_COMMENT_LIKE_Y"));
	$arResult["RATING_TEXT_LIKE_N"] = COption::GetOptionString("main", "rating_text_like_n", GetMessage("RATING_COMMENT_LIKE_N"));
	$arResult["RATING_TEXT_LIKE_D"] = COption::GetOptionString("main", "rating_text_like_d", GetMessage("RATING_COMMENT_LIKE_D"));
}
?>