<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult["urlToOwnBlog"] = "";
$arResult["urlToOwnProfile"] = "";

if (CModule::IncludeModule("blog") && $GLOBALS["USER"]->IsAuthorized())
{
	$arResult["urlToCreateMessageInBlog"] = CComponentEngine::MakePathFromTemplate(
		$arParams["PATH_TO_BLOG_NEW_POST"],
		array("user_id" => $GLOBALS["USER"]->GetID(), "post_id" => "new"));
}

if ($GLOBALS["USER"]->IsAuthorized())
{
	$arResult["urlToOwnProfile"] = CComponentEngine::MakePathFromTemplate($arParams["PROFILE_URL"], array("user_id" => $GLOBALS["USER"]->GetID()));

	$arCounters = CUserCounter::GetValues($GLOBALS["USER"]->GetID(), SITE_ID);
	$arResult["LOG_COUNTER"] = (isset($arCounters["**"]) ? intval($arCounters["**"]) : 0);
}
?>