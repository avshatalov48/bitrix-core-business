<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult["urlToOwnBlog"] = "";
$arResult["urlToOwnProfile"] = "";

if (CModule::IncludeModule("blog") && $GLOBALS["USER"]->IsAuthorized())
{

	$arOwnBlog = CBlog::GetByOwnerID($GLOBALS["USER"]->GetID());
	if ($arOwnBlog && array_key_exists("URL", $arOwnBlog) && strlen($arOwnBlog["URL"]) > 0)
	{
		$arResult["urlToOwnBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], 
			array("blog" => $arOwnBlog["URL"], "user_id" => $GLOBALS["USER"]->GetID()));
		$arResult["urlToCreateMessageInBlog"] = CComponentEngine::MakePathFromTemplate(
			$arParams["PATH_TO_BLOG_NEW_POST"], 
			array("blog" => $arOwnBlog["URL"], "user_id" => $GLOBALS["USER"]->GetID(), "post_id" => "new"));
	}
	else
	{
		$arResult["urlToCreateInBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_NEW_BLOG"], array("user_id" => $GLOBALS["USER"]->GetID()));
	}
}

if ($GLOBALS["USER"]->IsAuthorized())
{
	$arResult["urlToOwnProfile"] = CComponentEngine::MakePathFromTemplate($arParams["PROFILE_URL"], array("user_id" => $GLOBALS["USER"]->GetID()));
}
?>