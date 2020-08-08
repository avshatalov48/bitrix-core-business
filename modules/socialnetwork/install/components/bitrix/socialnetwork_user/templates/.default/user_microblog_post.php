<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if($arResult["VARIABLES"]["post_id"] <> '')
{
	CModule::IncludeModule("blog");
	$postID = trim($arResult["VARIABLES"]["post_id"]);
	if(!is_numeric($postID) || mb_strlen(intval($postID)) != mb_strlen($postID))
	{
		$postID = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($postID));
		$arFilter = array("CODE" => $postID);
	}
	else
		$arFilter = array("ID" => intval($postID));

	if($arParams["PATH_TO_USER_POST"] == '')
		$arParams["PATH_TO_USER_POST"] = "/company/personal/user/#user_id#/blog/#post_id#/";

	$dbPost = CBlogPost::GetList(array(), $arFilter, false, false, array("ID", "AUTHOR_ID"));
	if($arPost = $dbPost->Fetch())
	{
		LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_POST"], array("post_id"=>$arPost["ID"], "user_id" => $arPost["AUTHOR_ID"])));
		die();
	}
}
?>